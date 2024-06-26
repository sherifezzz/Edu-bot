<?php
session_start();

if (!isset($_SESSION['professorId'])) {
    header('Location: login.php');
    exit();
}

$professorId = $_SESSION['professorId'];
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "chatbot_login";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$requestMethod = $_SERVER['REQUEST_METHOD'];

if ($requestMethod === 'GET' && isset($_GET['action'])) {
    $action = $_GET['action'];

    if ($action === 'fetch') {
        fetchTasks($conn);
    } elseif ($action === 'delete' && isset($_GET['task_id'])) {
        deleteTask($conn, $_GET['task_id']);
    } elseif ($action === 'getTask' && isset($_GET['task_id'])) {
        getTask($conn, $_GET['task_id']);
    }
} elseif ($requestMethod === 'DELETE') {
    parse_str(file_get_contents("php://input"), $data);
    if (isset($data['task_id'])) {
        deleteTask($conn, $data['task_id']);
    }
} elseif ($requestMethod === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'modify') {
        modifyTask($conn);
    }
}

$conn->close();

function fetchTasks($conn)
{
    $sql = "SELECT t.*, p.first_name, p.last_name, c.course_name 
        FROM tasks t 
        JOIN professors p ON t.professor_id = p.professor_id 
        JOIN courses c ON t.course_id = c.course_id
        WHERE t.start_date >= NOW()";
    $result = $conn->query($sql);

    $tasks = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $tasks[] = $row;
        }
    }
    echo json_encode($tasks);
}

function deleteTask($conn, $taskId)
{
    $sql = "DELETE FROM tasks WHERE task_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $taskId);

    if ($stmt->execute()) {
        echo "Task deleted successfully";
    } else {
        echo "Error deleting task: " . $stmt->error;
    }

    $stmt->close();
}

function getTask($conn, $taskId)
{
    $sql = "SELECT * FROM tasks WHERE task_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $taskId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode($result->fetch_assoc());
    } else {
        echo json_encode(['error' => 'Task not found']);
    }

    $stmt->close();
}

function modifyTask($conn)
{
    $taskId = $_POST['taskId'];
    $taskType = $_POST['taskType'];
    $noteText = $_POST['noteText'];
    $startDate = $_POST['startDate'];
    $endDate = $_POST['endDate'];
    $courseID = $_POST['courseID'];

    $sql = "UPDATE tasks SET task_type = ?, start_date = ?, end_date = ?, note = ?, course_id = ? WHERE task_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssii", $taskType, $startDate, $endDate, $noteText, $courseID, $taskId);

    if ($stmt->execute()) {
        echo "Task updated successfully";
    } else {
        echo "Error updating task: " . $stmt->error;
    }

    $stmt->close();
}
?>