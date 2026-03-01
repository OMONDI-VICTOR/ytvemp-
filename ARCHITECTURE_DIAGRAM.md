# System Architecture & Data Flow Diagram

## User Journey - Admin (Adding Course Content)

```
┌─────────────────────────────────────────────────────────────────┐
│                     ADMIN WORKFLOW                              │
└─────────────────────────────────────────────────────────────────┘

    LOGIN
      ↓
  admin_login.php
      ↓
  [Authenticate]
      ↓
  admin_dashboard.php
      ↓
  "Manage Courses" link
      ↓
  manage_courses.php
      ↓
  [Display list of courses]
      ↓
  Click "Content" button
      ↓
  manage_course_content.php
      ├─────────────────────────────────────┐
      │ Show existing modules & lessons     │
      │ [Add New Module form]               │
      │ [Add New Lesson form in module]     │
      └─────────────────────────────────────┘
      │
      ├─────► [Add Module] ─────► add_course_module.php
      │         ↓
      │         [INSERT into course_modules]
      │         ↓
      │         Back to manage_course_content.php
      │
      └─────► [Add Lesson] ─────► add_course_lesson.php
                ↓
                [Rich Text Editor]
                [Video URL Input]
                [Duration Input]
                ↓
                [INSERT into course_lessons]
                ↓
                Back to manage_course_content.php
```

## User Journey - Learner (Viewing Course Content)

```
┌─────────────────────────────────────────────────────────────────┐
│                     LEARNER WORKFLOW                            │
└─────────────────────────────────────────────────────────────────┘

    LOGIN
      ↓
  login.php
      ↓
  [Authenticate]
      ↓
  dashboard.php
      ├──────────────────────────────────┐
      │ Display course cards             │
      │ "View Content" button            │
      │ "Start Quiz" button              │
      └──────────────────────────────────┘
      │
      └─────► "View Content" ─────► course_content.php
                ↓
                [Query course_modules]
                [Query course_lessons]
                [Query user_lesson_progress]
                ↓
                [Display modules with expand/collapse]
                [Show lesson list with ✅ status]
                [Show course progress %]
                ↓
        Click on a lesson
                ↓
              view_lesson.php
                ├──────────────────────────────────┐
                │ Display lesson content           │
                │ [Embedded video if available]    │
                │ "Mark as Complete" button        │
                │ Next/Previous lesson buttons     │
                │ Progress sidebar                 │
                └──────────────────────────────────┘
                ↓
          "Mark as Complete"
                ↓
        [INSERT/UPDATE user_lesson_progress]
                ↓
        Progress updates
        Lesson gets ✅ badge
        Sidebar shows new %
```

## Database Relationship Diagram

```
┌─────────────┐
│   users     │
├─────────────┤
│ id (PK)     │◄────────┐
│ fullname    │         │
│ email       │         │
│ password    │         │
│ interest    │         │
└─────────────┘         │
                        │
                  ┌──────────────────┐
                  │ user_lesson_     │
                  │ progress         │
                  ├──────────────────┤
                  │ id (PK)          │
                  │ user_id (FK)     │─────┐
                  │ lesson_id (FK)   │──┐  │
                  │ completed        │  │  │
                  │ completed_at     │  │  │
                  └──────────────────┘  │  │
                                         │  │
┌─────────────┐     ┌────────────────┐   │  │
│   skills    │     │ courses        │   │  │
├─────────────┤     ├────────────────┤   │  │
│ id (PK)     │────◄│ skill_id (FK)  │   │  │
│ skill_name  │     │ course_title   │   │  │
└─────────────┘     │ description    │   │  │
                    │ id (PK)        │   │  │
                    └────────────────┘   │  │
                           │             │  │
                           ▼             │  │
                    ┌──────────────────┐ │  │
                    │ course_modules   │ │  │
                    ├──────────────────┤ │  │
                    │ id (PK)          │ │  │
                    │ course_id (FK)   │ │  │
                    │ module_title     │ │  │
                    │ module_order     │ │  │
                    └──────────────────┘ │  │
                           │             │  │
                           ▼             │  │
                    ┌──────────────────┐ │  │
                    │ course_lessons   │ │  │
                    ├──────────────────┤ │  │
                    │ id (PK)          │─┘  │
                    │ module_id (FK)   │    │
                    │ lesson_title     │────┘
                    │ content          │
                    │ video_url        │
                    │ duration_minutes │
                    │ lesson_order     │
                    └──────────────────┘
                           │
                           ▼
                    ┌──────────────────┐
                    │ lesson_resources │
                    ├──────────────────┤
                    │ id (PK)          │
                    │ lesson_id (FK)   │
                    │ resource_name    │
                    │ file_path        │
                    │ resource_type    │
                    └──────────────────┘

Legend:
  (PK) = Primary Key
  (FK) = Foreign Key
  ◄──── = One-to-Many relationship
```

## Application Architecture

```
┌────────────────────────────────────────────────────────────────┐
│                    YouthSkills Platform                         │
├────────────────────────────────────────────────────────────────┤
│                                                                │
│  ┌──────────────────────┐      ┌──────────────────────┐       │
│  │    ADMIN LAYER       │      │    LEARNER LAYER     │       │
│  ├──────────────────────┤      ├──────────────────────┤       │
│  │ admin_login.php      │      │ login.php            │       │
│  │ admin_dashboard.php  │      │ dashboard.php        │       │
│  │ manage_courses.php   │      │ course_content.php   │       │
│  │ manage_course_-      │      │ view_lesson.php      │       │
│  │  content.php         │      │ quiz.php             │       │
│  │ add_course_module.php│      │ certificate.php      │       │
│  │ add_course_lesson.php│      └──────────────────────┘       │
│  │ edit_course_lesson.php                                      │
│  └──────────────────────┘                                      │
│           │                                                    │
│           └─────────────┬────────────────────┬────────────────┘
│                         │                    │
│           ┌─────────────▼─────────────┐      │
│           │      config.php            │      │
│           │  (Database Connection)     │      │
│           └────────────┬────────────────┘      │
│                        │                       │
│         ┌──────────────▼────────────────┐      │
│         │                               │      │
│         │    MySQL Database              │      │
│         │                               │      │
│         │  ┌─────────────────────────┐  │      │
│         │  │ users                   │  │      │
│         │  │ skills                  │  │      │
│         │  │ courses                 │  │      │
│         │  │ course_modules          │  │      │
│         │  │ course_lessons          │  │      │
│         │  │ lesson_resources        │  │      │
│         │  │ user_lesson_progress ◄─┼──┼──────┘
│         │  │ quiz_questions          │  │
│         │  │ results                 │  │
│         │  └─────────────────────────┘  │
│         │                               │
│         └───────────────────────────────┘
│
└────────────────────────────────────────────────────────────────┘
```

## Content Management Workflow

```
ADMIN CREATES COURSE STRUCTURE
    ↓
[Add Course] → course_title, description, skill_id
    ↓
course in 'courses' table
    ↓
┌─────────────────────────────────┐
│ [Add Modules to Course]         │
│ └→ module_title, description   │
│                                 │
│ [Add Lessons to Module]         │
│ ├→ lesson_title                 │
│ ├→ content (HTML)               │
│ ├→ video_url (optional)         │
│ └→ duration_minutes             │
│                                 │
│ [Add Resources to Lesson]       │
│ └→ file_path, resource_type    │
└─────────────────────────────────┘
    ↓
LEARNER CONSUMES CONTENT
    ↓
[Browse Course]
    ↓
[View Modules]
    ↓
[View Lessons]
    ↓
[Read Content + Watch Video]
    ↓
[Mark Complete]
    ↓
Progress tracked in user_lesson_progress
    ↓
[Take Quiz]
    ↓
Results recorded
    ↓
[Get Certificate]
```

## HTTP Request Flow

```
ADMIN REQUEST:
  GET /admin/manage_courses.php
    ↓
  Database: SELECT courses...
    ↓
  Display list with "Content" button
    ↓
  GET /admin/manage_course_content.php?course_id=1
    ↓
  Database: SELECT modules, lessons
    ↓
  POST /admin/add_course_lesson.php
    → Form data: lesson_title, content, video_url, duration
    ↓
  Database: INSERT into course_lessons
    ↓
  Redirect back to manage_course_content.php

LEARNER REQUEST:
  GET /course_content.php?course_id=1
    ↓
  Database: SELECT modules, lessons, progress
    ↓
  Display module structure with progress
    ↓
  GET /view_lesson.php?lesson_id=5&course_id=1
    ↓
  Database: SELECT lesson details, progress
    ↓
  Display lesson + video + navigation
    ↓
  POST /view_lesson.php (mark complete)
    → Form data: mark_complete=1
    ↓
  Database: INSERT/UPDATE user_lesson_progress
    ↓
  Success message + updated progress
```

## File Dependencies

```
ADMIN FILES:
  admin/manage_course_content.php
    ├─→ config.php (database)
    └─→ uses: course_modules, course_lessons tables

  admin/add_course_module.php
    ├─→ config.php (database)
    └─→ uses: course_modules table

  admin/add_course_lesson.php
    ├─→ config.php (database)
    ├─→ TinyMCE CDN (rich text editor)
    └─→ uses: course_lessons table

LEARNER FILES:
  course_content.php
    ├─→ config.php (database)
    ├─→ assets/css/style.css (styling)
    └─→ uses: course_modules, course_lessons, user_lesson_progress tables

  view_lesson.php
    ├─→ config.php (database)
    ├─→ assets/css/style.css (styling)
    └─→ uses: course_lessons, user_lesson_progress, course_modules tables

MODIFIED FILES:
  dashboard.php - Added "View Content" button
  admin/manage_courses.php - Added "Content" button

DATABASE SETUP:
  setup_course_content_db.sql - Creates all new tables
```

## Progress Calculation Example

```
Course has 5 lessons total
User completed 3 lessons

Calculation:
  completed_lessons = 3
  total_lessons = 5
  progress_percent = (3 / 5) * 100 = 60%

Display:
  "3/5 lessons completed"
  Progress bar: 60% filled
  Can show: "60% Complete"
```

## Session & Security Flow

```
┌─────────────────────────────────────────┐
│ User Request                            │
└──────────────────┬──────────────────────┘
                   ↓
        ┌─────────────────────┐
        │ Check $_SESSION     │
        └─────────┬───────────┘
                  ↓
         ┌────────────────────┐
         │ loggedin = true?   │
         │ role = admin/      │
         │ learner?           │
         └────┬──────────────┘
              ↓
     ┌────────────────┐
     │ Authenticated? │
     ├────────┬───────┤
     │  YES   │  NO   │
     │        │       │
     │        │    Redirect to
     │        │    login.php
     │        │
     │   Check │
     │   Access│
     │   Rights│
     ↓
  Allow/
  Deny
```

This architecture ensures clean separation of concerns, scalability, and security!
