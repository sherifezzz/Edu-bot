<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="utf-8">
    <title>Edu_bot</title>
    <link rel="stylesheet" href="css/students.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Google Fonts Link For Icons -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
</head>

<body>
    <!-- Chats container -->
    <div class="chat-container"></div>
    <!-- Typing container -->
    <div class="typing-container">
        <div class="typing-content">
            <div class="typing-textarea">
                <textarea id="chat-input" spellcheck="false" placeholder="Enter a prompt here" required></textarea>
                <span id="send-btn" class="material-symbols-rounded">send</span>
            </div>
            <div class="typing-controls">
                <span id="theme-btn" class="material-symbols-rounded">dark_mode</span>
                <span id="delete-btn" class="material-symbols-rounded">delete</span>
                <span><button id="logout-btn" class="material-symbols-rounded">Logout</button></span>
            </div>
        </div>
    </div>
    <script>
        const chatInput = document.querySelector("#chat-input");
        const sendButton = document.querySelector("#send-btn");
        const chatContainer = document.querySelector(".chat-container");
        const themeButton = document.querySelector("#theme-btn");
        const deleteButton = document.querySelector("#delete-btn");

        const loadDataFromLocalstorage = () => {
            const themeColor = localStorage.getItem("themeColor");
            if (themeColor === "light_mode") {
                document.body.classList.add("light-mode");
            } else {
                document.body.classList.remove("light-mode");
            }
            themeButton.innerText = themeColor;

            const defaultText = `<div class="default-text">
                            <h1>Edu Bot</h1>
                            <p>Software Designed To Provide Assistance And Learning</p>
                        </div>`;
            chatContainer.innerHTML = localStorage.getItem("all-chats") || defaultText;
            if (!localStorage.getItem("all-chats")) {
                defaultTextRemoved = true;
            }
            chatContainer.scrollTo(0, chatContainer.scrollHeight);
        };

        const createChatElement = (content, className) => {
            const chatDiv = document.createElement("div");
            chatDiv.classList.add("chat", className);
            chatDiv.innerHTML = content;
            return chatDiv;
        };

        const API_URL = "http://127.0.0.1:5005/edubot";

        const getChatResponse = async (question) => {
            let formdata = new FormData();
            formdata.append('question', question);
            formdata.append('student-id', '<?php echo $_SESSION['student_id']; ?>');
            const requestOptions = {
                method: "POST",
                body: formdata,
            };
            try {
                const response = await fetch(API_URL, requestOptions);
                const responseData = await response.json();
                return responseData;
            } catch (error) {
                console.error("Error fetching response from API:", error);
                return { error: "Error processing request." };
            } f
        };

        const displayApiResponse = (userQuestion) => {
            const typingAnimation = `<div class="chat-content">
                            <div class="chat-details">
                                <img src="images/chatbot.png" alt="chatbot-img">
                                <div class="typing-animation">
                                    <div class="typing-dot" style="--delay: 0.2s"></div>
                                    <div class="typing-dot" style="--delay: 0.3s"></div>
                                    <div class="typing-dot" style="--delay: 0.4s"></div>
                                </div>
                            </div>
                        </div>`;
            chatContainer.appendChild(createChatElement(typingAnimation, 'outgoing'));
            chatContainer.scrollTo(0, chatContainer.scrollHeight);

            getChatResponse(userQuestion)
                .then(responseData => {
                    if (chatContainer.lastChild.classList.contains('outgoing')) {
                        chatContainer.removeChild(chatContainer.lastChild);
                    }
                    if (responseData && (responseData.response || responseData.error)) {
                        const responseMessage = `<div class="chat-content">
                                        <div class="chat-details">
                                            <img src="images/chatbot.png" alt="chatbot-img">
                                            <p>${responseData.response || responseData.error}</p>
                                        </div>
                                    </div>`;
                        chatContainer.appendChild(createChatElement(responseMessage, 'incoming'));
                    }
                    chatContainer.scrollTo(0, chatContainer.scrollHeight);
                })
                .catch(error => {
                    console.error("Error processing request:", error);
                });
        };

        const handleOutgoingChat = () => {
            const userText = chatInput.value.trim();
            if (!userText) return;

            chatInput.value = "";
            chatInput.style.height = `${initialInputHeight}px`;

            const html = `<div class="chat-content">
                                <div class="chat-details">
                                    <img src="images/user.jpg" alt="user-img">
                                    <p>${userText}</p>
                                </div>
                            </div>`;
            chatContainer.appendChild(createChatElement(html, "outgoing"));
            chatContainer.scrollTo(0, chatContainer.scrollHeight);

            setTimeout(() => displayApiResponse(userText), 500);
        };

        function fetchStudentCourses() {
            let Formdata = new FormData();
            Formdata.append('student-id', '<?php echo $_SESSION['student_id']; ?>');
            fetch('http://127.0.0.1:5005/courses', {
                method: 'POST',
                body: Formdata
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Unauthorized');
                    }
                    return response.json();
                })
                .then(data => {
                    console.log(data);
                })
                .catch(error => {
                    alert(error.message);
                });
        }

        fetchStudentCourses();

        deleteButton.addEventListener("click", () => {
            if (confirm("Are you sure you want to delete all the chats?")) {
                localStorage.removeItem("all-chats");
                loadDataFromLocalstorage();
            }
        });

        themeButton.addEventListener("click", () => {
            document.body.classList.toggle("light-mode");
            const themeMode = document.body.classList.contains("light-mode") ? "light_mode" : "dark_mode";
            localStorage.setItem("themeColor", themeMode);
            themeButton.innerText = themeMode;
        });

        const initialInputHeight = chatInput.scrollHeight;

        chatInput.addEventListener("input", () => {
            chatInput.style.height = `${initialInputHeight}px`;
            chatInput.style.height = `${chatInput.scrollHeight}px`;
        });

        chatInput.addEventListener("keydown", (e) => {
            if (e.key === "Enter" && !e.shiftKey && window.innerWidth > 800) {
                e.preventDefault();
                handleOutgoingChat();
            }
        });

        const logoutButton = document.querySelector("#logout-btn");
        logoutButton.addEventListener("click", confirmLogout);

        function confirmLogout() {
            const logoutConfirmed = confirm("Are you sure you want to logout?");
            if (logoutConfirmed) {
                window.location.href = "login.php";
            }
        }

        loadDataFromLocalstorage();
        sendButton.addEventListener("click", handleOutgoingChat);
    </script>
</body>

</html>