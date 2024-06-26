<?php
// Include the database connection
$db = new mysqli('localhost', 'root', '', 'chatbot_login');

// Check for connection errors
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// Check for POST data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['delete_id'])) {
        $delete_id = $_POST['delete_id'];
        $delete_query = "DELETE FROM students WHERE student_id = ?";
        $stmt = $db->prepare($delete_query);
        $stmt->bind_param("i", $delete_id);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error']);
        }
        $stmt->close();
        exit;
    } elseif (isset($_POST['search_query'])) {
        $search_query = $_POST['search_query'];
        $search_param = "%" . $search_query . "%";
        $search_query = "SELECT * FROM students 
                        WHERE username LIKE ? 
                           OR first_name LIKE ? 
                           OR last_name LIKE ? 
                           OR email LIKE ? 
                           OR level_id = ?";
        $stmt = $db->prepare($search_query);
        $stmt->bind_param("ssssi", $search_param, $search_param, $search_param, $search_param, $search_query);
        $stmt->execute();
        $result = $stmt->get_result();
        $students = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        echo json_encode($students);
        exit;
    } elseif (isset($_POST['update_id'])) {
        $update_id = $_POST['update_id'];
        $level = $_POST['level'];
        $semester = $_POST['semester'];
        $update_query = "UPDATE students SET level_id = ?, semester = ? WHERE student_id = ?";
        $stmt = $db->prepare($update_query);
        $stmt->bind_param("iii", $level, $semester, $update_id);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error']);
        }
        $stmt->close();
        exit;
    }
}

// Fetch students data from the database
$query = "SELECT * FROM students";
$result = $db->query($query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students</title>
    <link rel="stylesheet" href="students.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
    <div class="sidebar">
        <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="manage_professor.php"><i class="fas fa-chalkboard-teacher"></i> Manage Professors</a>
        <a href="manage_students.php"><i class="fas fa-user-graduate"></i> Manage Students</a>
        <a href="manage_courses.php"><i class="fas fa-book"></i> Manage Courses</a>
    </div>
    <div class="content">
        <h1>Manage Students</h1>
        <div class="custom-search-container">
            <input type="text" id="searchInput" placeholder="Search " onkeyup="searchStudent()">
        </div>

        <table>
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Student ID</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Email</th>
                    <th>Level</th>
                    <th>Semester</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="studentTableBody">
                <?php
                while ($row = $result->fetch_assoc()) {
                    echo "<tr id='row-" . $row['student_id'] . "'>";
                    echo "<td>" . $row['username'] . "</td>";
                    echo "<td>" . $row['student_id'] . "</td>";
                    echo "<td>" . $row['first_name'] . "</td>";
                    echo "<td>" . $row['last_name'] . "</td>";
                    echo "<td>" . $row['email'] . "</td>";
                    echo "<td><input type='text' value='" . $row['level_id'] . "' id='level-" . $row['student_id'] . "'></td>";
                    echo "<td><input type='text' value='" . $row['semester'] . "' id='semester-" . $row['student_id'] . "'></td>";
                    echo "<td>
                            <button class='update-btn' onclick='updateStudent(" . $row['student_id'] . ")'>Update</button>
                            <button class='delete-btn' onclick='deleteStudent(" . $row['student_id'] . ")'>Delete</button>
                          </td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <script>
        function searchStudent() {
            var searchInput = document.getElementById('searchInput').value;
            $.ajax({
                type: 'POST',
                url: '',
                data: { search_query: searchInput },
                success: function (response) {
                    var students = JSON.parse(response);
                    var tbody = document.getElementById('studentTableBody');
                    tbody.innerHTML = ''; // Clear existing table rows
                    if (students.length > 0) {
                        students.forEach(function (student) {
                            var row = `<tr id='row-${student.student_id}'>
                                        <td>${student.username}</td>
                                        <td>${student.student_id}</td>
                                        <td>${student.first_name}</td>
                                        <td>${student.last_name}</td>
                                        <td>${student.email}</td>
                                        <td><input type='text' value='${student.level_id}' id='level-${student.student_id}'></td>
                                        <td><input type='text' value='${student.semester}' id='semester-${student.student_id}'></td>
                                        <td>
                                            <button class='update-btn' onclick='updateStudent(${student.student_id})'>Update</button>
                                            <button class='delete-btn' onclick='deleteStudent(${student.student_id})'>Delete</button>
                                        </td>
                                       </tr>`;
                            tbody.innerHTML += row;
                        });
                    } else {
                        var row = `<tr><td colspan='8'>No results found.</td></tr>`;
                        tbody.innerHTML = row;
                    }
                }
            });
        }

        function deleteStudent(studentID) {
            if (confirm('Are you sure you want to delete this student?')) {
                $.ajax({
                    type: 'POST',
                    url: '',
                    data: { delete_id: studentID },
                    success: function (response) {
                        var res = JSON.parse(response);
                        if (res.status == 'success') {
                            document.getElementById('row-' + studentID).remove();
                            alert('Student deleted successfully.');
                        } else {
                            alert('Error deleting student.');
                        }
                    }
                });
            }
        }

        function updateStudent(studentID) {
            var level = document.getElementById('level-' + studentID).value;
            var semester = document.getElementById('semester-' + studentID).value;
            $.ajax({
                type: 'POST',
                url: '',
                data: { update_id: studentID, level: level, semester: semester },
                success: function (response) {
                    var res = JSON.parse(response);
                    if (res.status == 'success') {
                        alert('Student updated successfully.');
                    } else {
                        alert('Error updating student.');
                    }
                },
                error: function () {
                    alert('Error: Unable to update student.');
                }
            });
        }
    </script>

</body>

</html>

<?php
// Close the database connection
$db->close();
?>
