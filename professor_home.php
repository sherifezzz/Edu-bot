<?php
session_start();

// تحقق من تسجيل الدخول
if (!isset($_SESSION['professorId'])) {
    header('Location: login.php');
    exit();
}

$professorId = $_SESSION['professorId'];

$db = new mysqli('localhost', 'root', '', 'chatbot_login');

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// جلب المستويات
$levels = [];
$levelQuery = "SELECT * FROM levels";
$levelResult = $db->query($levelQuery);

while ($row = $levelResult->fetch_assoc()) {
    $levels[] = $row;
}

// جلب الكورسات بناءً على مستوى معين
if (isset($_GET['level'])) {
    $level = $_GET['level'];
    $courseQuery = $db->prepare("SELECT courses.course_id, courses.course_name 
                                 FROM courses 
                                 JOIN course_professor ON courses.course_id = course_professor.course_id 
                                 WHERE course_professor.professor_id = ? AND courses.level_id = ?");
    $courseQuery->bind_param('ii', $professorId, $level);
    $courseQuery->execute();
    $courseResult = $courseQuery->get_result();

    $courses = [];
    while ($row = $courseResult->fetch_assoc()) {
        $courses[] = $row;
    }

    echo json_encode($courses);
    exit();
}

// إدخال المهام في قاعدة البيانات
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $taskType = $_POST['taskType'];
    $noteText = $_POST['noteText'];
    $startDate = $_POST['startDate'];
    $endDate = $_POST['endDate'];
    $courseID = $_POST['courseID'];

    $insertTaskQuery = $db->prepare("INSERT INTO tasks (task_type, start_date, end_date, note, course_id, professor_id)
                                     VALUES (?, ?, ?, ?, ?, ?)");
    $insertTaskQuery->bind_param('sssssi', $taskType, $startDate, $endDate, $noteText, $courseID, $professorId);

    if ($insertTaskQuery->execute()) {
        echo "Task created successfully!";
    } else {
        echo "Error: " . $insertTaskQuery->error;
    }

    $insertTaskQuery->close();
    exit();
}

$db->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assignment/Quiz Selector</title>
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
            <img src="icons8-male-user-100.png"
                alt="Profile Image">
            <div class="profile-menu-content">
                <a href="profile.php">Profile</a>
                <a href="login.php">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </div>
    <div class="container">
        <h1>Task Selector</h1>
        <label for="level-select">Select Student Level:</label>
        <select id="level-select">
            <?php foreach ($levels as $level): ?>
                <option value="<?php echo $level['level_id']; ?>"><?php echo $level['level_name']; ?></option>
            <?php endforeach; ?>
        </select>
        <label for="course-select">Select Course:</label>
        <select id="course-select"></select>
        <form id="task-form" action="" method="POST">
            <label for="task-type">Task Type:</label>
            <select id="task-type" name="taskType">
                <option value="Quiz">Quiz</option>
                <option value="Assignment">Assignment</option>
            </select><br>
            <label for="note-text">Note:</label>
            <textarea id="note-text" name="noteText"></textarea><br>
            <label for="start-date">Start Date:</label>
            <input type="date" id="start-date" name="startDate"><br>
            <label for="end-date">End Date:</label>
            <input type="date" id="end-date" name="endDate"><br>
            <input type="submit" value="Submit">
        </form>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const levelSelect = document.getElementById('level-select');
            const courseSelect = document.getElementById('course-select');
            const taskForm = document.getElementById('task-form');

            // Fetch courses based on selected level and logged-in professor
            levelSelect.addEventListener('change', function () {
                const selectedLevel = levelSelect.value;
                fetch(`?level=${selectedLevel}`)
                    .then(response => response.json())
                    .then(courses => {
                        courseSelect.innerHTML = '';
                        if (courses.error) {
                            alert(courses.error);
                        } else {
                            courses.forEach(course => {
                                const option = document.createElement('option');
                                option.value = course.course_id;
                                option.textContent = course.course_name;
                                courseSelect.appendChild(option);
                            });
                        }
                    });
            });

            // Handle form submission
            taskForm.addEventListener('submit', function (event) {
                event.preventDefault();
                const formData = new FormData(taskForm);
                const selectedCourseID = courseSelect.value;
                formData.append('courseID', selectedCourseID);

                fetch('', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.text())
                    .then(message => {
                        alert(message);
                        taskForm.reset();
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while submitting the form.');
                    });
            });
        });
    </script>
</body>

</html>