<?php
session_start();
// Include the database connection
$db = new mysqli('localhost', 'root', '', 'chatbot_login');
// Check for session admin_id
if (!isset($_SESSION['admin_id'])) {
    die("Admin not logged in");
} else {
    $admin_id = $_SESSION['admin_id']; // Get the admin ID from the session
}

// Check for POST data
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Handle insert course
    if (isset($_POST['course_name']) && (!isset($_POST['update_id']) || empty($_POST['update_id']))) {
        $course_name = $_POST['course_name'];
        $level_id = $_POST['level_id'];
        $semester = $_POST['semester'];
        $insert_query = "INSERT INTO courses (course_name, level_id, semester) VALUES (?, ?, ?)";
        $stmt = $db->prepare($insert_query);
        $stmt->bind_param("sis", $course_name, $level_id, $semester);
        if ($stmt->execute()) {
            // Log the action
            $course_id = $stmt->insert_id;
            $log_query = "INSERT INTO logs (log_type, table_name, row_id, admin_id) VALUES ('insert', 'courses', ?, ?)";
            $log_stmt = $db->prepare($log_query);
            $log_stmt->bind_param("ii", $course_id, $admin_id);
            $log_stmt->execute();
            $log_stmt->close();
            echo json_encode(['status' => 'success', 'action' => 'insert', 'course_id' => $course_id, 'course_name' => $course_name, 'level_id' => $level_id, 'semester' => $semester]);
        } else {
            echo json_encode(['status' => 'error']);
        }
        $stmt->close();
        exit;
    }

    // Handle update course
    if (isset($_POST['update_id']) && !empty($_POST['update_id'])) {
        $course_id = $_POST['update_id'];
        $course_name = $_POST['course_name'];
        $level_id = $_POST['level_id'];
        $semester = $_POST['semester'];
        $update_query = "UPDATE courses SET course_name = ?, level_id = ?, semester = ? WHERE course_id = ?";
        $stmt = $db->prepare($update_query);
        $stmt->bind_param("sisi", $course_name, $level_id, $semester, $course_id);
        if ($stmt->execute()) {
            // Log the action
            $log_query = "INSERT INTO logs (log_type, table_name, row_id, admin_id) VALUES ('update', 'courses', ?, ?)";
            $log_stmt = $db->prepare($log_query);
            $log_stmt->bind_param("ii", $course_id, $admin_id);
            $log_stmt->execute();
            $log_stmt->close();
            echo json_encode(['status' => 'success', 'action' => 'update', 'course_id' => $course_id, 'course_name' => $course_name, 'level_id' => $level_id, 'semester' => $semester]);
        } else {
            echo json_encode(['status' => 'error']);
        }
        $stmt->close();
        exit;
    }

    // Handle delete course
    if (isset($_POST['delete_id'])) {
        $delete_id = $_POST['delete_id'];
        $delete_query = "DELETE FROM courses WHERE course_id = ?";
        $stmt = $db->prepare($delete_query);
        $stmt->bind_param("i", $delete_id);
        if ($stmt->execute()) {
            // Log the action
            $log_query = "INSERT INTO logs (log_type, table_name, row_id, admin_id) VALUES ('delete', 'courses', ?, ?)";
            $log_stmt = $db->prepare($log_query);
            $log_stmt->bind_param("ii", $delete_id, $admin_id);
            $log_stmt->execute();
            $log_stmt->close();
            echo json_encode(['status' => 'success', 'action' => 'delete', 'course_id' => $delete_id]);
        } else {
            echo json_encode(['status' => 'error']);
        }
        $stmt->close();
        exit;
    }
}

// Fetch courses data from the database
$query = "SELECT * FROM courses";
$result = $db->query($query);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Courses</title>
    <link rel="stylesheet" href="courses.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
<div class="sidebar">
        <a href="dashbord.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="manage_professor.php"><i class="fas fa-chalkboard-teacher"></i> Manage Professors</a>
        <a href="manage_students.php"><i class="fas fa-user-graduate"></i> Manage Students</a>
        <a href="manage_courses.php"><i class="fas fa-book"></i> Manage Courses</a>
    </div>
    <div class="content">
        <h1>Manage Courses</h1>
        <div class="form-container">
            <h2>Add / Update Course</h2>
            <form id="courseForm">
                <input type="hidden" id="update_id" name="update_id">
                <div class="form-group">
                    <label for="course_name">Course Name</label>
                    <input type="text" id="course_name" name="course_name" required>
                </div>
                <div class="form-group">
                    <label for="level_id">Level ID</label>
                    <select id="level_id" name="level_id" required>
                        <option value="1">Level 1</option>
                        <option value="2">Level 2</option>
                        <option value="3">Level 3</option>
                        <option value="4">Level 4</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="semester">Semester</label>
                    <select id="semester" name="semester" required>
                        <option value="1">Semester 1</option>
                        <option value="2">Semester 2</option>
                    </select>
                </div>
                <div class="form-group">
                    <button type="submit" id="submitBtn">Add Course</button>
                </div>
            </form>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Course ID</th>
                    <th>Course Name</th>
                    <th>Level ID</th>
                    <th>Semester</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="courseTableBody">
                <?php
                while ($row = $result->fetch_assoc()) {
                    echo "<tr id='row-" . $row['course_id'] . "'>";
                    echo "<td>" . $row['course_id'] . "</td>";
                    echo "<td>" . $row['course_name'] . "</td>";
                    echo "<td>" . $row['level_id'] . "</td>";
                    echo "<td>" . $row['semester'] . "</td>";
                    echo "<td>
                                <button class='edit-btn' onclick='editCourse(" . $row['course_id'] . ", \"" . $row['course_name'] . "\", " . $row['level_id'] . ", \"" . $row['semester'] . "\")'>Edit</button>
                                <button class='delete-btn' onclick='deleteCourse(" . $row['course_id'] . ")'>Delete</button>
                              </td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
    <script>
        $(document).ready(function () {
            $('#courseForm').on('submit', function (e) {
                e.preventDefault();
                var formData = $(this).serialize();
                $.ajax({
                    type: 'POST',
                    url: '',
                    data: formData,
                    success: function (response) {
                        var res = JSON.parse(response);
                        if (res.status == 'success') {
                            alert('Course ' + res.action + 'd successfully.');
                            if (res.action == 'insert') {
                                $('#courseTableBody').append(
                                    `<tr id='row-${res.course_id}'>
                                        <td>${res.course_id}</td>
                                        <td>${res.course_name}</td>
                                        <td>${res.level_id}</td>
                                        <td>${res.semester}</td>
                                        <td>
                                            <button class='edit-btn' onclick='editCourse(${res.course_id}, "${res.course_name}", ${res.level_id}, ${res.semester})'>Edit</button>
                                            <button class='delete-btn' onclick='deleteCourse(${res.course_id})'>Delete</button>
                                        </td>
                                    </tr>`
                                );
                            } else if (res.action == 'update') {
                                $(`#row-${res.course_id}`).html(
                                    `<td>${res.course_id}</td>
                                     <td>${res.course_name}</td>
                                     <td>${res.level_id}</td>
                                     <td>${res.semester}</td>
                                     <td>
                                         <button class='edit-btn' onclick='editCourse(${res.course_id}, "${res.course_name}", ${res.level_id}, ${res.semester})'>Edit</button>
                                         <button class='delete-btn' onclick='deleteCourse(${res.course_id})'>Delete</button>
                                     </td>`
                                );
                            }
                            $('#courseForm')[0].reset();
                            $('#update_id').val('');
                            $('#submitBtn').text('Add Course');
                        } else {
                            alert('Error ' + res.action + 'ing course.');
                        }
                    }
                });
            });
        });

        function editCourse(course_id, course_name, level_id, semester) {
            $('#update_id').val(course_id);
            $('#course_name').val(course_name);
            $('#level_id').val(level_id);
            $('#semester').val(semester);
            $('#submitBtn').text('Update Course');
        }

        function deleteCourse(courseID) {
            if (confirm('Are you sure you want to delete this course?')) {
                $.ajax({
                    type: 'POST',
                    url: '',
                    data: { delete_id: courseID },
                    success: function (response) {
                        var res = JSON.parse(response);
                        if (res.status == 'success') {
                            $('#row-' + courseID).remove();
                            alert('Course deleted successfully.');
                        } else {
                            alert('Error deleting course.');
                        }
                    }
                });
            }
        }
    </script>
</body>

</html>