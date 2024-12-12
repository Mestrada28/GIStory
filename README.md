# GIStory

GIStory is an interactive web application that allows users to create and explore historical narratives through geographical mapping. By combining ArcGIS technology with multimedia content management, users can build dynamic timelines that bring historical events to life.

## Project Overview

GIStory enables users to:
- Create interactive historical timelines with geographical points
- Attach images, videos, and text descriptions to specific locations
- Visualize historical events on an interactive 3D map
- Generate AI-assisted insights about geographical regions
- Manage multiple workspaces for different historical narratives

## Technologies Used

- Frontend: HTML, CSS, JavaScript
- Backend: PHP
- Database: MariaDB
- Mapping: ArcGIS JavaScript API
- AI Integration: Google Gemini API
- Additional Libraries: Bootstrap 5.3.0

## Prerequisites

Before setting up GIStory, ensure you have:
- PHP 7.4 or higher
- MariaDB/MySQL
- Web server (Apache recommended)
- Composer (PHP package manager)

## Installation

1. Clone the repository:
```bash
git clone https://github.com/Mestrada28/gistory.git
cd gistory
```

2. Set up the database:
- Create a new MariaDB database
- Import the database structure from the provided SQL files
- Update database credentials in config.php

3. Configure API keys:
- Obtain an ArcGIS API key from [ArcGIS Developer Portal](https://developers.arcgis.com/)
- Obtain a Google Gemini API key
- Update the respective API keys in the configuration files

4. Update configuration:
Replace placeholder values in config.php with your actual database credentials:
```php
$conn = new PDO(
    "mysql:host=YOUR_HOST;dbname=YOUR_DATABASE",
    "YOUR_USERNAME",
    "YOUR_PASSWORD",
    $options
);
```

5. Set up file permissions:
- Ensure the images directory is writable by the web server
- Configure appropriate permissions for log files

## Project Structure

- /css - Stylesheet files
- /images - Uploaded media storage
- /Gistory - Main application files
- /vendor - Dependencies and third-party libraries

## Key Features

1. User Management
- Secure authentication system
- Personal workspaces
- Account management

2. Workspace Management
- Create multiple workspaces
- Organize historical narratives
- Share workspace content

3. Interactive Mapping
- 3D terrain visualization
- Custom point markers
- Timeline integration

4. Media Management
- Image upload and display
- YouTube video integration
- Text content management

5. Timeline Features
- Chronological event organization
- Interactive navigation

6. AI Integration
- Regional information generation
- Historical context assistance

## Security Considerations

- Implement proper input validation
- Use prepared statements for database queries
- Secure file upload handling
- Session management

## Contributing

1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request

## Known Issues

- Timeline scrolling may be inconsistent in some browsers
- Video embeds require stable internet connection
- Large image uploads may require additional configuration

## Future Enhancements

- Mobile responsive design improvements
- Additional basemap options
- Enhanced timeline filtering
- Collaborative workspace features
- Advanced media management tools

## Acknowledgments

- CSUB Computer Science Department

## Disclaimer

This project was developed as part of a senior project at California State University, Bakersfield. API keys and database credentials have been removed from the public repository for security purposes. Users must provide their own credentials to run the application.
