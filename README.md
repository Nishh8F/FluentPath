# FluentPath 🌍🚀

FluentPath is a modern, beautifully designed, and highly gamified language learning web application. It aims to provide an engaging, premium experience for users learning new languages, complete with a dark-mode-first aesthetic, dynamic micro-animations, and interactive elements.

![FluentPath App](https://img.shields.io/badge/Status-Active-success) ![License](https://img.shields.io/badge/License-MIT-blue)

## ✨ Features

- **Multi-Language Support**: Dive into diverse language courses, including newly added support for **Russian** and **Northern Malay (Utara)**, utilizing seamless inline SVGs instead of external images for robustness.
- **Premium Gamified Experience**:
  - **Hero Avatar & Progression**: Users earn XP, level up, and see their progress reflected with dynamic level-status borders.
  - **Stats & Daily Goals**: Two-column stats grid tracking lessons completed and XP earned, paired with a daily goal tracker to keep learners motivated.
  - **Awards & Milestones**: A horizontally scrolling awards section and a vertical list of recent milestones to celebrate learning achievements.
- **Modern User Interface**: 
  - Glassmorphism effects, smooth gradients, and curated color palettes.
  - Fully responsive, mobile-simulated learning environment acting as a PWA-like Single Page Application (SPA).
  - High-fidelity dark mode designed for a sleek and immersive experience.
- **Interactive Capabilities**: Built-in support for motion sensors (specifically iOS) to unlock rewards via interactive, physical actions.
- **Robust Backend integration**: Uses a fast PHP and MySQL stack to securely handle authentication, track user progress across different languages, and persist state.

## 🛠️ Technology Stack

- **Frontend**: HTML5, CSS3 (Vanilla, custom Design System), JavaScript (React-like component structuring within a monolithic file), FontAwesome Icons.
- **Backend**: PHP (RESTful APIs for user data and authentication)
- **Database**: MySQL (using PDO)

## 🚀 Getting Started

### Prerequisites

- A local development server like **XAMPP**, WAMP, or MAMP.
- PHP 7.4 or higher.
- MySQL Database.

### Installation

1. **Clone the repository**:
   Clone this project directly into your local server's document root (e.g., `htdocs` for XAMPP):
   ```bash
   cd c:/xampp/htdocs
   git clone https://github.com/Nishh8F/FluentPath.git
   ```

2. **Database Setup**:
   - Open your MySQL client (e.g., phpMyAdmin) and create a new database named `fluentpath`.
   - Update the database connection credentials if needed (by default it uses `root` with no password and `port=3307`). Check `setup_progress_db.php`, `api.php`, and `auth.php` to adjust your host and port.
   - Run the initial database setup script to create the necessary tables. You can do this by navigating to:
     ```text
     http://localhost/FluentPath/setup_progress_db.php
     ```
     *(This script creates the `user_progress` table and handles relationships).*

3. **Launch the App**:
   Navigate to the root directory in your browser to launch the app:
   ```text
   http://localhost/FluentPath/
   ```

## 📂 Project Structure

- `index.html`: The core Single Page Application containing all UI structures, styles, and frontend logic.
- `api.php`: The backend endpoint to fetch and update user progress and session state.
- `auth.php`: Handles user registration, login authentication, and secure session management.
- `setup_progress_db.php`: Script to initialize the MySQL database schema.
- `/uploads/`: Directory intended for handling user-uploaded assets (like avatars).

## 🤝 Contributing

Contributions, issues, and feature requests are welcome! Feel free to check the [issues page](https://github.com/Nishh8F/FluentPath/issues).

## 📝 License

This project is licensed under the MIT License. See the `LICENSE` file for details.