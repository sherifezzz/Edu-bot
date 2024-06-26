<?php
header('Content-Type: application/json');

// Database connection parameters
$host = 'localhost';
$db = 'chatbot_login';
$user = 'root';
$pass = '';

// Create a new PDO instance
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

// Initialize the data array
$data = [
    'studentsCount' => 0,
    'professorsCount' => 0,
    'adminsCount' => 0,
    'coursesCount' => 0,
    'activeTasksCount' => 0,
    'coursesByLevel' => [
        'labels' => [],
        'datasets' => []
    ],
    'coursesBySemester' => [
        'semester1' => 0,
        'semester2' => 0
    ],
    'studentEnrollment' => [
        'labels' => [],
        'datasets' => []
    ],
    'tasksByType' => [
        'quizzes' => 0,
        'assignments' => 0
    ],
    'tasksPerCourse' => [
        'labels' => [],
        'datasets' => []
    ]
];

try {
    // Fetch total students, professors, and admins
    $data['studentsCount'] = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
    $data['professorsCount'] = $pdo->query("SELECT COUNT(*) FROM professors")->fetchColumn();
    $data['adminsCount'] = $pdo->query("SELECT COUNT(*) FROM admins")->fetchColumn();

    // Fetch total courses
    $data['coursesCount'] = $pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn();

    // Fetch active tasks (quizzes and assignments)
    $data['activeTasksCount'] = $pdo->query("SELECT COUNT(*) FROM tasks")->fetchColumn();

    // Fetch courses by level
    $coursesByLevelQuery = $pdo->prepare("
        SELECT levels.level_name AS level, COUNT(courses.course_id) AS count
        FROM courses
        JOIN levels ON courses.level_id = levels.level_id
        GROUP BY courses.level_id
    ");
    $coursesByLevelQuery->execute();
    $coursesByLevelRows = $coursesByLevelQuery->fetchAll(PDO::FETCH_ASSOC);

    $coursesByLevelData = [];
    foreach ($coursesByLevelRows as $row) {
        $data['coursesByLevel']['labels'][] = $row['level'];
        $coursesByLevelData[] = $row['count'];
    }
    $data['coursesByLevel']['datasets'][] = [
        'label' => 'Courses by Level',
        'data' => $coursesByLevelData,
        'backgroundColor' => 'rgba(54, 162, 235, 0.2)',
        'borderColor' => 'rgba(54, 162, 235, 1)',
        'borderWidth' => 1
    ];

    // Fetch courses by semester
    $data['coursesBySemester']['semester1'] = $pdo->query("SELECT COUNT(*) FROM courses WHERE semester = '1'")->fetchColumn();
    $data['coursesBySemester']['semester2'] = $pdo->query("SELECT COUNT(*) FROM courses WHERE semester = '2'")->fetchColumn();

    // Fetch student enrollment per course
    $studentEnrollmentQuery = $pdo->prepare("
        SELECT courses.course_name AS course, COUNT(students_courses.student_id) AS count
        FROM students_courses
        JOIN courses ON students_courses.course_id = courses.course_id
        GROUP BY students_courses.course_id
    ");
    $studentEnrollmentQuery->execute();
    $studentEnrollmentRows = $studentEnrollmentQuery->fetchAll(PDO::FETCH_ASSOC);

    $studentEnrollmentData = [];
    foreach ($studentEnrollmentRows as $row) {
        $data['studentEnrollment']['labels'][] = $row['course'];
        $studentEnrollmentData[] = $row['count'];
    }
    $data['studentEnrollment']['datasets'][] = [
        'label' => 'Student Enrollment per Course',
        'data' => $studentEnrollmentData,
        'backgroundColor' => 'rgba(255, 159, 64, 0.2)',
        'borderColor' => 'rgba(255, 159, 64, 1)',
        'borderWidth' => 1
    ];

    // Fetch tasks by type
    $data['tasksByType']['quizzes'] = $pdo->query("SELECT COUNT(*) FROM tasks WHERE task_type = 'Quiz'")->fetchColumn();
    $data['tasksByType']['assignments'] = $pdo->query("SELECT COUNT(*) FROM tasks WHERE task_type = 'Assignment'")->fetchColumn();

    // Fetch tasks per course
    $tasksPerCourseQuery = $pdo->prepare("
        SELECT courses.course_name AS course, COUNT(tasks.task_id) AS count
        FROM tasks
        JOIN courses ON tasks.course_id = courses.course_id
        GROUP BY tasks.course_id
    ");
    $tasksPerCourseQuery->execute();
    $tasksPerCourseRows = $tasksPerCourseQuery->fetchAll(PDO::FETCH_ASSOC);

    $tasksPerCourseData = [];
    foreach ($tasksPerCourseRows as $row) {
        $data['tasksPerCourse']['labels'][] = $row['course'];
        $tasksPerCourseData[] = $row['count'];
    }
    $data['tasksPerCourse']['datasets'][] = [
        'label' => 'Tasks per Course',
        'data' => $tasksPerCourseData,
        'backgroundColor' => 'rgba(153, 102, 255, 0.2)',
        'borderColor' => 'rgba(153, 102, 255, 1)',
        'borderWidth' => 1
    ];

    // Output the JSON data
    echo json_encode($data);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Query execution error: ' . $e->getMessage()]);
}
?>
