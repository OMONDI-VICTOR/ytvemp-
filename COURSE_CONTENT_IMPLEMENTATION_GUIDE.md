# Course Content Implementation Guide

## Overview
This guide explains how to add course contents (lessons, modules, materials) to your YouthSkills platform.

## Step 1: Database Schema Updates

Add these tables to your `youth_skills_db` database:

```sql
-- Create course_modules table
CREATE TABLE course_modules (
    id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT NOT NULL,
    module_title VARCHAR(255) NOT NULL,
    module_order INT NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

-- Create course_lessons table
CREATE TABLE course_lessons (
    id INT PRIMARY KEY AUTO_INCREMENT,
    module_id INT NOT NULL,
    lesson_title VARCHAR(255) NOT NULL,
    lesson_order INT NOT NULL,
    content LONGTEXT,
    video_url VARCHAR(500),
    duration_minutes INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (module_id) REFERENCES course_modules(id) ON DELETE CASCADE
);

-- Create lesson_resources table (for downloadable materials)
CREATE TABLE lesson_resources (
    id INT PRIMARY KEY AUTO_INCREMENT,
    lesson_id INT NOT NULL,
    resource_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500),
    resource_type ENUM('pdf', 'doc', 'video', 'image', 'other'),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (lesson_id) REFERENCES course_lessons(id) ON DELETE CASCADE
);

-- Create user_lesson_progress table (track learner progress)
CREATE TABLE user_lesson_progress (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    lesson_id INT NOT NULL,
    completed BOOLEAN DEFAULT FALSE,
    completed_at TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (lesson_id) REFERENCES course_lessons(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_lesson (user_id, lesson_id)
);
```

## Step 2: Admin Panel Files to Create

### 1. `/admin/manage_course_content.php`
- Add modules to courses
- Add lessons to modules
- Manage lesson content and video URLs
- Add/delete resources

### 2. `/admin/add_course_module.php`
- Form to add new modules to a course
- Edit existing modules

### 3. `/admin/add_course_lesson.php`
- Form to add lessons to modules
- Edit lessons with rich text editor
- Upload video content

## Step 3: Learner-Facing Files to Create

### 1. `/course_content.php`
- Main course page showing modules and lessons
- Displays current module structure
- Links to individual lessons

### 2. `/view_lesson.php`
- Display lesson content
- Show video if available
- Download resources
- Mark lesson as complete

### 3. `/my_courses.php` (Optional)
- Show enrolled/in-progress courses
- Quick access to course content

## Step 4: Feature Implementation Steps

### Step 4.1: Admin - Manage Course Content
1. Create form to add modules to a course
2. Create form to add lessons to modules
3. Add rich text editor for lesson content (use TinyMCE or CKEditor)
4. Add video URL input
5. Add file upload for resources

### Step 4.2: Admin - View Course Structure
Display existing modules and lessons in a tree structure
Allow edit/delete for each item
Reorder modules and lessons

### Step 4.3: Learner - View Course Content
1. Display course with all modules
2. Show lessons within each module
3. Display lesson completion status
4. Show progress percentage

### Step 4.4: Learner - View Lesson
1. Display lesson content (with rich formatting)
2. Embed video player if URL exists
3. Show downloadable resources
4. "Mark as Complete" button
5. Navigation to next/previous lesson

## Step 5: Key Features to Implement

- **Progress Tracking**: Update `user_lesson_progress` when lesson marked complete
- **Course Completion**: Calculate course completion % based on lessons completed
- **Resources**: Allow admins to upload PDFs, documents, images
- **Video Integration**: Support YouTube, Vimeo, or local videos
- **Rich Text**: Use rich text editor for lesson content formatting
- **Module Ordering**: Drag-and-drop or manual ordering of modules/lessons

## Step 6: Integration with Existing Code

The `dashboard.php` will be updated to:
1. Show course cards (existing - keep as is)
2. Add link to "View Content" button that goes to `course_content.php?course_id=X`
3. Show completion percentage on course card

The `quiz.php` will remain for assessments after content completion.

## Database Relationships
```
Skills (1) ---> (many) Courses
Courses (1) ---> (many) Modules
Modules (1) ---> (many) Lessons
Lessons (1) ---> (many) Resources
Users (many) ---> (many) Lessons (via user_lesson_progress)
```

## Security Considerations
- Validate all file uploads
- Check user enrollment before showing course content
- Use prepared statements for all database queries
- Sanitize HTML content with htmlspecialchars()
- Validate file types before download
