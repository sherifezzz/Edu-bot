<?php
session_start();

// initializing variables
$username = "";
$email = "";
$errors = array();

// connect to the database
$db = mysqli_connect('localhost', 'root', '', 'chatbot_login');

if (isset($_POST['reset_password'])) {
    // receive all input values from the form
    $username = mysqli_real_escape_string($db, $_POST['username']);
    $email = mysqli_real_escape_string($db, $_POST['email']);
    $new_password = mysqli_real_escape_string($db, $_POST['new_password']);

    // form validation: ensure that the form is correctly filled
    if (empty($username)) {
        array_push($errors, "Username is required");
    }
    if (empty($email)) {
        array_push($errors, "Email is required");
    }
    if (empty($new_password)) {
        array_push($errors, "New password is required");
    }

    // Finally, reset password if there are no errors in the form
    if (count($errors) == 0) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT); // encrypt the password before saving in the database

        $query = "UPDATE students SET password = ? WHERE username = ? AND email = ?";
        $stmt = $db->prepare($query);
        if ($stmt) {
            $stmt->bind_param("sss", $hashed_password, $username, $email);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                echo "<script>alert('New password saved.'); window.location.href='login.php';</script>";
            } else {
                echo "<script>alert('Password did not change. Please check for correct username or email.'); window.location.href='forgot_password.php';</script>";
            }
            $stmt->close();
        } else {
            array_push($errors, "Error preparing statement: " . $db->error);
        }
    }
}
?>

<!-- Include errors file where the $errors variable is already defined -->
<?php include ('errors.php'); ?>

<!DOCTYPE html>
<html lang="en">

<head>

    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Font Awesome Icons  -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css"
        integrity="sha512-+4zCK9k+qNFUR5X+cKL9EIR+ZOhtIloNl9GIKS57V1MyNsYpYcUrUeQc9vNfzsWfV28IaLL3i96P9sdNyeRssA=="
        crossorigin="anonymous" />
    <!-- Google Fonts  -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
    <title>Reset Password</title>
    <style>
        @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap");

        body {
            font-family: "Poppins", sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: linear-gradient(135deg, #0d3073, #f1f5f9);
        }

        form {
            width: 350px;
            background-color: #fff;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        h2 {
            font-size: 24px;
            font-weight: 600;
            color: #0d3073;
            margin-bottom: 20px;
        }

        input {
            display: block;
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 8px;
            box-sizing: border-box;
            transition: border-color 0.3s ease;
        }

        input:focus {
            border-color: #0d3073;
            outline: none;
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #0d3073;
            color: #fff;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        button:hover {
            background-color: #09245a;
            transform: translateY(-2px);
        }

        button:active {
            background-color: #081c4e;
            transform: translateY(0);
        }

        p {
            margin-top: 15px;
        }

        p a {
            color: #0d3073;
            text-decoration: none;
            font-weight: 500;
        }
    </style>

</head>

<body>
    <div class="card">
        <div class="header">
            <h2>Forgot Password?</h2>
            <p>You can reset your Password here</p>

        </div>
        <form method="post" action="forgot_password.php">
            <div class="input-group">
                <input type="text" class="passInput" name="username" placeholder="username" required>
            </div>
            <div class="input-group">
                <input type="text" class="passInput" name="email" placeholder="Your Email" required>
            </div>
            <div class="input-group">
                <input type="text" class="passInput" name="new_password" placeholder="New Password" name="new_password"
                    required>
            </div>
            <div class="input-group">
                <button type="submit" class="btn" name="reset_password">Reset Password</button>
            </div>
            <p>
            <a href="login.php">Sign in</a>
            </p>
        </form>
    </div>
</body>

</html>