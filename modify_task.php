<?php
session_start();

// Check if logged in
if (!isset($_SESSION['professorId'])) {
    header('Location: login.php');
    exit();
}

$professorId = $_SESSION['professorId'];
$db = new mysqli('localhost', 'root', '', 'chatbot_login');

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

$task = null;
$levels = [];
$courses = [];

// Fetch levels
$levelQuery = "SELECT * FROM levels";
$levelResult = $db->query($levelQuery);

while ($row = $levelResult->fetch_assoc()) {
    $levels[] = $row;
}

// Fetch task details if task_id is passed
if (isset($_GET['task_id'])) {
    $taskId = $_GET['task_id'];
    $taskQuery = $db->prepare("SELECT * FROM tasks WHERE task_id = ?");
    $taskQuery->bind_param('i', $taskId);
    $taskQuery->execute();
    $taskResult = $taskQuery->get_result();

    if ($taskResult->num_rows > 0) {
        $task = $taskResult->fetch_assoc();

        // Fetch the course name for the task
        $courseQuery = $db->prepare("SELECT course_name FROM courses WHERE course_id = ?");
        $courseQuery->bind_param('i', $task['course_id']);
        $courseQuery->execute();
        $courseResult = $courseQuery->get_result();

        if ($courseResult->num_rows > 0) {
            $course = $courseResult->fetch_assoc();
        } else {
            $course = ['course_name' => 'Unknown'];
        }
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $taskId = $_POST['taskId'];
    $taskType = $_POST['taskType'];
    $noteText = $_POST['noteText'];
    $startDate = $_POST['startDate'];
    $endDate = $_POST['endDate'];
    $courseID = $_POST['courseId'];

    $updateTaskQuery = $db->prepare("UPDATE tasks SET task_type = ?, start_date = ?, end_date = ?, note = ?, course_id = ? WHERE task_id = ?");
    $updateTaskQuery->bind_param('ssssii', $taskType, $startDate, $endDate, $noteText, $courseID, $taskId);

    if ($updateTaskQuery->execute()) {
        header('Location: ../graduation_project/modify_task.php?task_id=' . $taskId . '&success=true');
    } else {
        echo "Error: " . $updateTaskQuery->error;
    }

    $updateTaskQuery->close();
    exit();
}


$db->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modify Task</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600;700&display=swap");

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Poppins", sans-serif;
        }

        body {
            background: linear-gradient(to right, #6a11cb, #2575fc);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            color: #333;
        }

        #navbar {
            width: 100%;
            background: #11101d;
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: fixed;
            top: 0;
            z-index: 1000;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        #navbar ul {
            list-style-type: none;
            display: flex;
            margin: 0;
            padding: 0;
        }

        #navbar ul li {
            margin-right: 20px;
        }

        #navbar ul li a {
            color: #fff;
            text-decoration: none;
            padding: 10px 15px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            transition: background 0.3s ease;
        }

        #navbar ul li a:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .profile-menu {
            position: relative;
        }

        .profile-menu img {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            cursor: pointer;
        }

        .profile-menu-content {
            display: none;
            position: absolute;
            right: 0;
            background-color: #fff;
            min-width: 160px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            z-index: 1;
            border-radius: 12px;
            overflow: hidden;
        }

        .profile-menu-content a {
            color: #333;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
            transition: background 0.3s ease;
        }

        .profile-menu-content a:hover {
            background-color: #f1f1f1;
        }

        .profile-menu:hover .profile-menu-content {
            display: block;
        }

        .container {
            margin-top: 100px;
            padding: 20px;
            width: calc(100% - 40px);
            max-width: 800px;
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            animation: fadeIn 1s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .container h1 {
            font-size: 36px;
            font-weight: 600;
            margin-bottom: 20px;
            text-align: center;
            color: #0d3073;
        }

        .container label {
            display: block;
            font-size: 14px;
            margin-bottom: 5px;
            color: #333;
        }

        .container select,
        .container textarea,
        .container input[type="date"],
        .container input[type="submit"] {
            width: 100%;
            padding: 12px;
            font-size: 14px;
            border-radius: 12px;
            border: 1px solid #ddd;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }

        .container select:focus,
        .container textarea:focus,
        .container input[type="date"]:focus {
            border-color: #0d3073;
            box-shadow: 0 0 5px rgba(13, 48, 115, 0.5);
        }

        .container input[type="submit"] {
            background: #0d3073;
            color: #fff;
            font-weight: 500;
            cursor: pointer;
        }

        .container input[type="submit"]:hover {
            background: #09245a;
        }

        textarea {
            resize: vertical;
            min-height: 100px;
        }

        #course-select:disabled {
            background: #f1f5f9;
            cursor: not-allowed;
        }

        select {
            appearance: none;
            background: url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNiIgaGVpZ2h0PSI0IiB2aWV3Qm94PSIwIDAgNiA0IiBmaWxsPSJub25lIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPjxwYXRoIGQ9Ik0zIDRMNiAwSDVMMyAyTDQgMkwzIDRaIiBmaWxsPSIjQzJDMkMyIi8+PC9zdmc+') no-repeat right 10px center;
            background-size: 12px 12px;
        }

        .container select::-ms-expand {
            display: none;
        }
    </style>
</head>

<body>
    <div id="navbar">
        <ul>
            <li><a href="professor_home.php">
                    <h4 align="center">Tasks</h4>
                </a></li>
            <li><a href="Dashboard.php">
                    <h4 align="center">Dashboard</h4>
                </a></li>
        </ul>
        <div class="profile-menu">
            <img src="icons8-male-user-100.png" alt="Profile Image">
            <div class="profile-menu-content">
                <a href="profile.php">Profile</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </div>
    <div class="container">
        <h1>Modify Task</h1>
        <?php if ($task): ?>
            <form id="task-form" action="" method="POST">
                <input type="hidden" name="taskId" value="<?php echo $task['task_id']; ?>">
                <input type="hidden" name="courseId" value="<?php echo $task['course_id']; ?>">
                <label for="task-type">Task Type:</label>
                <select id="task-type" name="taskType">
                    <option value="Quiz" <?php if ($task['task_type'] == 'Quiz')
                        echo 'selected'; ?>>Quiz</option>
                    <option value="Assignment" <?php if ($task['task_type'] == 'Assignment')
                        echo 'selected'; ?>>Assignment
                    </option>
                </select><br>
                <label for="note-text">Note:</label>
                <textarea id="note-text" name="noteText"><?php echo htmlspecialchars($task['note']); ?></textarea><br>
                <label for="start-date">Start Date:</label>
                <input type="date" id="start-date" name="startDate"
                    value="<?php echo date('Y-m-d', strtotime($task['start_date'])); ?>"><br>
                <label for="end-date">End Date:</label>
                <input type="date" id="end-date" name="endDate"
                    value="<?php echo date('Y-m-d', strtotime($task['end_date'])); ?>"><br>
                <label for="course-name">Course:</label>
                <p id="course-name">
                    <?php echo isset($course) ? htmlspecialchars($course['course_name']) : 'Course not found'; ?></p><br>
                <br>
                <input type="submit" value="Submit">
            </form>

        <?php else: ?>
            <p>Task not found</p>
        <?php endif; ?>
    </div>
    <script>
        function getQueryParam(name) {
            const urlParams = new URLSearchParams(window.location.search);
            return urlParams.get(name);
        }

        const success = getQueryParam('success');
        if (success) {
            alert('Task updated successfully');
            window.location.href = 'Dashboard.php';
        }
    </script>
</body>

</html>