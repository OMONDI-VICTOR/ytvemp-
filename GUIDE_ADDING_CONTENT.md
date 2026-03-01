# Guide: Adding Content for Learners by Interest Area

This guide explains how to populate the system so that learners can see content relevant to their selected interest (e.g., "Web Development", "Data Science").

## Prerequisite: Database Setup
Ensure your database has the necessary tables and default skills.

1. Open your browser and go to: `http://localhost/ytvemp_app/install_skills.php`
   - This script checks if the `skills` table exists and adds the default categories:
     - Web Development
     - Data Science
     - Graphic Design
     - Digital Marketing
   - **Note:** These must match exactly what is in the Registration form.

## Step 1: Log in as Administrator
1. Go to `http://localhost/ytvemp_app/admin/admin_login.php`.
2. Login with your admin credentials.

## Step 2: Create a Course
1. Click on **"Manage Courses"** in the navigation menu.
2. In the "Add New Course" section:
   - **Course Title:** Enter a catchy title (e.g., "Intro to HTML5").
   - **Description:** A short summary.
   - **Skill Category:** Select the relevant category (e.g., "Web Development").
     - *Important:* This selection determines which learners see this course. A learner who selected "Web Development" will ONLY see courses with this category.
3. Click "Add Course".

## Step 3: Add Learning Content
Once the course is created, you need to add modules and lessons.
1. Find your new course in the list below.
2. Click the **"Content"** button next to it.
3. **Add Module:** Create a module (e.g., "Week 1: Basics").
4. **Add Lesson:** Inside that module, click "Add Lesson".
   - **Title:** Lesson name.
   - **Content:** You can add text, images, or embed YouTube videos here.
   - **Video URL:** Optional direct link to a video.

## Step 4: Verification
To test that it works:
1. Open a new browser window (Incognito mode recommended).
2. Go to `http://localhost/ytvemp_app/register.php`.
3. Create a **new learner account**.
   - Select the **same interest** you used for the course (e.g., "Web Development").
4. After logging in, you should see the course on your **Dashboard**.
   - If you select a different interest, the course should NOT appear (unless you add courses for that interest too).

## Troubleshooting
- **No Courses Found:** Ensure the "Skill Category" of the course matches the User's "Interest".
- **Database Error:** If you see errors about missing tables, import the `setup_course_content_db.sql` file into your MySQL database using phpMyAdmin.
