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

<?php
// Starts the session and includes the configuration file
session_start();
require_once("config.php");

// Checks if the workspace ID is provided in the URL query parameter
if (!isset($_GET['id'])) {
    die('WorkspaceID not specified.');
}

// Gets a PDO database connection 
$conn = get_pdo_connection();

// Sanitizes and retrieves the workspace ID from the URL query parameter
$workspaceID = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

// check if the workspace belongs to user logged in 
$stmt = $conn->prepare("SELECT WorkspaceID from Workspaces WHERE WorkspaceID = :workspaceID AND UserID = :userID");
$stmt->bindParam(':workspaceID', $workspaceID, PDO::PARAM_INT);
$stmt->bindParam(':userID', $_SESSION['userID'], PDO::PARAM_INT);
$stmt->execute();

// Get the result to see if the workspace exists for this user
if ($stmt->rowCount() == 0) {
    die('Access denied. You do not have permissions to view this workspace.');
}

// Prepares and executes a SQL statement to fetch points for the given workspace ID
$stmt = $conn->prepare("SELECT pointID, WorkspaceID, latitude, longitude FROM Points WHERE WorkspaceID = :workspaceID");
$stmt->bindParam(':workspaceID', $workspaceID, PDO::PARAM_INT);
$stmt->execute();

$points = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>ArcGIS Developers Guide: Display map (2D)</title>
    <link rel="stylesheet" href="./css/workspace.css">
    <link rel="stylesheet" href="https://js.arcgis.com/4.19/esri/themes/light/main.css">
    <style>
        .toolbar {
            display: flex;
            flex-direction: column;
            gap: 10px;
            padding: 10px;
        }

        .date-container {
            margin-bottom: 15px;
            width: 100%;
        }

        .buttons-container {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .date-picker {
            background-color: rgba(50, 52, 55, 0.7);
            border: 1px solid #e2b714;
            color: #d1d0c5;
            padding: 5px 10px;
            border-radius: 4px;
            width: 100%;
            box-sizing: border-box;
        }

        .date-picker:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .date-picker::-webkit-calendar-picker-indicator {
            filter: invert(0.8);
            cursor: pointer;
        }
    </style>
</head>
<body>
     <!-- Menu bar --> 
     <div id="menuBar">
    <a href="dashboard.php" id="logo"><img src="LogoGistory.png" alt="Logo" class="menu-logo"></a>
    <div class="menu-title">GIStory</div>
        <button id="menuButton" class="menu-button">☰</button>
        <ul id="menuOptions" class="menu-options">
            <li><a href="dashboard.php">Workspace</a></li>
            <li><a href="logOut.php">Logout</a></li>
        </ul>
    </div>
    <!-- Map container -->
    <div id="viewDiv">
        <!-- Search Widget Container -->
        <div id="searchWidgetDiv"></div>

        <!-- Chatbot UI -->
        <div id="chatbot-icon">💬</div>
        <div id="chatbot-container">
            <div id="chatbot-messages"></div>
            <div id="chatbot-input">
                <input type="text" id="user-input" placeholder="Ask about this region...">
                <button id="send-button">Send</button>
            </div>
        </div>
    </div>

    <!-- Toolbar for drawing tools -->
    <div class="toolbar">
        <div class="buttons-container">
            <button id="pointBtn">Point</button>
            <button id="submitBtn">Submit</button>
            <button id="deleteBtn">Delete Point</button>
        </div>
    </div>
    <!-- Date box -->
    <div class="content-box date-box">
        <h3>Date</h3>
        <div class="date-input-container">
            <input type="date" id="editPointDate" class="date-picker" disabled>
        </div>
        <div class="button-container">
            <button id="editDateBtn" disabled>Edit Date</button>
            <button id="saveDateBtn" disabled>Save Date</button>
        </div>
    </div>
    <script>
    // Toggle menu visibility
    document.getElementById("menuButton").onclick = function() {
        var menu = document.getElementById("menuOptions");
        if (menu.style.display === "block") {
            menu.style.display = "none";
        } else {
            menu.style.display = "block";
        }
    };
</script>
    <!-- Content container for text, video, and image -->
    <div class="content-container">
        <!-- Text content box -->
        <div class="content-box text-box">
            <h3> Text Box</h3>
            <p>Add and manage text content for the selected point.</p>
            <textarea id="contentTextarea" disabled></textarea>
            <div class="button-container">
                <button id="addTextBtn" disabled>Add Text</button>
                <button id="saveTextBtn" disabled>Save Text</button>
                <button id="deleteTextBtn" disabled>Delete Text</button>
            </div>
        </div>
        <!-- Video content box -->
        <div class="content-box video-box">
            <h3>Video Box</h3>
            <p>Add and manage video content for the selected point.</p>
            <div id="videoContainer"></div>
            <div class="button-container">
                <button id="addVideoBtn" disabled>Add Video</button>
                <button id="saveVideoBtn" disabled>Save Video</button>
                <button id="deleteVideoBtn" disabled>Delete Video</button>
            </div>
            <input type="text" id="videoUrlInput" placeholder="Enter video URL" style="display: none;">
        </div>
        <!-- Image content box -->
        <div class="content-box image-box">
            <h3>Image Box</h3>
            <p>Add and manage image content for the selected point.</p>
            <div id="imageContainer"></div>
            <div class="button-container">
                <button id="addImageBtn" disabled>Add Image</button>
                <button id="saveImageBtn" disabled>Save Image</button>
                <button id="deleteImageBtn" disabled>Delete Image</button>
            </div>
            <input type="file" id="imageFileInput" accept="image/*" style="display: none;">
        </div>
    </div>
    <!-- Includes the ArcGIS JavaScript API -->
    <script src="https://js.arcgis.com/4.19/"></script>

    <script>
        // Passes the points data from PHP to JavaScript
        var pointsFromPHP = <?php echo json_encode($points); ?>;
    </script>

    <script>
        // Setup for drawing and media interaction.
        let currentDrawingMode = '';
        let isDrawingPoint = false;
        let coordinates = [];
        let tempPointGraphic = null;
        let selectedPointGraphic = null;
        let selectedPointID = null;
        let isAddingText = false;
        let selectedVideoUrl = null;
        let selectedImageFile = null;

        require([
            "esri/config",
            "esri/Map",
            "esri/views/MapView",
            "esri/widgets/Search",
            "esri/Graphic",
            "esri/layers/GraphicsLayer",
            "esri/geometry/Polyline",
            "esri/geometry/Polygon"
        ], function (esriConfig, Map, MapView, Search, Graphic, GraphicsLayer, Polyline, Polygon) {

            // Sets the ArcGIS API key
            esriConfig.apiKey = "YOUR_ARCGIS_API_KEY";
            
            // Creates a new map instance
            const map = new Map({
                // This is the base map layer
                basemap: "arcgis-topographic" 
            });

            // Creates a new map view instance
            const view = new MapView({
                map: map,
                center: [-98.5795, 39.8283],
                zoom: 5,
                container: "viewDiv",
                constraints: {
                    snapToZoom: false
                }
            });

            // Initialize the search widget
            const searchWidget = new Search({
                view: view,
                popupEnabled: false,
                resultGraphicEnabled: false,
                includeDefaultSources: true,
                container: "searchWidgetDiv"
            });

            // Add the chatbot functionality
            const chatbotIcon = document.getElementById('chatbot-icon');
            const chatbotContainer = document.getElementById('chatbot-container');
            const userInput = document.getElementById('user-input');
            const sendButton = document.getElementById('send-button');

            chatbotIcon.addEventListener('click', () => {
                chatbotContainer.style.display = 
                    chatbotContainer.style.display === 'none' ? 'flex' : 'none';
            });

            sendButton.addEventListener('click', sendMessage);
            userInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') sendMessage();
            });

            function sendMessage() {
                const message = userInput.value.trim();
                if (message) {
                    addMessage('User', message);
                    userInput.value = '';
                    getAIResponse(message);
                }
            }

            // shows save success feedback
            function showSaveSuccess(message = 'Saved successfully!') {
                // Show floating notification
                const notification = document.createElement('div');
                notification.className = 'save-success';
                notification.textContent = message;
                document.body.appendChild(notification);

                // Remove notification after animation
                setTimeout(() => {
                    document.body.removeChild(notification);
                }, 2000);
            }

            // Add button animation function
            function animateButton(buttonId) {
                const button = document.getElementById(buttonId);
                button.classList.add('button-success');
                setTimeout(() => {
                    button.classList.remove('button-success');
                }, 500);
            }


            function addMessage(sender, text) {
                const chatbotMessages = document.getElementById('chatbot-messages');
                const messageElement = document.createElement('div');
                messageElement.innerHTML = `<strong>${sender}:</strong> ${text}`;
                chatbotMessages.appendChild(messageElement);
                chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
            }

            async function getAIResponse(message) {
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
                    addMessage('AI', data.response);
                } catch (error) {
                    console.error('Error:', error);
                    addMessage('AI', 'Sorry, I encountered an error.');
                }
            }

            // Creates a new graphics layer and add it to the map
            const graphicsLayer = new GraphicsLayer();
            map.add(graphicsLayer);

            // Adds existing points from PHP to the map
            pointsFromPHP.forEach(function (point) {
                const pointGraphic = new Graphic({
                    geometry: {
                        type: "point",
                        longitude: point.longitude,
                        latitude: point.latitude
                    },
                    symbol: {
                        type: "simple-marker",
                        color: "orange",
                        outline: {
                            color: [255, 255, 255],
                            width: 2
                        }
                    }
                });
                graphicsLayer.add(pointGraphic);
            });

            // Event listener for map clicks
            view.on("click", function (event) {
                if (currentDrawingMode === 'point') {
                    addPoint(event);
                } else {
                    // Performs hit test on the clicked location
                    view.hitTest(event).then(function (response) {
                        if (response.results.length > 0) {
                            const graphic = response.results[0].graphic;
                            if (graphic.geometry.type === 'point') {
                                // Handles point selection
                                if (selectedPointGraphic) {
                                    selectedPointGraphic.symbol = {
                                        type: "simple-marker",
                                        color: "orange",
                                        outline: {
                                            color: "white",
                                            width: 1
                                        }
                                    };
                                }
                                selectedPointGraphic = graphic;
                                selectedPointGraphic.symbol = {
                                    type: "simple-marker",
                                    color: "red",
                                    outline: {
                                        color: "white",
                                        width: 1
                                    }
                                };

                                const pointCoordinates = graphic.geometry;
                                const xhr = new XMLHttpRequest();
                                xhr.open('POST', 'get_point_id.php', true);
                                xhr.setRequestHeader('Content-Type', 'application/json');
                                xhr.onload = function () {
                                    if (xhr.status === 200) {
                                        const pointID = xhr.responseText;
                                        console.log('Selected Point ID:', pointID);
                                        selectedPointID = pointID;
                                        loadTextForPoint(pointID);
                                        loadVideoForPoint(pointID);
                                        loadImageForPoint(pointID);
                                        loadDateForPoint(pointID);

                                        // Enables the buttons when a point is selected
                                        document.getElementById('addTextBtn').disabled = false;
                                        document.getElementById('saveTextBtn').disabled = false;
                                        document.getElementById('deleteTextBtn').disabled = false;
                                        document.getElementById('addVideoBtn').disabled = false;
                                        document.getElementById('saveVideoBtn').disabled = false;
                                        document.getElementById('deleteVideoBtn').disabled = false;
                                        document.getElementById('addImageBtn').disabled = false;
                                        document.getElementById('saveImageBtn').disabled = false;
                                        document.getElementById('deleteImageBtn').disabled = false;
                                        document.getElementById('editDateBtn').disabled = false;
                                        document.getElementById('saveDateBtn').disabled = false;
                                    }
                                };
                                xhr.send(JSON.stringify({ latitude: pointCoordinates.latitude, longitude: pointCoordinates.longitude }));
                            }
                        } else {
                            // Handles deselection of point
                            if (selectedPointGraphic) {
                                selectedPointGraphic.symbol = {
                                    type: "simple-marker",
                                    color: "orange",
                                    outline: {
                                        color: "white",
                                        width: 1
                                    }
                                };
                                selectedPointGraphic = null;
                            }
                            selectedPointID = null;
                            document.getElementById('contentTextarea').value = '';
                            document.getElementById('contentTextarea').disabled = true; 
                            selectedVideoUrl = null;
                            document.getElementById('videoUrlInput').value = '';
                            document.getElementById('videoContainer').innerHTML = '';
                            selectedImageFile = null;
                            document.getElementById('imageContainer').innerHTML = '';

                            // Disables the buttons when no point is selected
                            document.getElementById('addTextBtn').disabled = true;
                            document.getElementById('saveTextBtn').disabled = true;
                            document.getElementById('deleteTextBtn').disabled = true;
                            document.getElementById('addVideoBtn').disabled = true;
                            document.getElementById('saveVideoBtn').disabled = true;
                            document.getElementById('deleteVideoBtn').disabled = true;
                            document.getElementById('addImageBtn').disabled = true;
                            document.getElementById('saveImageBtn').disabled = true;
                            document.getElementById('deleteImageBtn').disabled = true;
                            document.getElementById('editDateBtn').disabled = true;
                            document.getElementById('saveDateBtn').disabled = true;
                            document.getElementById('editPointDate').disabled = true;
                            document.getElementById('editPointDate').value = '';
                        }
                    });
                }
            });

            // This function adds a point to the map
            function addPoint(event) {
                if (isDrawingPoint) {
                    try {
                        // Converts the click event coordinates to map coordinates
                        const point = view.toMap({ x: event.x, y: event.y });

                        // Creates a new point graphic
                        const pointGraphic = new Graphic({
                            geometry: point,
                            symbol: {
                                type: "simple-marker",
                                color: "orange",
                                outline: {
                                    color: "white",
                                    width: 1
                                }
                            }
                        });

                        // Removes the previous temporary point graphic, if it exists
                        if (tempPointGraphic) {
                            graphicsLayer.remove(tempPointGraphic);
                            tempPointGraphic.geometry = null;
                        }

                        // Adds the new point graphic to the graphics layer
                        graphicsLayer.add(pointGraphic);

                        // Stores the new point graphic as the temporary point graphic
                        tempPointGraphic = pointGraphic;
                    } catch (error) {
                        console.error("Failed to add point due to:", error);
                    }
                } else {
                    // Removes the temporary point graphic when not in drawing point mode
                    if (tempPointGraphic) {
                        graphicsLayer.remove(tempPointGraphic);
                        tempPointGraphic.geometry = null;
                        tempPointGraphic = null;
                    }
                }

                // Prevents the event from propagating further
                event.stopPropagation();
            }

            // Update the submit point function
            function submitPoint() {
                const submitButton = document.getElementById('submitBtn');
                if (tempPointGraphic && !submitButton.disabled) {
                    submitButton.disabled = true;

                    const lat = tempPointGraphic.geometry.latitude;
                    const lon = tempPointGraphic.geometry.longitude;
                    const data = {
                        latitude: lat,
                        longitude: lon,
                        workspaceID: <?php echo $workspaceID; ?>
                    };

                    fetch('save_point.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(data),
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log('Success:', data);
                        tempPointGraphic = null;
                        // Reset date picker
                        document.getElementById('pointDate').value = '';
                        submitButton.disabled = false;
                    })
                    .catch((error) => {
                        console.error('Error:', error);
                        submitButton.disabled = false;
                    });
                }
            }

          function loadTextForPoint(pointID) {
              const xhr = new XMLHttpRequest();
              xhr.open('POST', 'get_text.php', true);
              xhr.setRequestHeader('Content-Type', 'application/json');
              xhr.onload = function () {
                  if (xhr.status === 200) { 
                    // Updates the text area with the retrieved text
                      const response = JSON.parse(xhr.responseText);
                      if (response.status === 'success') {
                          document.getElementById('contentTextarea').value = response.text;
                      } else {
                        // Clears the text area if no text is found for the point
                          document.getElementById('contentTextarea').value = '';
                      }
                  }
              };
              // Sends the point ID to retrieve the associated text
              xhr.send(JSON.stringify({ pointID: pointID }));
          }

          function loadVideoForPoint(pointID) {
              const xhr = new XMLHttpRequest();
              xhr.open('POST', 'get_video.php', true);
              xhr.setRequestHeader('Content-Type', 'application/json');
              xhr.onload = function () {
                  if (xhr.status === 200) {
                      const response = JSON.parse(xhr.responseText);
                      if (response.status === 'success') {
                        // Stores the retrieved video URL
                          selectedVideoUrl = response.videoUrl;
                          // Updates the video URL input field with the retrieved video URL
                          document.getElementById('videoUrlInput').value = selectedVideoUrl;
                          // Embeds the video using the retrieved video URL
                          embedVideo(selectedVideoUrl);
                      } else {
                        // Clears the selected video URL and video container if no video is found for the point
                          selectedVideoUrl = null;
                          document.getElementById('videoUrlInput').value = '';
                          document.getElementById('videoContainer').innerHTML = '';
                      }
                  }
              };
              // Sends the point ID to retrieve the associated video URL
              xhr.send(JSON.stringify({ pointID: pointID }));
          }

          function loadImageForPoint(pointID) {
              const xhr = new XMLHttpRequest();
              xhr.open('POST', 'get_image.php', true);
              xhr.setRequestHeader('Content-Type', 'application/json');
              xhr.onload = function () {
                  if (xhr.status === 200) {
                      const response = JSON.parse(xhr.responseText);
                      if (response.status === 'success') {
                          const imagePath = response.imagePath;
                          // Creates an image element with the retrieved image path
                          const imageElement = document.createElement('img');
                          imageElement.src = imagePath;
                          // Clears the image container and appends the new image element
                          document.getElementById('imageContainer').innerHTML = '';
                          document.getElementById('imageContainer').appendChild(imageElement);
                      } else {
                        // Clears the image container if no image is found for the point
                          document.getElementById('imageContainer').innerHTML = '';
                      }
                  }
              };
              // Sends the point ID to retrieve the associated image
              xhr.send(JSON.stringify({ pointID: pointID }));
          }

          function embedVideo(videoUrl) {
              const videoContainer = document.getElementById('videoContainer');
              //Clears previous content
              videoContainer.innerHTML = '';

              if (videoUrl) {
                // Extracts the video ID from the video URL
                  const videoId = extractVideoId(videoUrl);
                  if (videoId) {
                    // Constructs the YouTube embed URL with the video ID and enablejsapi parameter
                    // Adding enablejsapi parameter
                      const embedUrl = `https://www.youtube.com/embed/${videoId}?enablejsapi=1`; 
                      // Creates an iframe element with the embed URL
                      const iframe = document.createElement('iframe');
                      iframe.src = embedUrl;
                      iframe.width = '100%';
                      iframe.height = '315';
                      iframe.frameBorder = '0';
                      iframe.allowFullscreen = true;
                      // Appends the iframe element to the video container
                      videoContainer.appendChild(iframe);
                  } else {
                      console.error('Failed to extract video ID from URL:', videoUrl);
                  }
              } else {
                  console.error('No video URL provided');
              }
          }

          function extractVideoId(url) {
            // Regular expression to extract the video ID from a YouTube URL
              const regex = /(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?|shorts)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/;
              const match = url.match(regex);
              return match ? match[1] : null;
          }

          // ADD THE loadDateForPoint FUNCTION HERE
        function loadDateForPoint(pointID) {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'get_point_date.php', true);
            xhr.setRequestHeader('Content-Type', 'application/json');
            xhr.onload = function () {
                if (xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    if (response.status === 'success' && response.customDate) {
                        document.getElementById('editPointDate').value = response.customDate;
                    } else {
                        document.getElementById('editPointDate').value = '';
                    }
                }
            };
            xhr.send(JSON.stringify({ pointID: pointID }));
        }
    
          // REPLACE with this:
            document.getElementById("pointBtn").onclick = function () {
                isDrawingPoint = !isDrawingPoint;
                this.classList.toggle('active');
                if (isDrawingPoint) {
                    currentDrawingMode = 'point';
                } else {
                    currentDrawingMode = '';
                    if (tempPointGraphic) {
                        graphicsLayer.remove(tempPointGraphic);
                        tempPointGraphic.geometry = null;
                        tempPointGraphic = null;
                    }
                }
            };
                    
          // Event listener for the "Submit" button
          document.getElementById("submitBtn").addEventListener('click', submitPoint);
          
          // Event listener for the "Add Text" button
          document.getElementById('addTextBtn').addEventListener('click', function () {
              if (selectedPointID) {
                  isAddingText = true;
                  document.getElementById('contentTextarea').disabled = false;
                  document.getElementById('contentTextarea').focus();
              }
          });
          
          // Event listener for input changes in the text area
          document.getElementById('contentTextarea').addEventListener('input', function () {
              if (!isAddingText) {
                  document.getElementById('contentTextarea').value = '';
              }
          });
          
          // Save Text
            document.getElementById('saveTextBtn').addEventListener('click', function() {
                if (selectedPointID) {
                    const text = document.getElementById('contentTextarea').value;
                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', 'save_text.php', true);
                    xhr.setRequestHeader('Content-Type', 'application/json');
                    xhr.onload = function() {
                        if (xhr.status === 200) {
                            showSaveSuccess('Text saved!');
                            animateButton('saveTextBtn');
                            isAddingText = false;
                        }
                    };
                    xhr.send(JSON.stringify({ pointID: selectedPointID, text: text }));
                }
            });
          
          // Event listener for the "Delete Text" button
          document.getElementById('deleteTextBtn').addEventListener('click', function () {
              if (selectedPointID) {
                  const xhr = new XMLHttpRequest();
                  xhr.open('POST', 'delete_text.php', true);
                  xhr.setRequestHeader('Content-Type', 'application/json');
                  xhr.onload = function () {
                      if (xhr.status === 200) {
                          console.log('Text deleted successfully');
                          document.getElementById('contentTextarea').value = '';
                      }
                  };
                  xhr.send(JSON.stringify({ pointID: selectedPointID }));
              }
          });

          // Event listener for the "Add Video" button
          document.getElementById('addVideoBtn').addEventListener('click', function () {
              if (selectedPointID) {
                  document.getElementById('videoUrlInput').style.display = 'block';
                  document.getElementById('videoUrlInput').focus();
              }
          });

          // Save Video
            document.getElementById('saveVideoBtn').addEventListener('click', function() {
                if (selectedPointID) {
                    const videoUrl = document.getElementById('videoUrlInput').value;
                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', 'save_video.php', true);
                    xhr.setRequestHeader('Content-Type', 'application/json');
                    xhr.onload = function() {
                        if (xhr.status === 200) {
                            showSaveSuccess('Video saved!');
                            animateButton('saveVideoBtn');
                            selectedVideoUrl = videoUrl;
                            embedVideo(selectedVideoUrl);
                            document.getElementById('videoUrlInput').style.display = 'none';
                        }
                    };
                    xhr.send(JSON.stringify({ pointID: selectedPointID, videoUrl: videoUrl }));
                }
            });

          // Event listener for the "Delete Video" button
          document.getElementById('deleteVideoBtn').addEventListener('click', function () {
              if (selectedPointID) {
                  const xhr = new XMLHttpRequest();
                  xhr.open('POST', 'delete_video.php', true);
                  xhr.setRequestHeader('Content-Type', 'application/json');
                  xhr.onload = function () {
                      if (xhr.status === 200) {
                          console.log('Video deleted successfully');
                          selectedVideoUrl = null;
                          document.getElementById('videoUrlInput').value = '';
                          document.getElementById('videoContainer').innerHTML = '';
                      }
                  };
                  xhr.send(JSON.stringify({ pointID: selectedPointID }));
              }
          });

          // Event listener for the "Add Image" button
          document.getElementById('addImageBtn').addEventListener('click', function () {
              if (selectedPointID) {
                  document.getElementById('imageFileInput').click();
              }
          });

          // Event listener for file input changes
          document.getElementById('imageFileInput').addEventListener('change', function (event) {
              const file = event.target.files[0];
              if (file) {
                  selectedImageFile = file;
                  const reader = new FileReader();
                  reader.onload = function (e) {
                      const imagePreview = document.createElement('img');
                      imagePreview.src = e.target.result;
                      document.getElementById('imageContainer').innerHTML = '';
                      document.getElementById('imageContainer').appendChild(imagePreview);
                  };
                  reader.readAsDataURL(file);
              }
          });

          // Save Image
            document.getElementById('saveImageBtn').addEventListener('click', function() {
                if (selectedPointID && selectedImageFile) {
                    const formData = new FormData();
                    formData.append('pointID', selectedPointID);
                    formData.append('image', selectedImageFile);

                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', 'save_image.php', true);
                    xhr.onload = function() {
                        if (xhr.status === 200) {
                            showSaveSuccess('Image saved!');
                            animateButton('saveImageBtn');
                            selectedImageFile = null;
                            document.getElementById('imageFileInput').value = '';
                        }
                    };
                    xhr.send(formData);
                }
            });

          // Event listener for the "Delete Image" button
          document.getElementById('deleteImageBtn').addEventListener('click', function () {
              if (selectedPointID) {
                  const xhr = new XMLHttpRequest();
                  xhr.open('POST', 'delete_image.php', true);
                  xhr.setRequestHeader('Content-Type', 'application/json');
                  xhr.onload = function () {
                      if (xhr.status === 200) {
                          console.log('Image deleted successfully');
                          document.getElementById('imageContainer').innerHTML = '';
                      }
                  };
                  xhr.send(JSON.stringify({ pointID: selectedPointID }));
              }
          });

          // ADD THE NEW DATE EVENT LISTENERS HERE
            document.getElementById('editDateBtn').addEventListener('click', function() {
                if (selectedPointID) {
                    document.getElementById('editPointDate').disabled = false;
                    document.getElementById('editPointDate').focus();
                }
            });

            // Save Date
            document.getElementById('saveDateBtn').addEventListener('click', function() {
                if (selectedPointID) {
                    const newDate = document.getElementById('editPointDate').value;
                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', 'update_date.php', true);
                    xhr.setRequestHeader('Content-Type', 'application/json');
                    xhr.onload = function() {
                        if (xhr.status === 200) {
                            showSaveSuccess('Date saved!');
                            animateButton('saveDateBtn');
                            document.getElementById('editPointDate').disabled = true;
                        }
                    };
                    xhr.send(JSON.stringify({ 
                        pointID: selectedPointID, 
                        customDate: newDate 
                    }));
                }
            });

          // Event listener for the "Delete Point" button
            document.getElementById("deleteBtn").addEventListener('click', deleteSelectedPoint);

            function deleteSelectedPoint() {
                if (selectedPointID) {
                    // Delete associated media from Pictures, Texts, and Videos tables
                    deletePointMedia(selectedPointID)
                        .then(() => {
                            // Delete the selected point from the Points table
                            return deletePointFromDatabase(selectedPointID);
                        })
                        .then(() => {
                            // Remove the selected point graphic from the map
                            graphicsLayer.remove(selectedPointGraphic);
                            selectedPointGraphic = null;
                            selectedPointID = null;

                            // Clear the content of the text area, video container, and image container
                            document.getElementById('contentTextarea').value = '';
                            document.getElementById('videoContainer').innerHTML = '';
                            document.getElementById('imageContainer').innerHTML = '';
                        })
                        .catch((error) => {
                            console.error('Error deleting point:', error);
                        });
                }
            }

            function deletePointMedia(pointID) {
                return new Promise((resolve, reject) => {
                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', 'delete_point_media.php', true);
                    xhr.setRequestHeader('Content-Type', 'application/json');
                    xhr.onload = function () {
                        if (xhr.status === 200) {
                            resolve();
                        } else {
                            reject(new Error('Failed to delete point media'));
                        }
                    };
                    xhr.onerror = function () {
                        reject(new Error('Failed to delete point media'));
                    };
                    xhr.send(JSON.stringify({ pointID: pointID }));
                });
            }

            function deletePointFromDatabase(pointID) {
                return new Promise((resolve, reject) => {
                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', 'delete_point.php', true);
                    xhr.setRequestHeader('Content-Type', 'application/json');
                    xhr.onload = function () {
                        if (xhr.status === 200) {
                            resolve();
                        } else {
                            reject(new Error('Failed to delete point from database'));
                        }
                    };
                    xhr.onerror = function () {
                        reject(new Error('Failed to delete point from database'));
                    };
                    xhr.send(JSON.stringify({ pointID: pointID }));
                });
            }
});
</script>
</body>
</html>
