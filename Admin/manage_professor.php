<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

set_exception_handler(function ($exception) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => $exception->getMessage()]);
    exit;
});

$db = new mysqli('localhost', 'root', '', 'chatbot_login');

if ($db->connect_error) {
    echo json_encode(['status' => 'error', 'message' => "Connection failed: " . $db->connect_error]);
    exit;
}

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    header('Content-Type: application/json');
    ob_clean();

    if (isset($_POST['add_professor'])) {
        $username = $db->real_escape_string($_POST['username']);
        $password = password_hash($db->real_escape_string($_POST['password']), PASSWORD_BCRYPT);
        $first_name = $db->real_escape_string($_POST['first_name']);
        $last_name = $db->real_escape_string($_POST['last_name']);
        $email = $db->real_escape_string($_POST['email']);
        $courses = $_POST['courses'];
        $levels = $_POST['levels'];

        $add_query = "INSERT INTO professors (username, password, first_name, last_name, email) VALUES (?, ?, ?, ?, ?)";
        $stmt = $db->prepare($add_query);
        $stmt->bind_param("sssss", $username, $password, $first_name, $last_name, $email);

        if ($stmt->execute()) {
            $professor_id = $stmt->insert_id;

            foreach ($courses as $course_id) {
                $course_query = "INSERT INTO course_professor (course_id, professor_id) VALUES (?, ?)";
                $course_stmt = $db->prepare($course_query);
                $course_stmt->bind_param("ii", $course_id, $professor_id);
                $course_stmt->execute();
            }

            $log_insert_query = "INSERT INTO logs (log_type, table_name, row_id, admin_id) VALUES (?, ?, ?, ?)";
            $log_insert_stmt = $db->prepare($log_insert_query);
            $log_type = 'insert';
            $table_name = 'professors';
            $admin_id = $_SESSION['admin_id'] ?? null;
            if (!$admin_id) {
                echo json_encode(['status' => 'error', 'message' => 'Admin ID is not set.']);
                exit;
            }
            $log_insert_stmt->bind_param("ssii", $log_type, $table_name, $professor_id, $admin_id);
            $log_insert_stmt->execute();

            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $stmt->error]);
        }
        $stmt->close();
        ob_end_flush();
        exit;
    }

    if (isset($_POST['delete_professor_id'])) {
        $professor_id = intval($_POST['delete_professor_id']);

        $delete_query = "DELETE FROM professors WHERE professor_id = ?";
        $stmt = $db->prepare($delete_query);
        $stmt->bind_param("i", $professor_id);
        if ($stmt->execute()) {
            $log_delete_query = "INSERT INTO logs (log_type, table_name, row_id, admin_id) VALUES (?, ?, ?, ?)";
            $log_delete_stmt = $db->prepare($log_delete_query);
            $log_type = 'delete';
            $table_name = 'professors';
            $admin_id = $_SESSION['admin_id'] ?? null;
            if (!$admin_id) {
                echo json_encode(['status' => 'error', 'message' => 'Admin ID is not set.']);
                exit;
            }
            $log_delete_stmt->bind_param("ssii", $log_type, $table_name, $professor_id, $admin_id);
            $log_delete_stmt->execute();

            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $stmt->error]);
        }
        $stmt->close();
        ob_end_flush();
        exit;
    }



}

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['search'])) {
    header('Content-Type: application/json');
    ob_clean();

    $search = '%' . $_GET['search'] . '%';

    $query = "SELECT * FROM professors WHERE first_name LIKE ? OR last_name LIKE ? OR username LIKE ?";
    $stmt = $db->prepare($query);
    $stmt->bind_param("sss", $search, $search, $search);
    $stmt->execute();
    $result = $stmt->get_result();

    $professors = [];
    while ($row = $result->fetch_assoc()) {
        $professors[] = $row;
    }

    echo json_encode(['status' => 'success', 'professors' => $professors]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['levels'])) {
    header('Content-Type: application/json');
    ob_clean();

    $levels = explode(',', $_GET['levels']);
    $level_placeholders = implode(',', array_fill(0, count($levels), '?'));
    $types = str_repeat('i', count($levels));

    $query = "SELECT * FROM courses WHERE level_id IN ($level_placeholders)";
    $stmt = $db->prepare($query);
    $stmt->bind_param($types, ...$levels);
    $stmt->execute();
    $result = $stmt->get_result();

    $courses = [];
    while ($row = $result->fetch_assoc()) {
        $courses[] = $row;
    }

    echo json_encode(['status' => 'success', 'courses' => $courses]);
    exit;
}

$professors_query = "
    SELECT p.professor_id, p.first_name, p.last_name, p.username, p.email, GROUP_CONCAT(c.course_name SEPARATOR ', ') as courses
    FROM professors p
    LEFT JOIN course_professor cp ON p.professor_id = cp.professor_id
    LEFT JOIN courses c ON cp.course_id = c.course_id
    GROUP BY p.professor_id, p.first_name, p.last_name, p.username, p.email";
$professors_result = $db->query($professors_query);

$courses_query = "SELECT * FROM courses";
$courses_result = $db->query($courses_query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Professors</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="Professors.css">
</head>

<body>
    <div class="sidebar">
        <a href="dashbord.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="manage_professor.php"><i class="fas fa-chalkboard-teacher"></i> Manage Professors</a>
        <a href="manage_students.php"><i class="fas fa-user-graduate"></i> Manage Students</a>
        <a href="manage_courses.php"><i class="fas fa-book"></i> Manage Courses</a>
    </div>
    <div class="content">
        <h1>Manage Professors</h1>

        <div class="search-container">
            <input type="text" id="searchInput" placeholder="Search Professor...">
        </div>
        <div id="searchResults"></div>

        <div class="form-container">
            <h2>Add Professor</h2>
            <form id="addProfessorForm" method="POST">
                <input type="hidden" name="add_professor" value="1">
                <label for="first_name">First Name:</label>
                <input type="text" id="first_name" name="first_name" required>
                <label for="last_name">Last Name:</label>
                <input type="text" id="last_name" name="last_name" required>
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
                <label for="level">Level:</label>
                <div id="levels" class="checkbox-container">
                    <label><input type="checkbox" name="levels[]" value="1"> Level 1</label>
                    <label><input type="checkbox" name="levels[]" value="2"> Level 2</label>
                    <label><input type="checkbox" name="levels[]" value="3"> Level 3</label>
                    <label><input type="checkbox" name="levels[]" value="4"> Level 4</label>
                </div>
                <label for="courses">Courses:</label>
                <select id="courses" name="courses[]" multiple required></select>
                <button id="add_professor" type="submit">Add Professor</button>
            </form>
        </div>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Courses</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="professorTableBody">
                <?php while ($row = $professors_result->fetch_assoc()) { ?>
                    <tr id="row-<?php echo $row['professor_id']; ?>">
                        <td><?php echo $row['professor_id']; ?></td>
                        <td><?php echo $row['first_name']; ?></td>
                        <td><?php echo $row['last_name']; ?></td>
                        <td><?php echo $row['username']; ?></td>
                        <td><?php echo $row['email']; ?></td>
                        <td><?php echo $row['courses']; ?></td>
                        <td>
                            <button class="delete-button" data-id="<?php echo $row['professor_id']; ?>">Delete</button>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
    <script>
        document.getElementById('searchInput').addEventListener('input', function () {
            var searchInput = document.getElementById('searchInput').value;

            fetch('manage_professor.php?search=' + searchInput)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        displayProfessors(data.professors);
                    } else {
                        console.error('Error fetching professors:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error fetching professors:', error);
                });
        });

        function displayProfessors(professors) {
            var tableBody = document.getElementById('professorTableBody');
            tableBody.innerHTML = '';

            professors.forEach(function (professor) {
                var row = tableBody.insertRow();
                row.innerHTML = `
                <td>${professor.professor_id}</td>
                <td>${professor.first_name}</td>
                <td>${professor.last_name}</td>
                <td>${professor.username}</td>
                <td>${professor.email}</td>
                <td>${professor.courses}</td>
                <td>
                    <button class="delete-button" data-id="${professor.professor_id}">Delete</button>
                </td>
            `;
            });

            attachDeleteEvent();
        }

        function attachDeleteEvent() {
            document.querySelectorAll('.delete-button').forEach(button => {
                button.addEventListener('click', function () {
                    const professorId = this.getAttribute('data-id');
                    fetch('manage_professor.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: new URLSearchParams({
                            'delete_professor_id': professorId
                        })
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 'success') {
                                document.getElementById('row-' + professorId).remove();
                            } else {
                                alert('Error deleting professor: ' + data.message);
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('An error occurred while deleting the professor.');
                        });
                });
            });
        }


        document.getElementById('addProfessorForm').addEventListener('submit', function (e) {
            e.preventDefault();
            var form = document.getElementById('addProfessorForm');
            var formData = new FormData(form);

            fetch('manage_professor.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        alert('Professor added successfully.');
                        location.reload();
                    } else {
                        alert('Error adding professor: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while adding the professor.');
                });
        });

        document.querySelectorAll('input[name="levels[]"]').forEach(checkbox => {
            checkbox.addEventListener('change', function () {
                var selectedLevels = Array.from(document.querySelectorAll('input[name="levels[]"]:checked')).map(cb => cb.value);

                fetch('manage_professor.php?levels=' + selectedLevels.join(','))
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            var coursesDropdown = document.getElementById('courses');
                            coursesDropdown.innerHTML = ''; // Clear existing options

                            data.courses.forEach(course => {
                                var option = document.createElement('option');
                                option.value = course.course_id;
                                option.textContent = course.course_name;
                                coursesDropdown.appendChild(option);
                            });
                        } else {
                            alert('Error fetching courses: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching courses:', error);
                    });
            });
        });

        attachDeleteEvent();

    </script>
</body>

</html>