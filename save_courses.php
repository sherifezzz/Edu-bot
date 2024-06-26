<?php
// Establish database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "chatbot_login";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve selected courses
    $selectedCourses = $_POST['courses'];

    // Assuming the professor ID is retrieved from the logged-in user session
    session_start();
    if (isset($_SESSION['professor_id'])) {
        $professorId = $_SESSION['professor_id'];
    } else {
        // Handle case where professor ID is not available
        echo "<script>alert('Professor ID not found');</script>";
        exit; // Stop further execution of the script
    }
    
    // Prepare SQL statement to insert data into the Course_Professor table
    $insert_sql = "INSERT INTO Course_Professor (ProfessorID, CourseID) VALUES (?, ?)";
    $insert_stmt = $conn->prepare($insert_sql);

    // Bind parameters
    $insert_stmt->bind_param("ii", $professorId, $courseId);

    // Array to store messages for each course
    $courseMessages = [];

    // Execute the statements for each selected course
    foreach ($selectedCourses as $courseId) {
        // Execute the insert statement for each course
        if ($insert_stmt->execute()) {
            $courseMessages[] = "Course saved successfully for ProfessorID $professorId and CourseID $courseId";
        } else {
            $courseMessages[] = "Error inserting course for ProfessorID $professorId and CourseID $courseId: " . $conn->error;
        }
    }

    // Output course messages as alerts
    foreach ($courseMessages as $message) {
        echo "<script>alert('$message');</script>";
    }

    // Close statement
    $insert_stmt->close();
}

// Close connection
$conn->close();
?>
