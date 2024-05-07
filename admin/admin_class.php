<?php
session_start();

class Action
{

	private $db;

	public function __construct()
	{
		ob_start();
		include 'db_connect.php';

		$this->db = $conn;
	}
	function __destruct()
	{
		$this->db->close();
		ob_end_flush();
	}
	private function log_activity($description)
	{
		$user_id = isset($_SESSION['login_user_id']) ? $_SESSION['login_user_id'] : 0; // Assuming 0 for system or non-logged-in actions
		$stmt = $this->db->prepare("INSERT INTO system_logs (user_id, description) VALUES (?, ?)");
		$stmt->bind_param("is", $user_id, $description);
		$stmt->execute();
		$stmt->close();
	}

	function login()
	{
		extract($_POST);
		$qry = $this->db->query("SELECT * FROM `users` where username = '" . $username . "' ");
		if ($qry->num_rows > 0) {
			$result = $qry->fetch_array();
			$is_verified = password_verify($password, $result['password']);
			if ($is_verified) {
				foreach ($result as $key => $value) {
					if ($key != 'password' && !is_numeric($key))
						$_SESSION['login_' . $key] = $value;
				}
				$this->log_activity("User logged in: " . $username);
				return 1;
			}
		}
		$this->log_activity("Failed login attempt for username: " . $username);
		return 3;
	}


	function login2()
	{
		extract($_POST);
		$stmt = $this->db->prepare("SELECT * FROM user_info WHERE email = ?");
		$stmt->bind_param("s", $email);
		$stmt->execute();
		$result = $stmt->get_result();

		if ($result->num_rows > 0) {
			$row = $result->fetch_assoc();
			if (password_verify($password, $row['password'])) {
				foreach ($row as $key => $value) {
					if ($key != 'password' && !is_numeric($key))
						$_SESSION['login_' . $key] = $value;
				}
				// After successful login, update the cart with the user's ID
				$ip = isset($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : (isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR']);
				$stmt = $this->db->prepare("UPDATE cart SET user_id = ? WHERE client_ip = ?");
				$stmt->bind_param("is", $_SESSION['login_user_id'], $ip);
				$stmt->execute();

				$this->log_activity("User logged in");
				return 1;
			} else {
				$this->log_activity("Failed login attempt via email for");
				return 3;
			}
		}
		$this->log_activity("Email not found during login attempt: " . $email);
		return 3;
	}

	function logout()
	{
		$this->log_activity("User logged out");
		session_destroy();
		foreach ($_SESSION as $key => $value) {
			unset($_SESSION[$key]);
		}
		header("location:login.php");
	}
	function logout2()
	{
		$this->log_activity("User logged out");
		session_destroy();
		foreach ($_SESSION as $key => $value) {
			unset($_SESSION[$key]);
		}
		header("location:../index.php");
	}

	function save_user()
	{
		extract($_POST);
		$password = password_hash($password, PASSWORD_DEFAULT);
		$data = " `name` = '$name' ";
		$data .= ", `username` = '$username' ";
		$data .= ", `password` = '$password' ";
		$data .= ", `type` = '$type' ";
		if (empty($id)) {
			$save = $this->db->query("INSERT INTO users set " . $data);
			$this->log_activity("Created new user: " . $username);
		} else {
			$save = $this->db->query("UPDATE users set " . $data . " where id = " . $id);
			$this->log_activity("Updated user: " . $username);
		}
		if ($save) {
			return 1;
		}
	}

	function signup()
	{
		extract($_POST);
		$password = password_hash($password, PASSWORD_DEFAULT);

		// Prepare a statement to check if the email already exists
		$stmt = $this->db->prepare("SELECT * FROM user_info WHERE email = ?");
		$stmt->bind_param("s", $email);
		$stmt->execute();
		$result = $stmt->get_result();
		if ($result->num_rows > 0) {
			$this->log_activity("Signup failed, email already exists: " . $email);
			return 2;
		}
		$stmt->close();

		// Prepare a statement to insert new user info
		$stmt = $this->db->prepare("INSERT INTO user_info (first_name, last_name, mobile, address, email, password) VALUES (?, ?, ?, ?, ?, ?)");
		$stmt->bind_param("ssssss", $first_name, $last_name, $mobile, $address, $email, $password);
		$execute = $stmt->execute();

		if ($execute) {
			$this->log_activity("New user signed up: " . $email);
			$this->login2();
			return 1; // Success
		} else {
			$this->log_activity("Signup failed for unknown reasons: " . $email);
			return 3; // Database insertion failed
		}
	}


	function save_settings()
	{
		extract($_POST);
		$data = " name = '$name' ";
		$data .= ", email = '$email' ";
		$data .= ", contact = '$contact' ";
		$data .= ", about_content = '" . htmlentities(str_replace("'", "&#x2019;", $about)) . "' ";
		if ($_FILES['img']['tmp_name'] != '') {
			$fname = strtotime(date('y-m-d H:i')) . '_' . $_FILES['img']['name'];
			$move = move_uploaded_file($_FILES['img']['tmp_name'], '../assets/img/' . $fname);
			$data .= ", cover_img = '$fname' ";
		}

		// echo "INSERT INTO system_settings set ".$data;
		$chk = $this->db->query("SELECT * FROM system_settings");
		if ($chk->num_rows > 0) {
			$save = $this->db->query("UPDATE system_settings set " . $data . " where id =" . $chk->fetch_array()['id']);
		} else {
			$save = $this->db->query("INSERT INTO system_settings set " . $data);
		}
		if ($save) {
			$query = $this->db->query("SELECT * FROM system_settings limit 1")->fetch_array();
			foreach ($query as $key => $value) {
				if (!is_numeric($key))
					$_SESSION['setting_' . $key] = $value;
			}

			return 1;
		}
	}


	function save_category()
	{
		extract($_POST);
		$data = " name = '$name' ";
		if (empty($id)) {
			$save = $this->db->query("INSERT INTO category_list set " . $data);
		} else {
			$save = $this->db->query("UPDATE category_list set " . $data . " where id=" . $id);
		}
		if ($save)
			return 1;
	}
	function delete_category()
	{
		extract($_POST);
		$delete = $this->db->query("DELETE FROM category_list where id = " . $id);
		if ($delete) {
			$this->log_activity("Deleted category: Category ID " . $id);
			return 1;
		}
	}
	function save_menu()
	{
		extract($_POST);
		$data = " name = '$name' ";
		$data .= ", price = '$price' ";
		$data .= ", category_id = '$category_id' ";
		$data .= ", description = '$description' ";
		if (isset($status) && $status == 'on')
			$data .= ", status = 1 ";
		else
			$data .= ", status = 0 ";
		if ($_FILES['img']['tmp_name'] != '') {
			$fname = strtotime(date('y-m-d H:i')) . '_' . $_FILES['img']['name'];
			$move = move_uploaded_file($_FILES['img']['tmp_name'], '../assets/img/' . $fname);
			$data .= ", img_path = '$fname' ";
		}
		$action_desc = empty($id) ? "Added new menu item: " . $name : "Updated menu item: " . $name;
		if (empty($id)) {
			$save = $this->db->query("INSERT INTO product_list set " . $data);
		} else {
			$save = $this->db->query("UPDATE product_list set " . $data . " where id=" . $id);
		}
		if ($save) {
			$this->log_activity($action_desc);
			return 1;
		}
	}
	function delete_menu()
	{
		extract($_POST);
		$delete = $this->db->query("DELETE FROM product_list where id = " . $id);
		if ($delete)
			return 1;
	}
	function delete_cart()
	{
		extract($_GET);
		$delete = $this->db->query("DELETE FROM cart where id = " . $id);
		if ($delete)
			header('location:' . $_SERVER['HTTP_REFERER']);
	}
	function add_to_cart()
	{
		extract($_POST);
		$data = " product_id = $pid ";
		$qty = isset($qty) ? $qty : 1;
		$data .= ", qty = $qty ";
		if (isset($_SESSION['login_user_id'])) {
			$data .= ", user_id = '" . $_SESSION['login_user_id'] . "' ";
		} else {
			$ip = isset($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : (isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR']);
			$data .= ", client_ip = '" . $ip . "' ";
		}
		$save = $this->db->query("INSERT INTO cart set " . $data);
		$this->log_activity("Added to cart: Product ID " . $pid . ", Quantity: " . $qty);
		if ($save)
			return 1;
	}

	function get_cart_count()
	{
		extract($_POST);
		if (isset($_SESSION['login_user_id'])) {
			$where = " where user_id = '" . $_SESSION['login_user_id'] . "'  ";
		} else {
			$ip = isset($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : (isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR']);
			$where = " where client_ip = '$ip'  ";
		}
		$get = $this->db->query("SELECT sum(qty) as cart FROM cart " . $where);
		if ($get->num_rows > 0) {
			return $get->fetch_array()['cart'];
		} else {
			return '0';
		}
	}

	function update_cart_qty()
	{
		extract($_POST);
		$data = " qty = $qty ";
		$save = $this->db->query("UPDATE cart set " . $data . " where id = " . $id);
		if ($save)
			return 1;
	}

	function save_order()
	{
		extract($_POST);
		$data = " name = '" . $first_name . " " . $last_name . "' ";
		$data .= ", address = '$address' ";
		$data .= ", mobile = '$mobile' ";
		$data .= ", email = '$email' ";
		$save = $this->db->query("INSERT INTO orders set " . $data);
		if ($save) {
			$id = $this->db->insert_id;
			$qry = $this->db->query("SELECT * FROM cart where user_id =" . $_SESSION['login_user_id']);
			while ($row = $qry->fetch_assoc()) {

				$data = " order_id = '$id' ";
				$data .= ", product_id = '" . $row['product_id'] . "' ";
				$data .= ", qty = '" . $row['qty'] . "' ";
				$save2 = $this->db->query("INSERT INTO order_list set " . $data);
				if ($save2) {
					$this->db->query("DELETE FROM cart where id= " . $row['id']);
				}
			}
			return 1;
		}
	}
	function confirm_order()
	{
		extract($_POST);
		$save = $this->db->query("UPDATE orders set status = 1 where id= " . $id);
		if ($save)
			return 1;
	}
}
