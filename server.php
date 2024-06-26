<?php
session_start();

// initializing variables
$first_name = "";
$last_name = "";
$username = "";
$level_id = "";
$semester = "";
$email = "";
$errors = array();

// connect to the database
$db = new mysqli('localhost', 'root', '', 'chatbot_login');

// Check connection
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// REGISTER USER
if (isset($_POST['reg_user'])) {
    // receive all input values from the form
    $username = $_POST['username'];
    $password_1 = $_POST['password_1'];
    $password_2 = $_POST['password_2'];
    $level_id = $_POST['student_level'];
    $first_name = $_POST['firstname'];
    $last_name = $_POST['lastname'];
    $semester = $_POST['semester'];
    $email = $_POST['email']; // New email field
    $role = $_POST['role']; // New role field

    // Form validation: Ensure that the form is correctly filled
    if (empty($first_name)) {
        array_push($errors, "First name is required");
    }
    if (empty($last_name)) {
        array_push($errors, "Last name is required");
    }
    if (empty($username)) {
        array_push($errors, "Username is required");
    }
    if (!preg_match("/^[a-zA-Z0-9_]+$/", $username)) {
        array_push($errors, "Username can only contain letters, numbers, and underscores");
    }
    if (empty($level_id)) {
        array_push($errors, "Student level is required");
    }
    if (empty($semester)) {
        array_push($errors, "Semester is required");
    }
    if (empty($password_1)) {
        array_push($errors, "Password is required");
    }
    if ($password_1 != $password_2) {
        array_push($errors, "The two passwords do not match");
    }
    if (empty($email)) {
        array_push($errors, "Email is required");
    }

    // Check if the username or email already exists
    $check_query = $role == 'professor' ? "SELECT * FROM professors WHERE username = ? OR email = ?" : "SELECT * FROM students WHERE username = ? OR email = ?";
    $stmt = $db->prepare($check_query);
    if ($stmt) {
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        if ($user) {
            if ($user['username'] === $username) {
                array_push($errors, "This username is already used.");
            }
            if ($user['email'] === $email) {
                array_push($errors, "This email is already used.");
            }
        }
        $stmt->close();
    } else {
        array_push($errors, "Error preparing statement: " . $db->error);
    }

    // Register the user if there are no errors in the form
    if (count($errors) == 0) {
        $password = password_hash($password_1, PASSWORD_DEFAULT); // Encrypt the password before saving in the database

        if ($role == 'professor') {
            $query = "INSERT INTO professors (username, password, first_name, last_name, email) VALUES (?, ?, ?, ?, ?)";
        } else {
            $query = "INSERT INTO students (username, password, Level_id, first_name, last_name, semester, email) VALUES (?, ?, ?, ?, ?, ?, ?)";
        }

        $stmt = $db->prepare($query);
        if ($stmt) {
            if ($role == 'professor') {
                $stmt->bind_param("sssss", $username, $password, $first_name, $last_name, $email);
            } else {
                $stmt->bind_param("ssissss", $username, $password, $level_id, $first_name, $last_name, $semester, $email);
            }
            if ($stmt->execute()) {
                if ($role != 'professor') {
                    $student_id = $stmt->insert_id; // Get the last inserted student_id

                    // Insert courses into students_courses
                    $course_query = "SELECT course_id FROM courses WHERE level_id = ? AND semester = ?";
                    $course_stmt = $db->prepare($course_query);
                    $course_stmt->bind_param("ii", $level_id, $semester);
                    $course_stmt->execute();
                    $courses_result = $course_stmt->get_result();

                    while ($course = $courses_result->fetch_assoc()) {
                        $insert_course_query = "INSERT INTO students_courses (student_id, course_id) VALUES (?, ?)";
                        $insert_course_stmt = $db->prepare($insert_course_query);
                        $insert_course_stmt->bind_param("is", $student_id, $course['course_id']);
                        $insert_course_stmt->execute();
                        $insert_course_stmt->close();
                    }
                    $course_stmt->close();
                }

                $_SESSION['username'] = $username;
                $_SESSION['success'] = "Registration successful";
                header('location: login.php');
                exit();
            } else {
                array_push($errors, "Error executing query: " . $stmt->error);
            }
            $stmt->close();
        } else {
            array_push($errors, "Error preparing statement: " . $db->error);
        }
    }
}

// LOGIN USER
if (isset($_POST['login_user'])) {
    $username = $_POST["username"];
    $password = $_POST["password"];
    $role = $_POST['role']; // get the role from the form

    if (empty($username)) {
        array_push($errors, "Username is required");
    }
    if (empty($password)) {
        array_push($errors, "Password is required");
    }

    if (count($errors) == 0) {
        if ($role == 'professor') {
            // Check if the username is in the Professors table
            $query = "SELECT * FROM professors WHERE username=?";
        } else {
            // Check if the username is in the Students table
            $query = "SELECT * FROM students WHERE username=?";
        }

        $stmt = $db->prepare($query);
        if ($stmt) {
            $stmt->bind_param("s", $username); // Bind the username parameter
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows == 1) {
                $logged_in_user = $result->fetch_assoc();
                if (password_verify($password, $logged_in_user['password'])) {
                    $_SESSION['username'] = $logged_in_user['username'];
                    $_SESSION['success'] = "You are now logged in";
                    $_SESSION['student_id'] = $logged_in_user['student_id']; // Set the student_id in the session
                    

                    // If the user is a professor, set the professorId in the session
                    if ($role == 'professor') {
                        $_SESSION['professorId'] = $logged_in_user['professor_id'];
                    }

                    header('location: ' . ($role == 'professor' ? 'professor_home.php' : 'student_home.php'));
                    exit();
                } else {
                    array_push($errors, "Wrong password");
                }
            } else {
                array_push($errors, "Wrong username");
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
