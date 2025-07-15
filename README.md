# Social Media Web Application

A mini social media platform with user registration, profiles, posts, likes, comments, and follow functionality.

## Features

- USER AUTHENTICATION
  - Registration with username, email, and password
  - Secure login/logout functionality
  - Password hashing for security

- USER PROFILES
  - Customizable profile information
  - Profile picture uploads
  - Follow/unfollow other users
  - View followers/following counts

- POSTS
  - Create text or image posts
  - View posts from followed users
  - Like/unlike posts
  - Comment on posts

- RESPONSIVE DESIGN
  - Works on desktop, tablet, and mobile devices
  - Clean, modern interface

## Technologies Used

Frontend:
- HTML5
- CSS3
- JavaScript (ES6)
- Font Awesome icons

Backend:
- PHP
- MySQL database
- XAMPP server environment

## Installation

### Prerequisites
- XAMPP (or similar local server environment)
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web browser

### Setup Instructions

1. Clone the repository:
   git clone https://github.com/Abdul-Moeid-Rao/social-media-app.git
   cd social-media-app

2. Database Setup:
   - Start XAMPP server
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Create a new database named: social_media
   - Import SQL schema from: db.sql

3. Configuration:
   Open config.php and update database credentials if needed:
   $host = 'localhost';
   $dbname = 'social_media';
   $username = 'root';
   $password = '';

4. File Permissions:
   Ensure the 'uploads' directory is writable:
   chmod -R 755 uploads

5. Run the Application:
   - Move project folder to XAMPP's htdocs directory
   - Access it at: http://localhost/social-media-app

## File Structure

social-media-app/
├── css/
│   └── style.css              (Main stylesheet)
├── js/
│   └── main.js                (Main JavaScript file)
├── uploads/                   (User uploads - images, profile pictures)
├── config.php                 (Database config)
├── create_post.php            (Handles post creation)
├── comment_post.php           (Handles comment creation)
├── follow_user.php            (Handles follow/unfollow)
├── get_comments.php           (API to fetch comments)
├── index.php                  (Main feed)
├── like_post.php              (Handles likes)
├── login.php                  (Login page)
├── logout.php                 (Logout)
├── profile.php                (User profile page)
├── register.php               (Registration page)
├── edit_profile.php           (Edit profile info)
└── README.txt                 (This file)

## Usage

1. Register a new account or login with existing credentials
2. Create posts with text or images
3. Like and comment on posts
4. Follow users to view their posts in your feed
5. Edit your profile with updated info and photo


## Contributing

Contributions are welcome. Fork this repository and create a pull request.

## Support

For questions or issues, open an issue on GitHub.
