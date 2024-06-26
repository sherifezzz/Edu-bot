<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="dashboard_styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    <div class="sidebar">
        <a href="dashbord.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="manage_professor.php"><i class="fas fa-chalkboard-teacher"></i> Manage Professors</a>
        <a href="manage_students.php"><i class="fas fa-user-graduate"></i> Manage Students</a>
        <a href="manage_courses.php"><i class="fas fa-book"></i> Manage Courses</a>
    </div>
    <div class="content">
        <header>
            <h1>Welcome to Admin Dashboard</h1>
        </header>

        <!-- Overview Section -->
        <div class="overview-section">
            <div class="metric metric1">
                <i class="fas fa-user-graduate"></i>
                <div class="count" id="studentsCount">0</div>
                <div class="label">Total Students</div>
            </div>
            <div class="metric metric2">
                <i class="fas fa-chalkboard-teacher"></i>
                <div class="count" id="professorsCount">0</div>
                <div class="label">Total Professors</div>
            </div>
            <div class="metric metric3">
                <i class="fas fa-user-shield"></i>
                <div class="count" id="adminsCount">0</div>
                <div class="label">Total Admins</div>
            </div>
            <div class="metric metric4">
                <i class="fas fa-book"></i>
                <div class="count" id="coursesCount">0</div>
                <div class="label">Total Courses</div>
            </div>
            <div class="metric metric5">
                <i class="fas fa-tasks"></i>
                <div class="count" id="activeTasksCount">0</div>
                <div class="label">Active Tasks</div>
            </div>
        </div>

        <!-- Course Management Section -->
        <div class="course-management-section">
            <h2>Course Management</h2>
            <div class="chart-container">
                <canvas id="coursesByLevelChart"></canvas>
            </div>
            <div class="chart-container">
                <canvas id="coursesBySemesterChart"></canvas>
            </div>
            <div class="chart-container">
                <canvas id="studentEnrollmentChart"></canvas>
            </div>
        </div>

        <!-- Task Management Section -->
        <div class="task-management-section">
            <h2>Task Management</h2>
            <div class="chart-container">
                <canvas id="tasksByTypeChart"></canvas>
            </div>
            <div class="chart-container">
                <canvas id="tasksPerCourseChart"></canvas>
            </div>
        </div>
    </div>

    <script>
        fetch('get_data.php')
            .then(response => response.json())
            .then(data => {
                document.getElementById('studentsCount').textContent = data.studentsCount;
                document.getElementById('professorsCount').textContent = data.professorsCount;
                document.getElementById('adminsCount').textContent = data.adminsCount;
                document.getElementById('coursesCount').textContent = data.coursesCount;
                document.getElementById('activeTasksCount').textContent = data.activeTasksCount;

                new Chart(document.getElementById('coursesByLevelChart'), {
                    type: 'bar',
                    data: {
                        labels: data.coursesByLevel.labels,
                        datasets: data.coursesByLevel.datasets
                    },
                    options: {
                        maintainAspectRatio: false,
                        responsive: true
                    }
                });

                new Chart(document.getElementById('coursesBySemesterChart'), {
                    type: 'pie',
                    data: {
                        labels: ['Semester 1', 'Semester 2'],
                        datasets: [{
                            data: [data.coursesBySemester.semester1, data.coursesBySemester.semester2],
                            backgroundColor: ['#FF6384', '#36A2EB']
                        }]
                    },
                    options: {
                        maintainAspectRatio: false,
                        responsive: true
                    }
                });

                new Chart(document.getElementById('studentEnrollmentChart'), {
                    type: 'bar',
                    data: {
                        labels: data.studentEnrollment.labels,
                        datasets: data.studentEnrollment.datasets
                    },
                    options: {
                        maintainAspectRatio: false,
                        responsive: true
                    }
                });

                new Chart(document.getElementById('tasksByTypeChart'), {
                    type: 'pie',
                    data: {
                        labels: ['Quizzes', 'Assignments'],
                        datasets: [{
                            data: [data.tasksByType.quizzes, data.tasksByType.assignments],
                            backgroundColor: ['#FF6384', '#36A2EB']
                        }]
                    },
                    options: {
                        maintainAspectRatio: false,
                        responsive: true
                    }
                });

                new Chart(document.getElementById('tasksPerCourseChart'), {
                    type: 'bar',
                    data: {
                        labels: data.tasksPerCourse.labels,
                        datasets: data.tasksPerCourse.datasets
                    },
                    options: {
                        maintainAspectRatio: false,
                        responsive: true
                    }
                });
            })
            .catch(error => console.error('Error fetching data:', error));
    </script>
</body>

</html>
