<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Interactive Dashboard</title>
    <link rel="stylesheet" href="Dashboard_Style.css">
    <style>
        .current-day {
            background-color: #f0f0f0;
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
                <a href="login.php">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="header">
            <h1>Dashboard</h1>
        </div>
        <div class="timeline">
            <h2>Timeline</h2>
            <div class="timeline-controls">
                <button onclick="showNext7Days()">Next 7 days</button>
                <button onclick="sortByDates()">Sort by dates</button>
                <input type="text" id="searchInput" placeholder="Search by activity type or name"
                    onkeyup="searchActivities()">
            </div>
            <div id="activityList">Loading activities...</div>
        </div>
        <div class="calendar">
            <h2>Calendar</h2>
            <div class="calendar-nav">
                <button onclick="prevMonth()">Previous Month</button>
                <button onclick="nextMonth()">Next Month</button>
            </div>
            <h2 id="currentMonth"></h2>
            <table>
                <thead>
                    <tr>
                        <th>Sat</th>
                        <th>Sun</th>
                        <th>Mon</th>
                        <th>Tue</th>
                        <th>Wed</th>
                        <th>Thu</th>
                        <th>Fri</th>
                    </tr>
                </thead>
                <tbody id="calendarBody">
                </tbody>
            </table>
            <button onclick="openModal()">New event</button>
        </div>
    </div>

    <script>
        let currentMonth = new Date().getMonth();
        let currentYear = new Date().getFullYear();

        async function fetchTasks() {
            try {
                const response = await fetch('tasks.php?action=fetch');
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                const tasks = await response.json();
                return tasks;
            } catch (error) {
                console.error('Error fetching tasks:', error);
                return [];
            }
        }

        function displayTasks(tasks) {
            const calendarBody = document.getElementById('calendarBody');
            const allCells = calendarBody.querySelectorAll('td');

            allCells.forEach(cell => {
                cell.innerHTML = '';
                const span = document.createElement('span');
                span.classList.add('date-number');
                const dayNumber = cell.getAttribute('data-date');
                span.innerText = dayNumber;
                cell.appendChild(span);
            });

            tasks.forEach(task => {
                const taskDate = new Date(task.end_date);
                if (taskDate.getMonth() === currentMonth && taskDate.getFullYear() === currentYear) {
                    const day = taskDate.getDate();
                    const cell = calendarBody.querySelector(`td[data-date="${day}"]`);
                    if (cell) {
                        const taskDiv = document.createElement('div');
                        taskDiv.classList.add('task');
                        taskDiv.innerHTML = `
                            <strong>${task.task_type}</strong>
                        `;
                        cell.appendChild(taskDiv);
                    }
                }
            });
        }

        function displayTasksInTimeline(tasks) {
            const activityList = document.getElementById('activityList');
            activityList.innerHTML = '';
            if (tasks.length === 0) {
                activityList.innerText = 'No activities require action';
            } else {
                tasks.forEach(task => {
                    const taskDiv = document.createElement('div');
                    taskDiv.classList.add('task');
                    taskDiv.innerHTML = `
                        <strong>${task.task_type}</strong><br>
                        ${task.start_date} - ${task.end_date}<br>
                        Doctor: ${task.first_name} ${task.last_name}<br>
                        Course: ${task.course_name}<br>
                        <button onclick="modifyTask(${task.task_id})">Modify</button>
                        <button onclick="deleteTask(${task.task_id})">Delete</button>
                    `;
                    activityList.appendChild(taskDiv);
                });
            }
        }

        function showNext7Days() {
            fetchTasks().then(tasks => {
                const now = new Date();
                const next7Days = new Date();
                next7Days.setDate(now.getDate() + 7);

                const next7DaysTasks = tasks.filter(task => {
                    const taskDate = new Date(task.end_date);
                    return taskDate >= now && taskDate <= next7Days;
                });

                displayTasksInTimeline(next7DaysTasks);
            });
        }

        function sortByDates() {
            fetchTasks().then(tasks => {
                const sortedTasks = tasks.sort((a, b) => new Date(a.end_date) - new Date(b.end_date));
                displayTasksInTimeline(sortedTasks);
            });
        }

        function searchActivities() {
            const searchValue = document.getElementById('searchInput').value.toLowerCase();
            fetchTasks().then(tasks => {
                const filteredTasks = tasks.filter(task =>
                    task.task_type.toLowerCase().includes(searchValue) ||
                    task.course_name.toLowerCase().includes(searchValue)
                );
                displayTasksInTimeline(filteredTasks);
            });
        }

        function modifyTask(taskId) {
            window.location.href = `modify_task.php?task_id=${taskId}`;
        }

        function deleteTask(taskId) {
            if (confirm('Are you sure you want to delete this task?')) {
                fetch(`tasks.php?action=delete&task_id=${taskId}`, { method: 'GET' })
                    .then(response => response.text())
                    .then(result => {
                        populateCalendar(); // Refresh the calendar to show updated tasks
                        showNext7Days(); // Refresh the timeline to show updated tasks
                    })
                    .catch(error => console.error('Error:', error));
            }
        }



        async function populateCalendar() {
            const calendarBody = document.getElementById('calendarBody');
            const firstDayOfMonth = new Date(currentYear, currentMonth, 1).getDay();
            const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();
            const currentDay = new Date().getDate();

            const adjustedFirstDay = (firstDayOfMonth + 6) % 7;

            document.getElementById('currentMonth').innerText = new Date(currentYear, currentMonth, 1).toLocaleString('default', { month: 'long' });

            calendarBody.innerHTML = '';
            let date = 1;

            for (let i = 0; i < 6; i++) {
                const row = document.createElement('tr');

                for (let j = 0; j < 7; j++) {
                    const cell = document.createElement('td');
                    if (i === 0 && j < adjustedFirstDay) {
                        cell.classList.add('empty');
                    } else if (date > daysInMonth) {
                        cell.classList.add('empty');
                    } else {
                        const span = document.createElement('span');
                        span.innerText = date;
                        cell.appendChild(span);

                        const tooltip = document.createElement('div');
                        tooltip.classList.add('tooltiptext');
                        tooltip.innerText = `${new Date(currentYear, currentMonth, date).toLocaleString('default', { month: 'long' })} ${date}, ${currentYear}`;
                        cell.classList.add('tooltip');
                        cell.appendChild(tooltip);

                        cell.setAttribute('data-date', date);

                        if (date === currentDay && currentMonth === new Date().getMonth() && currentYear === new Date().getFullYear()) {
                            cell.classList.add('current-day');
                        }

                        cell.addEventListener('click', () => alert(`You clicked on day ${date}`));
                        date++;
                    }
                    row.appendChild(cell);
                }
                calendarBody.appendChild(row);
            }

            const tasks = await fetchTasks();
            displayTasks(tasks);
            showNext7Days();
        }

        function prevMonth() {
            currentMonth--;
            if (currentMonth < 0) {
                currentMonth = 11;
                currentYear--;
            }
            populateCalendar();
        }

        function nextMonth() {
            currentMonth++;
            if (currentMonth > 11) {
                currentMonth = 0;
                currentYear++;
            }
            populateCalendar();
        }

        window.onload = populateCalendar;
    </script>
</body>

</html>