s# Course Content Feature - Quick Start Guide

## What Has Been Added

Your YouthSkills application now has a complete course content management system with the following features:

### New Files Created

#### Admin Files:
1. **`admin/manage_course_content.php`** - Main interface to manage modules and lessons for a course
2. **`admin/add_course_module.php`** - Add modules to courses
3. **`admin/add_course_lesson.php`** - Add lessons to modules with rich text editor support

#### Learner Files:
1. **`course_content.php`** - View all modules and lessons in a course with progress tracking
2. **`view_lesson.php`** - Display individual lesson with video support and completion tracking

### Database Updates Required

Run the SQL queries from `setup_course_content_db.sql` in your MySQL database to create the necessary tables:

- `course_modules` - Stores course modules
- `course_lessons` - Stores lessons within modules
- `lesson_resources` - Stores downloadable resources (for future use)
- `user_lesson_progress` - Tracks which lessons learners have completed

## Step-by-Step Implementation

### Step 1: Database Setup (Required)
1. Open `setup_course_content_db.sql`
2. Copy all SQL queries
3. Paste into your MySQL client (phpMyAdmin, MySQL Workbench, or CLI)
4. Execute the queries

### Step 2: Admin - Adding Course Content
1. Log in to Admin Panel
2. Go to **Manage Courses**
3. Click **Content** button next to a course
4. Click **Add New Module**
5. Enter module title and description
6. Click **Add Module**
7. Click **Add Lesson** in the module
8. Fill in lesson details:
   - **Lesson Title**: Name of the lesson
   - **Lesson Content**: Use the rich text editor to format your content
   - **Video URL**: (Optional) Paste YouTube embed URL
   - **Duration**: How many minutes the lesson takes
9. Click **Add Lesson**
10. Repeat for all lessons and modules

### Step 3: Learner - Viewing Course Content
1. Log in to Learner Dashboard
2. Find a course card
3. Click **View Content** (new button)
4. See all modules and lessons organized
5. Click on any lesson to view it
6. Watch video (if available)
7. Read lesson content
8. Click **Mark Lesson as Complete**
9. Progress bar updates automatically

## Features

### Admin Features:
- ✅ Add/Delete Modules
- ✅ Add/Delete Lessons with rich text formatting
- ✅ Add video URLs (YouTube, Vimeo, or direct links)
- ✅ Set lesson duration
- ✅ View course structure in tree format
- ✅ See completion statistics

### Learner Features:
- ✅ View organized course structure
- ✅ Watch embedded videos
- ✅ Read formatted lesson content
- ✅ Mark lessons as complete
- ✅ Track progress percentage
- ✅ Navigate between lessons
- ✅ See completion status

## Video URL Examples

### YouTube Videos
- Embed URL: `https://www.youtube.com/embed/VIDEO_ID`
- Share URL: `https://youtu.be/VIDEO_ID`

### Vimeo Videos
- Embed URL: `https://player.vimeo.com/video/VIDEO_ID`

### Direct Video Files
- MP4: `https://yoursite.com/videos/lesson.mp4`

## Rich Text Editor

The lesson content editor uses TinyMCE, which supports:
- **Bold** and *Italic* text
- Ordered and unordered lists
- Links
- Headings

## Navigation Flow

```
Dashboard (learner view)
    ↓
[View Content button] → course_content.php
    ↓
Select Lesson → view_lesson.php
    ↓
Read & Mark Complete → Progress Updates
    ↓
[Take Quiz] → quiz.php
```

## Admin Management Flow

```
Admin Dashboard
    ↓
Manage Courses
    ↓
[Content button] → manage_course_content.php
    ↓
[Add New Module] or [Add Lesson]
    ↓
add_course_module.php or add_course_lesson.php
    ↓
Data Saved & Returned to manage_course_content.php
```

## Future Enhancements

The system is designed to easily support:
- Downloadable resources (PDFs, documents, images)
- Assignments and homework
- Discussion forums per lesson
- Quizzes at module level
- Certificates with completion date
- Bulk upload of lessons
- Lesson reordering (drag & drop)

## Troubleshooting

### "Table doesn't exist" Error
- Run the SQL queries from `setup_course_content_db.sql`

### Video not playing
- Verify the video URL is an embed URL (contains `/embed/`)
- YouTube: Use `https://www.youtube.com/embed/VIDEO_ID`
- Not: `https://youtu.be/VIDEO_ID`

### Progress not updating
- Clear browser cache
- Check if `user_lesson_progress` table exists
- Verify user is logged in as learner

### Rich text editor not showing
- TinyMCE loads from CDN - ensure internet connection
- Check browser console for errors

## Security Notes

- All user inputs are validated
- SQL injection protected with prepared statements
- XSS protection with htmlspecialchars()
- Only authenticated learners can view course content
- Only admins can manage content
- File uploads are validated (when implemented)

## Database Relationships

```
Users (1) → (many) user_lesson_progress
Skills (1) → (many) Courses
Courses (1) → (many) Modules
Modules (1) → (many) Lessons
Lessons (1) → (many) Resources
Lessons (1) → (many) user_lesson_progress
```

## CSS Classes Used

The files use your existing CSS from `assets/css/style.css`. If you need to customize styling further, add custom CSS in `<style>` tags within each file.

## Support

If you encounter issues:
1. Check that all tables exist: `course_modules`, `course_lessons`, `user_lesson_progress`
2. Verify database connection in `config.php`
3. Check browser console for JavaScript errors
4. Verify user roles are set correctly (learner vs admin)

Happy teaching! 🎓
