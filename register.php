<?php include ('server.php') ?>
<!DOCTYPE html>
<html>

<head>
	<title>Registration system PHP and MySQL</title>
	<link rel="stylesheet" type="text/css" href="css/registers.css">
</head>

<body>
	<div class="wrapper">
		<div class="inner">
			<img src="images/image-1.png" alt="" class="image-1">
			<form method="post" action="register.php">
				<div class="input-group">
					<label>First Name</label>
					<input type="text" name="firstname" required>
				</div>
				<div class="input-group">
					<label>Last Name</label>
					<input type="text" name="lastname" required>
				</div>
				<div class="input-group">
					<label>Username</label>
					<input type="text" name="username" required>
				</div>
				<div class="input-group">
					<label>Student Level</label>
					<select name="student_level" id="student_level" required>
						<option value="" selected disabled>Please select level</option>
						<option value="1">Level One</option>
						<option value="2">Level Two</option>
						<option value="3">Level Three</option>
						<option value="4">Level Four</option>
					</select>
				</div>
				<div class="input-group">
					<label>Semester</label>
					<select name="semester" required>
						<option value="" selected disabled>Please select Semester</option>
						<option value="1">1</option>
						<option value="2">2</option>
					</select>
				</div>
				<div class="input-group">
					<label>Email</label>
					<input type="email" name="email" required>
				</div>
				<div class="input-group">
					<label>Password</label>
					<input type="password" name="password_1" required>
				</div>
				<div class="input-group">
					<label>Confirm password</label>
					<input type="password" name="password_2" required>
				</div>
				<div class="input-group">
					<button type="submit" class="btn" name="reg_user">Register</button>
				</div>
				<p>
					Already a member? <a href="login.php">Sign in</a>
				</p>
			</form>
			<img src="images/image-2.png" alt="" class="image-2">
		</div>
	</div>


</body>

</html>