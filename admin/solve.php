<?php
	if(ISSET($_POST['solve'])){
		$captcha = $_POST['captcha'];
		if($_SESSION['captcha'] == $captcha){
			echo "<center><label class = 'text-success'> Congratulations! You solve the captcha</label></center>";
		}else{
			echo "<center><label class = 'text-danger'> Invalid captcha! </label></center>";
		}
	}
?>