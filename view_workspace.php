<?php
// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If user is not logged in, redirect to login page
if (!isset($_SESSION["username"]) || !isset($_SESSION["userID"])) {
    header("Location: login.html");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GIStory - View Workspace</title>
    <link rel="stylesheet" href="https://js.arcgis.com/4.19/esri/themes/light/main.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto+Mono:wght@400;700;900&display=swap">
    <style>
        :root {
            --primary-color: #CCA300;
            --dark-bg: #323437;
            --text-light: #D1D0C5;
            --danger: #FF0000;
            --danger-hover: #CC0000;
            --button-timing: cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes slideDown {
            from { transform: translateY(-100%); }
            to { transform: translateY(0); }
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.02); }
            100% { transform: scale(1); }
        }

        @keyframes fadeInScale {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }

        @keyframes modalSlideIn {
            from { transform: translateY(-100px) scale(0.8); opacity: 0; }
            to { transform: translateY(0) scale(1); opacity: 1; }
        }

        body, html {
            padding: 0;
            margin: 0;
            height: 100%;
            width: 100%;
            overflow: hidden;
            font-family: 'Roboto Mono', monospace;
        }

        #viewDiv {
            height: calc(100% - 60px);
            width: 100%;
            position: absolute;
            top: 60px;
            animation: fadeIn 0.8s ease-out;
        }

        #menuBar {
            position: sticky;
            height: 60px;
            background: var(--primary-color);
            display: flex;
            align-items: center;
            padding: 0 20px;
            z-index: 100;
            justify-content: space-between;
            box-shadow: 0 2px 15px rgba(204, 163, 0, 0.3);
            animation: slideDown 0.5s ease-out;
        }

        .menu-logo-container {
            flex: 1;
        }

        .menu-logo {
            height: 40px;
            margin-right: 20px;
            cursor: pointer;
            transition: all 0.4s var(--button-timing);
        }

        .menu-logo:hover {
            transform: scale(1.2) rotate(5deg);
            filter: brightness(1.2);
        }

        .menu-title {
            flex: 1;
            color: var(--dark-bg);
            font-size: clamp(20px, 4vw, 40px); 
            font-weight: 900;
            text-align: center;
            letter-spacing: 1px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
            animation: pulse 2s infinite;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            display: flex;
            align-items: center;
            justify-content: center;
            /* Add some padding on the sides */
            padding: 0 20px; 
            /* Prevent taking too much space */
            max-width: 80%; 
            margin: 0 auto; 
        }

        .menu-controls {
            flex: 1;
            display: flex;
            justify-content: flex-end;
        }

        .menu-button {
            background: var(--danger);
            border: none;
            color: white;
            font-size: 24px;
            padding: 8px 12px;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.4s var(--button-timing);
            position: relative;
            overflow: hidden;
        }

        .menu-button::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .menu-button:hover::after {
            width: 200px;
            height: 200px;
        }

        .menu-button:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
            background-color: var(--danger-hover);
        }

        .menu-options {
            display: none;
            position: absolute;
            top: 60px;
            right: 20px;
            background-color: var(--dark-bg);
            border: 1px solid var(--primary-color);
            border-radius: 4px;
            min-width: 150px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            z-index: 101;
            padding: 0;
            animation: fadeInScale 0.3s ease-out;
        }

        .menu-options li {
            list-style: none;
            margin: 0;
            padding: 0;
            width: 100%;
        }

        .menu-options a {
            display: block;
            padding: 10px 20px;
            color: var(--text-light);
            text-decoration: none;
            width: 100%;
            box-sizing: border-box;
            transition: all 0.3s ease;
            position: relative;
        }

        .menu-options a::before {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 0;
            height: 2px;
            background: var(--primary-color);
            transition: width 0.3s ease;
        }

        .menu-options a:hover::before {
            width: 100%;
        }

        .menu-options a:hover {
            background-color: var(--primary-color);
            color: var(--dark-bg);
            padding-left: 25px;
        }

        /* Basemap gallery and expand widget styles */
        .esri-expand__container {
            background-color: var(--dark-bg) !important;
        }
        
        .esri-expand__icon {
            color: var(--text-light) !important;
        }
        
        .esri-expand .esri-widget--button {
            background-color: var(--dark-bg) !important;
            border: 1px solid var(--primary-color) !important;
        }
        
        .esri-expand .esri-widget--button:hover {
            background-color: var(--primary-color) !important;
        }
        
        .esri-basemap-gallery {
            background-color: var(--dark-bg) !important;
        }
        
        .esri-basemap-gallery__item {
            background-color: var(--dark-bg) !important;
            border: 1px solid var(--primary-color) !important;
        }
        
        .esri-basemap-gallery__item:hover {
            border-color: var(--primary-color) !important;
            box-shadow: 0 0 8px var(--primary-color) !important;
        }
        
        .esri-basemap-gallery__item-title {
            color: var(--text-light) !important;
            font-family: 'Roboto Mono', monospace !important;
        }
        
        .esri-basemap-gallery__item--selected {
            border-color: var(--primary-color) !important;
            box-shadow: 0 0 12px var(--primary-color) !important;
        }
        
        .esri-basemap-gallery__item-thumbnail {
            border: 1px solid var(--primary-color) !important;
        }

        /* Navigation widget styles */
        .esri-widget--button {
            background-color: var(--dark-bg) !important;
            border: 1px solid var(--primary-color) !important;
            color: var(--text-light);
        }

        .esri-widget--button:hover {
            background-color: var(--primary-color) !important;
            color: var(--dark-bg);
        }

        /* Special lighter color for navigation toggle icons */
        .esri-navigation-toggle .esri-icon-pan,
        .esri-navigation-toggle .esri-icon-rotate {
            color: #ffffff !important;
        }

        /* Hover states */
        .esri-zoom .esri-widget--button:hover,
        .esri-navigation-toggle .esri-widget--button:hover,
        .esri-compass .esri-widget--button:hover,
        .esri-home .esri-widget--button:hover {
            background-color: var(--primary-color) !important;
        }

        .esri-zoom .esri-widget--button:hover .esri-icon,
        .esri-navigation-toggle .esri-widget--button:hover .esri-icon,
        .esri-compass .esri-widget--button:hover .esri-icon,
        .esri-home .esri-widget--button:hover .esri-icon,
        .esri-navigation-toggle .esri-widget--button:hover .esri-icon-pan,
        .esri-navigation-toggle .esri-widget--button:hover .esri-icon-rotate {
            color: var(--dark-bg) !important;
        }

        .popup-content {
            margin: 10px 0;
            animation: fadeIn 0.8s ease-out;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }

        .popup-content h3 {
            align-self: flex-start;
            width: 100%;
            margin-bottom: 5px;
        }

        .popup-content img {
            max-width: 100%;
            height: auto;
            display: block;
        }

        .popup-content iframe {
            width: 100%;
            height: 200px;
        }

        .fullscreen-button {
            background-color: var(--primary-color);
            color: var(--dark-bg);
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.4s var(--button-timing);
            position: relative;
            overflow: hidden;
            font-family: 'Roboto Mono', monospace;
            width: 100%;
            margin-top: 10px;
        }

        .fullscreen-button::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .fullscreen-button:hover::after {
            width: 200px;
            height: 200px;
        }

        .fullscreen-button:hover {
            /*transform: scale(1.05);*/
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
        }

        .fullscreen-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background-color: rgba(0,0,0,0.9);
            z-index: 1001;
            justify-content: center;
            align-items: center;
            animation: fadeIn 0.3s ease-out;
        }

        .fullscreen-content {
            max-width: 90%;
            max-height: 90%;
            overflow: auto;
            background-color: var(--dark-bg);
            padding: 20px;
            border-radius: 8px;
            color: var(--text-light);
            animation: modalSlideIn 0.5s var(--button-timing);
        }

        .close-fullscreen {
            position: absolute;
            top: 20px;
            right: 30px;
            color: #f1f1f1;
            font-size: 40px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .close-fullscreen:hover {
            color: var(--primary-color);
            transform: rotate(90deg);
        }

        #chatbot-icon {
            position: fixed;
            bottom: 30px;
            left: 20px;
            background-color: var(--primary-color);
            color: var(--dark-bg);
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            font-size: 24px;
            z-index: 1000;
            transition: all 0.4s var(--button-timing);
            box-shadow: 0 2px 15px rgba(204, 163, 0, 0.3);
        }

        #chatbot-icon:hover {
            transform: scale(1.1);
            box-shadow: 0 4px 20px rgba(204, 163, 0, 0.4);
        }

        #chatbot-container {
            position: fixed;
            bottom: 90px;
            left: 20px;
            width: 300px;
            height: 400px;
            border: 2px solid var(--primary-color);
            background-color: var(--dark-bg);
            display: none;
            flex-direction: column;
            z-index: 1000;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            font-family: 'Roboto Mono', monospace;
            animation: fadeInScale 0.3s var(--button-timing);
        }

        #chatbot-messages {
            flex-grow: 1;
            overflow-y: auto;
            padding: 15px;
            color: var(--text-light);
        }

        #chatbot-messages div {
            margin-bottom: 10px;
            animation: fadeIn 0.3s ease-out;
        }

        #chatbot-input {
            display: flex;
            padding: 15px;
            background-color: rgba(204, 163, 0, 0.1);
            border-top: 1px solid var(--primary-color);
            border-radius: 0 0 8px 8px;
        }

        #user-input {
            flex-grow: 1;
            margin-right: 10px;
            padding: 8px 12px;
            border: 1px solid var(--primary-color);
            background-color: var(--dark-bg);
            color: var(--text-light);
            border-radius: 4px;
            font-family: 'Roboto Mono', monospace;
        }

        #user-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 5px rgba(204, 163, 0, 0.5);
        }

        #send-button {
            background-color: var(--primary-color);
            color: var(--dark-bg);
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-family: 'Roboto Mono', monospace;
            font-weight: bold;
            transition: all 0.4s var(--button-timing);
        }

        #send-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(204, 163, 0, 0.3);
        }

        #searchWidgetDiv {
            position: absolute;
            bottom: 20px;
            right: 20px;
            z-index: 99;
        }

        .esri-search {
            width: 300px;
        }

        .esri-search__input {
            background-color: var(--dark-bg);
            color: var(--text-light);
            border: 1px solid var(--primary-color);
            font-family: 'Roboto Mono', monospace;
        }

        .esri-search__input::placeholder {
            color: #646669;
            font-family: 'Roboto Mono', monospace;
        }

        .esri-search__submit-button {
            background-color: var(--dark-bg) !important;
            border: 1px solid var(--primary-color) !important;
            transition: all 0.3s ease !important;
        }

        .esri-search__submit-button .esri-icon {
            color: var(--text-light) !important;
        }

        .esri-search__submit-button:hover {
            background-color: var(--primary-color) !important;
        }

        .esri-search__submit-button:hover .esri-icon {
            color: var(--dark-bg) !important;
        }

        .esri-search__suggestions-menu {
            background-color: var(--dark-bg);
            color: var(--text-light);
            border: 1px solid var(--primary-color);
        }

        .esri-search__suggestion-item:hover,
        .esri-search__suggestion-item--active {
            background-color: var(--primary-color);
            color: var(--dark-bg);
        }

        .esri-widget {
            font-family: 'Roboto Mono', monospace !important;
        }

        .timeline-marker {
            position: absolute;
            width: 12px;
            height: 12px;
            background-color: #4B77BE;
            border-radius: 50%;
            transform: translate(-50%, -50%);
            top: 50%;
            cursor: pointer;
            transition: all 0.3s ease;
            z-index: 1002;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.3);
        }

        .timeline-marker:hover {
            transform: translate(-50%, -50%) scale(1.5);
            box-shadow: 0 0 10px rgba(255, 215, 0, 0.5);
            background-color: #FFD700;
        }

        .timeline-marker.active {
            background-color: #FFD700;
            transform: translate(-50%, -50%) scale(1.3);
            box-shadow: 0 0 15px rgba(255, 215, 0, 0.6);
        }

        #timelineContainer {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            width: 60%;
            height: 70px;
            background-color: rgba(50, 52, 55, 0.9);
            border: 1px solid #e2b714;
            border-radius: 8px;
            z-index: 1000;
            display: flex;
            align-items: center;
            padding: 10px;
            overflow: visible;
        }

        #timelineTrack {
            position: relative;
            height: 60px;
            background-color: rgba(0, 0, 0, 0.3);
            border-radius: 4px;
            overflow-x: auto;
            overflow-y: visible;
            scroll-behavior: smooth;
            flex-grow: 1;
            margin: 0 10px;
        }

        #timelinePoints {
            position: absolute;
            height: 100%;
            width: calc(100% - 40px);
            display: flex;
            align-items: center;
            left: 20px;
            overflow: visible;
        }

        #timelineControls {
            display: flex;
            align-items: center;
            width: 100%;
            gap: 10px;
        }

        #timelineControls button {
            background-color: #e2b714;
            border: none;
            color: #323437;
            width: 30px;
            height: 40px;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }

        #timelineControls button:hover {
            background-color: #ffd700;
            transform: scale(1.1);
        }

        #timelineControls button:disabled {
            background-color: rgba(226, 183, 20, 0.3);
            cursor: not-allowed;
            transform: none;
        }


        .timeline-marker::after {
            content: attr(data-date);
            position: absolute;
            top: -18px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 12px;
            color: #d1d0c5;
            white-space: nowrap;
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: 9999;
        }

        .timeline-marker:hover::after {
            opacity: 1;
        }

        #timelineTrack::-webkit-scrollbar {
            display: none;
        }
    </style>
</head>

<body>
    <div id="menuBar">
        <div class="menu-logo-container">
            <a href="dashboard.php" id="logo">
                <img src="LogoGistory.png" alt="GIStory Logo" class="menu-logo">
            </a>
        </div>
        <div class="menu-title" id="workspaceTitle"></div>
        <div class="menu-controls">
            <button id="menuButton" class="menu-button" aria-label="Menu">☰</button>
            <ul id="menuOptions" class="menu-options">
                <li><a href="dashboard.php">Workspace</a></li>
                <li><a href="logOut.php">Logout</a></li>
            </ul>
        </div>
    </div>

    <div id="viewDiv" role="application" aria-label="Interactive map"></div>
    <div id="timelineContainer">
    <div id="timelineControls">
        <button id="timelinePrev">◀</button>
        <div id="timelineTrack">
            <div id="timelinePoints"></div>
        </div>
        <button id="timelineNext">▶</button>
    </div>
</div>
    <div id="searchWidgetDiv"></div>

    <div id="fullscreenOverlay" class="fullscreen-overlay" role="dialog" aria-modal="true">
        <span class="close-fullscreen" aria-label="Close fullscreen view">&times;</span>
        <div id="fullscreenContent" class="fullscreen-content"></div>
    </div>

    <!-- Chatbot UI -->
    <div id="chatbot-icon">💬</div>
    <div id="chatbot-container">
        <div id="chatbot-messages"></div>
        <div id="chatbot-input">
            <input type="text" id="user-input" placeholder="Ask about this region...">
            <button id="send-button">Send</button>
        </div>
    </div>

    <script src="https://js.arcgis.com/4.19/"></script>
    <script>
            class GIStoryApp {
            constructor() {
                this.view = null;
                this.initializeUI();
                this.initializeChatbot();
                this.loadMap();
                this.initializeTimeline();
                this.initializeTimelineControls();
                
                // Add this to load the workspace title
                fetch(`get_workspace_title.php?id=${new URLSearchParams(window.location.search).get('id')}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            document.getElementById('workspaceTitle').textContent = data.title;
                        }
                    });
            }

            initializeUI() {
                this.menuButton = document.getElementById("menuButton");
                this.menuOptions = document.getElementById("menuOptions");
                this.fullscreenOverlay = document.getElementById('fullscreenOverlay');
                
                this.menuButton.addEventListener('click', () => {
                    this.menuOptions.style.display = 
                        this.menuOptions.style.display === "block" ? "none" : "block";
                });

                document.querySelector('.close-fullscreen').addEventListener('click', function() {
                    const fullscreenOverlay = document.getElementById('fullscreenOverlay');
                    fullscreenOverlay.style.display = 'none';
                    
                    // Find any iframes (videos) and reset their src
                    const iframes = fullscreenOverlay.getElementsByTagName('iframe');
                    for(let iframe of iframes) {
                        const currentSrc = iframe.src;
                        // Clear the source
                        iframe.src = ''; 
                        // Reset the source
                        iframe.src = currentSrc;  
                    }
                });

                this.fullscreenOverlay.addEventListener('click', function(e) {
                    if (e.target === this) {
                        this.style.display = 'none';
                        
                        // Find any iframes (videos) and reset their src
                        const iframes = this.getElementsByTagName('iframe');
                        for(let iframe of iframes) {
                            const currentSrc = iframe.src;
                            iframe.src = '';  
                            iframe.src = currentSrc;  
                        }
                    }
                });
            }

            initializeTimeline = () => {
                fetch(`get_timeline_points.php?id=${new URLSearchParams(window.location.search).get('id')}`)
                    .then(response => response.json())
                    .then(result => {
                        if (result.status === 'success') {
                            this.renderTimeline(result.data);
                        }
                    })
                    .catch(error => console.error('Error fetching timeline points:', error));
            }

            renderTimeline = (points) => {
                const timelinePoints = document.getElementById('timelinePoints');
                timelinePoints.innerHTML = '';

                points.forEach((point, index) => {
                    const marker = document.createElement('div');
                    marker.className = 'timeline-marker';
                    marker.style.left = `${(index / (points.length - 1)) * 100}%`;
                    
                    // Format the date nicely
                    const date = point.CustomDate || point.DateCreated.split(' ')[0];
                    const formattedDate = new Date(date).toLocaleDateString('en-US', {
                        year: 'numeric',
                        month: 'short',
                        day: 'numeric'
                    });
                    
                    marker.setAttribute('data-date', formattedDate);
                    marker.setAttribute('data-point-id', point.pointID);
                    
                    marker.addEventListener('click', () => {
                        this.view.goTo({
                            center: [parseFloat(point.longitude), parseFloat(point.latitude)],
                            zoom: 6
                        });
                    });

                    timelinePoints.appendChild(marker);
                });
            };

            initializeChatbot() {
                const chatbotIcon = document.getElementById('chatbot-icon');
                const chatbotContainer = document.getElementById('chatbot-container');
                const userInput = document.getElementById('user-input');
                const sendButton = document.getElementById('send-button');

                chatbotIcon.addEventListener('click', () => {
                    chatbotContainer.style.display = 
                        chatbotContainer.style.display === 'none' ? 'flex' : 'none';
                });

                sendButton.addEventListener('click', () => this.sendMessage());
                userInput.addEventListener('keypress', (e) => {
                    if (e.key === 'Enter') this.sendMessage();
                });
            }

            sendMessage() {
                const userInput = document.getElementById('user-input');
                const message = userInput.value.trim();
                if (message) {
                    this.addMessage('User', message);
                    userInput.value = '';
                    this.getAIResponse(message);
                }
            }

            addMessage(sender, text) {
                const chatbotMessages = document.getElementById('chatbot-messages');
                const messageElement = document.createElement('div');
                messageElement.innerHTML = `<strong>${sender}:</strong> ${text}`;
                chatbotMessages.appendChild(messageElement);
                chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
            }

            async getAIResponse(message) {
                try {
                    const response = await fetch('get_ai_response.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ message: message })
                    });

                    if (!response.ok) {
                        throw new Error('API request failed');
                    }

                    const data = await response.json();
                    this.addMessage('AI', data.response);
                } catch (error) {
                    console.error('Error:', error);
                    this.addMessage('AI', 'Sorry, I encountered an error.');
                }
            }

            loadMap() {
                require([
                    "esri/config",
                    "esri/Map",
                    "esri/views/SceneView",
                    "esri/widgets/BasemapGallery",
                    "esri/widgets/Expand",
                    "esri/widgets/Home",
                    "esri/widgets/Search",
                    "esri/Graphic",
                    "esri/layers/GraphicsLayer",
                    "esri/PopupTemplate"
                ], (esriConfig, Map, SceneView, BasemapGallery, Expand, Home, Search, 
                    Graphic, GraphicsLayer, PopupTemplate) => {
                    
                    this.initializeArcGIS(esriConfig, Map, SceneView, BasemapGallery, 
                        Expand, Home, Search, Graphic, GraphicsLayer, PopupTemplate);
                });
            }

            setupGraphicsLayer(map, GraphicsLayer, Graphic, PopupTemplate) {
                const graphicsLayer = new GraphicsLayer();
                map.add(graphicsLayer);

                const popupTemplate = this.createPopupTemplate(PopupTemplate);
                
                this.graphicsLayer = graphicsLayer;
                this.timelineInteraction = false;
                
                this.defaultSymbol = {
                    type: "simple-marker",
                    color: "blue",
                    outline: {
                        color: [255, 255, 255],
                        width: 1
                    },
                    size: "12px"
                };

                this.highlightSymbol = {
                    type: "simple-marker",
                    color: "#FFD700",
                    outline: {
                        color: [255, 255, 255],
                        width: 2
                    },
                    size: "16px"
                };

                // Updated click event handler with more reliable popup handling
                this.view.on("click", async (event) => {
                    const response = await this.view.hitTest(event);
                    const graphic = response.results[0]?.graphic;
                    
                    if (graphic?.layer === graphicsLayer) {
                        // Ensure the popup is displayed
                        this.view.popup.open({
                            features: [graphic],
                            location: event.mapPoint
                        });
                        
                        // Then handle the highlighting and timeline updates
                        const pointID = graphic.attributes.pointID;
                        
                        // Small delay to ensure popup renders first
                        setTimeout(() => {
                            this.highlightPoint(pointID);
                            
                            // Find and scroll to the corresponding timeline marker
                            const timelineMarker = document.querySelector(`.timeline-marker[data-point-id="${pointID}"]`);
                            if (timelineMarker) {
                                const timelineTrack = document.getElementById('timelineTrack');
                                const markers = document.querySelectorAll('.timeline-marker');
                                const currentIndex = Array.from(markers).indexOf(timelineMarker);
                                
                                // Update timeline controls
                                this.updateTimelineControlsState(currentIndex, markers.length);
                                
                                // Scroll the timeline to center the marker
                                const markerLeft = timelineMarker.offsetLeft;
                                const trackWidth = timelineTrack.offsetWidth;
                                timelineTrack.scrollLeft = markerLeft - (trackWidth / 2);
                            }
                        }, 50);
                    } else {
                        // Click was not on a point
                        this.view.popup.close();
                        this.clearHighlights();
                        
                        // Remove active class from all timeline markers
                        const markers = document.querySelectorAll('.timeline-marker');
                        markers.forEach(marker => marker.classList.remove('active'));
                    }
                });

                this.fetchWorkspacePoints(graphicsLayer, Graphic, popupTemplate);
            }

            highlightPoint(pointID) {
                // Reset all points to default symbol
                this.clearHighlights();

                // Highlight the selected point
                const targetGraphic = this.graphicsLayer.graphics.find(g => g.attributes.pointID === pointID);
                if (targetGraphic) {
                    targetGraphic.symbol = this.highlightSymbol;

                    // Find and highlight corresponding timeline marker
                    const timelineMarker = document.querySelector(`.timeline-marker[data-point-id="${pointID}"]`);
                    if (timelineMarker) {
                        const markers = document.querySelectorAll('.timeline-marker');
                        markers.forEach(marker => marker.classList.remove('active'));
                        timelineMarker.classList.add('active');

                        // Update timeline position
                        const index = Array.from(markers).indexOf(timelineMarker);
                        if (index !== -1) {
                            const timelineTrack = document.getElementById('timelineTrack');
                            const markerLeft = timelineMarker.offsetLeft;
                            const trackWidth = timelineTrack.offsetWidth;
                            timelineTrack.scrollLeft = markerLeft - (trackWidth / 2);

                            // Update timeline controls state
                            this.updateTimelineControlsState(index, markers.length);
                        }
                    }
                }
            }

            clearHighlights() {
                // Reset all map points to default symbol
                this.graphicsLayer.graphics.forEach(graphic => {
                    graphic.symbol = this.defaultSymbol;
                });
            }

            updateTimelineControlsState(currentIndex, totalPoints) {
                const prevButton = document.getElementById('timelinePrev');
                const nextButton = document.getElementById('timelineNext');
                
                if (prevButton && nextButton) {
                    prevButton.disabled = currentIndex === 0;
                    nextButton.disabled = currentIndex === totalPoints - 1;
                }
            }

            initializeTimelineControls() {
                const prevButton = document.getElementById('timelinePrev');
                const nextButton = document.getElementById('timelineNext');
                const timelineTrack = document.getElementById('timelineTrack');
                let currentIndex = 0;
                let points = [];

                fetch(`get_timeline_points.php?id=${new URLSearchParams(window.location.search).get('id')}`)
                    .then(response => response.json())
                    .then(result => {
                        if (result.status === 'success') {
                            points = result.data;
                            updateTimelineUI();
                            addMarkerClickHandlers();
                        }
                    });

                const navigateToPointPreservingPopup = (point) => {
                    const isPopupOpen = this.view.popup.visible;
                    const isDocked = this.view.popup.dockEnabled;
                    
                    // Find the graphic for the point
                    const targetGraphic = this.graphicsLayer.graphics.find(g => 
                        g.attributes.pointID === point.pointID);

                    this.view.goTo({
                        center: [parseFloat(point.longitude), parseFloat(point.latitude)],
                        zoom: 6
                    }, {
                        duration: 1000,
                        easing: 'ease-in-out'
                    }).then(() => {
                        if (isPopupOpen && targetGraphic) {
                            // Maintain the same docking state
                            this.view.popup.dockEnabled = isDocked;
                            this.view.popup.open({
                                features: [targetGraphic],
                                location: targetGraphic.geometry
                            });
                        }
                    });
                };

                const addMarkerClickHandlers = () => {
                    document.querySelectorAll('.timeline-marker').forEach((marker, index) => {
                        marker.addEventListener('click', () => {
                            this.timelineInteraction = true;
                            const pointId = marker.getAttribute('data-point-id');
                            currentIndex = index;
                            this.highlightPoint(pointId);
                            
                            const point = points[currentIndex];
                            navigateToPointPreservingPopup(point);
                            updateTimelineUI();
                        });
                    });
                };

                prevButton.addEventListener('click', () => {
                    if (currentIndex > 0) {
                        this.timelineInteraction = true;
                        currentIndex--;
                        const point = points[currentIndex];
                        this.highlightPoint(point.pointID);
                        navigateToPointPreservingPopup(point);
                        updateTimelineUI();
                    }
                });

                nextButton.addEventListener('click', () => {
                    if (currentIndex < points.length - 1) {
                        this.timelineInteraction = true;
                        currentIndex++;
                        const point = points[currentIndex];
                        this.highlightPoint(point.pointID);
                        navigateToPointPreservingPopup(point);
                        updateTimelineUI();
                    }
                });

                const updateTimelineUI = () => {
                    this.updateTimelineControlsState(currentIndex, points.length);
                    
                    const markers = document.querySelectorAll('.timeline-marker');
                    if (markers[currentIndex]) {
                        const markerLeft = markers[currentIndex].offsetLeft;
                        const trackWidth = timelineTrack.offsetWidth;
                        timelineTrack.scrollLeft = markerLeft - (trackWidth / 2);
                    }
                };
            }

            initializeArcGIS(esriConfig, Map, SceneView, BasemapGallery, Expand, Home, 
                Search, Graphic, GraphicsLayer, PopupTemplate) {
                
                esriConfig.apiKey = "YOUR_ARCGIS_API_KEY";

                const map = new Map({
                    basemap: "hybrid"
                });

                this.view = new SceneView({
                    container: "viewDiv",
                    map: map,
                    center: [-98, 40],
                    zoom: 4,
                    qualityProfile: "high",
                    environment: {
                        lighting: {
                            directShadowsEnabled: true,
                            date: "sun-always-up",
                            cameraTrackingEnabled: false
                        },
                        atmosphere: { quality: "high" },
                    }
                });

                this.addMapWidgets(this.view, BasemapGallery, Expand, Home, Search);
                this.setupGraphicsLayer(map, GraphicsLayer, Graphic, PopupTemplate);
            }

            addMapWidgets(view, BasemapGallery, Expand, Home, Search) {
                // Create BasemapGallery widget
                const basemapGalleryExpanded = new BasemapGallery({
                    view: view
                });

                // Create expand widget for BasemapGallery
                const bgExpand = new Expand({
                    view: view,
                    content: basemapGalleryExpanded,
                    expandIcon: "basemap",
                    group: "top-left"
                });

                const widgets = [
                    { widget: new Home({ view: view }), position: "top-left" },
                    { widget: bgExpand, position: "top-left" },
                    { widget: new Search({ 
                        view: view,
                        popupEnabled: false,
                        resultGraphicEnabled: false,
                        includeDefaultSources: true,
                        container: "searchWidgetDiv"
                    }), position: "bottom-right" }
                ];

                widgets.forEach(({widget, position}) => {
                    view.ui.add(widget, position);
                });
            }

            createPopupTemplate(PopupTemplate) {
                return new PopupTemplate({
                    title: "Point Information",
                    content: [{
                        type: "custom",
                        creator: (event) => {
                            const div = document.createElement("div");
                            const data = event.graphic.attributes;

                            const contentTypes = {
                                text_content: this.createTextContent,
                                video_url: this.createVideoContent,
                                image_path: this.createImageContent
                            };

                            Object.entries(contentTypes).forEach(([key, creator]) => {
                                if (data[key]) {
                                    div.appendChild(creator.call(this, data[key]));
                                }
                            });

                            return div;
                        }
                    }]
                });
            }

            createTextContent(text) {
                const div = document.createElement("div");
                div.className = "popup-content";
                div.innerHTML = `
                    <h3>Text Content</h3>
                    <p>${text}</p>
                    <button class="fullscreen-button" data-text="${encodeURIComponent(text)}">
                        Full Screen
                    </button>
                `;
                
                // Add click event listener to the button
                const button = div.querySelector('.fullscreen-button');
                button.addEventListener('click', () => {
                    const textContent = decodeURIComponent(button.getAttribute('data-text'));
                    this.showFullscreen('text', textContent);
                });
                
                return div;
            }

            createVideoContent(url) {
                const div = document.createElement("div");
                div.className = "popup-content";
                div.innerHTML = `
                    <h3>Video</h3>
                    <iframe src="${url}" frameborder="0" allowfullscreen></iframe>
                    <button class="fullscreen-button" onclick="app.showFullscreen('video', '${url}')">
                        Full Screen
                    </button>
                `;
                return div;
            }

            createImageContent(path) {
                const div = document.createElement("div");
                div.className = "popup-content";
                div.innerHTML = `
                    <h3>Image</h3>
                    <img src="${path}" alt="Point Image">
                    <button class="fullscreen-button" onclick="app.showFullscreen('image', '${path}')">
                        Full Screen
                    </button>
                `;
                return div;
            }

            async fetchWorkspacePoints(graphicsLayer, Graphic, popupTemplate) {
                try {
                    const response = await fetch('get_workspace_points.php?id=<?php echo $_GET["id"]; ?>');
                    const result = await response.json();

                    if (result.status === 'success') {
                        result.data.forEach(point => {
                            const pointGraphic = new Graphic({
                                geometry: {
                                    type: "point",
                                    longitude: parseFloat(point.longitude),
                                    latitude: parseFloat(point.latitude)
                                },
                                symbol: {
                                    type: "simple-marker",
                                    color: "blue",
                                    outline: {
                                        color: [255, 255, 255],
                                        width: 1
                                    },
                                    size: "12px" 
                                },
                                attributes: point,
                                popupTemplate: popupTemplate
                            });
                            graphicsLayer.add(pointGraphic);
                        });

                        graphicsLayer.when(() => {
                            this.view.goTo(graphicsLayer.graphics.toArray());
                        });
                    } else {
                        console.error("Failed to fetch workspace points:", result.message);
                    }
                } catch (error) {
                    console.error("Error fetching workspace points:", error);
                }
            }

            showFullscreen(type, content) {
                const contentMap = {
                    text: () => `<div class="fullscreen-text">${content}</div>`,
                    video: () => `
                        <div style="display: flex; justify-content: center; align-items: center; height: 100%;">
                            <iframe 
                                src="${content}" 
                                frameborder="0" 
                                allowfullscreen 
                                style="width: 80vh; height: 80vh; border-radius: 8px; box-shadow: 0 4px 20px rgba(0,0,0,0.3);">
                            </iframe>
                        </div>`,
                    image: () => `<img src="${content}" alt="Full Screen Image" style="max-width:100%; max-height:80vh; object-fit:contain;">`
                };

                this.fullscreenOverlay.style.display = 'flex';
                document.getElementById('fullscreenContent').innerHTML = contentMap[type]();
            }
        }

        // Initialize the application
        const app = new GIStoryApp();
        window.app = app;
    </script>
</body>
</html>
