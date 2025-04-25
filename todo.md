# Craigslist Clone Project Plan

## Project Requirements
- Create a Craigslist clone using HTML, CSS, JavaScript, PHP, and MySQL
- Use XAMPP with default logins
- Beautiful, responsive design

## Features
- User authentication (register, login)
- Post viewing functionality
- Post creation with title, description, images, and contact information
- Administrator dashboard with CRUD operations on all entities

## Database Structure
- Users table (id, username, email, password, is_admin, etc.)
- Posts table (id, user_id, title, description, contact_info, date_created, etc.)
- Images table (id, post_id, image_path, etc.)
- Categories table (id, name, description, etc.)
- Post_categories table (post_id, category_id)

## Project Structure
- index.php (Homepage)
- auth/ (Authentication pages)
  - login.php
  - register.php
  - logout.php
- posts/ (Post-related pages)
  - view.php
  - create.php
  - edit.php
- admin/ (Admin dashboard)
  - index.php
  - users.php
  - posts.php
  - categories.php
- assets/
  - css/
  - js/
  - images/
- includes/ (Reusable components)
  - header.php
  - footer.php
  - db.php
  - functions.php

## Tasks
1. [ ] Set up project structure
2. [ ] Design database schema
3. [ ] Create database and tables
4. [ ] Implement user authentication
5. [ ] Create homepage with posts listing
6. [ ] Implement post viewing functionality
7. [ ] Create post creation form
8. [ ] Implement image upload functionality
9. [ ] Create admin dashboard
10. [ ] Implement CRUD operations for administrators
11. [ ] Add responsive design and styling
12. [ ] Test all features
13. [ ] Optimize performance

## Timeline
- Database setup and user authentication: Day 1
- Post viewing and creation: Day 2
- Admin dashboard and CRUD operations: Day 3
- UI/UX improvements and testing: Day 4

## Tech Stack
- Frontend: HTML5, CSS3, JavaScript, Bootstrap 5
- Backend: PHP 8+
- Database: MySQL
- Server: XAMPP (Apache)
