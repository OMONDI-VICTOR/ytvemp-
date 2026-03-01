# Course Content System - Setup & Testing Checklist

## Pre-Implementation Checklist

- [ ] Back up your current database
- [ ] Verify MySQL is running
- [ ] Have admin and learner test accounts ready
- [ ] Internet connection available (for TinyMCE CDN)

## Phase 1: Database Setup

### Execute SQL Script
- [ ] Open `setup_course_content_db.sql` in text editor
- [ ] Copy all SQL queries
- [ ] Open phpMyAdmin or MySQL client
- [ ] Select `youth_skills_db` database
- [ ] Paste SQL and execute
- [ ] Verify all 4 tables created:
  - [ ] `course_modules` table exists
  - [ ] `course_lessons` table exists
  - [ ] `lesson_resources` table exists
  - [ ] `user_lesson_progress` table exists

### Verify Table Structure
```sql
-- Run these to verify:
DESCRIBE course_modules;
DESCRIBE course_lessons;
DESCRIBE lesson_resources;
DESCRIBE user_lesson_progress;

-- Should show all expected columns
```

## Phase 2: File Deployment

### Verify All Files Created
- [ ] `/admin/manage_course_content.php`
- [ ] `/admin/add_course_module.php`
- [ ] `/admin/add_course_lesson.php`
- [ ] `/admin/edit_course_lesson.php`
- [ ] `/course_content.php`
- [ ] `/view_lesson.php`

### Verify Documentation Files
- [ ] `QUICK_START_GUIDE.md`
- [ ] `COURSE_CONTENT_IMPLEMENTATION_GUIDE.md`
- [ ] `IMPLEMENTATION_SUMMARY.md`
- [ ] `ARCHITECTURE_DIAGRAM.md`
- [ ] `setup_course_content_db.sql`

### Verify Modifications
- [ ] `dashboard.php` has "View Content" button
- [ ] `admin/manage_courses.php` has "Content" button

## Phase 3: Admin Testing

### Test Login
- [ ] Admin can log in to admin panel
- [ ] Session is set correctly
- [ ] Role is set to "admin"

### Test Course Management
- [ ] Go to Manage Courses
- [ ] See list of existing courses
- [ ] Click "Content" button on a course
- [ ] See manage_course_content.php loads

### Test Module Creation
- [ ] Click "Add New Module"
- [ ] See module form
- [ ] Enter module title (e.g., "Module 1: Basics")
- [ ] Enter description (e.g., "Learn the fundamentals")
- [ ] Click "Add Module"
- [ ] See module appears in list
- [ ] Module order is set correctly

### Test Lesson Creation
- [ ] Expand the module
- [ ] Click "Add Lesson" button
- [ ] See lesson form with rich text editor
- [ ] Enter lesson title (e.g., "Lesson 1: Introduction")
- [ ] Enter content using text editor:
  - [ ] Can make text **bold**
  - [ ] Can make text *italic*
  - [ ] Can create bullet lists
  - [ ] Can create numbered lists
  - [ ] Can add links
- [ ] Enter video URL (optional):
  - [ ] Try YouTube: `https://www.youtube.com/embed/VIDEOID`
- [ ] Enter duration (e.g., 15 minutes)
- [ ] Click "Add Lesson"
- [ ] Lesson appears in module

### Test Lesson Editing
- [ ] Click "Edit" on a lesson
- [ ] See edit form populated with existing data
- [ ] Change lesson title
- [ ] Change content
- [ ] Update duration
- [ ] Click "Update Lesson"
- [ ] Changes saved and visible

### Test Deletion
- [ ] Click "Delete" on a lesson
- [ ] See confirmation dialog
- [ ] Confirm deletion
- [ ] Lesson removed from list
- [ ] Try deleting module
- [ ] Module and all lessons removed

## Phase 4: Learner Testing

### Test Dashboard
- [ ] Log in as learner
- [ ] Go to dashboard
- [ ] See course cards (unchanged)
- [ ] See new "View Content" button on courses
- [ ] Button is positioned correctly

### Test Course Content View
- [ ] Click "View Content" on a course with modules
- [ ] See `course_content.php` loads
- [ ] See course title and description
- [ ] See progress bar (should be 0% initially)
- [ ] See all modules listed
- [ ] Modules show description if set
- [ ] Lessons appear under modules
- [ ] Can expand/collapse modules
- [ ] See lesson duration if set
- [ ] Lessons show no checkmark initially

### Test Lesson Viewing
- [ ] Click on a lesson
- [ ] See `view_lesson.php` loads
- [ ] See lesson title
- [ ] See breadcrumb navigation
- [ ] See module name
- [ ] See course name
- [ ] See lesson duration (if set)
- [ ] See formatted lesson content
- [ ] See video player (if URL was set)
- [ ] Test video playback if URL is valid

### Test Completion Tracking
- [ ] Click "Mark Lesson as Complete"
- [ ] See success message
- [ ] Return to lesson
- [ ] See "✅ You have completed this lesson" badge
- [ ] Go back to course content
- [ ] Lesson shows ✅ badge
- [ ] Progress bar increased (if there are multiple lessons)

### Test Progress Calculation
Steps (need multiple lessons):
1. Create 5 lessons in a module
2. Log in as learner
3. View course content
4. Progress should be 0% and 0/5 lessons
5. Mark lesson 1 complete → Progress = 20% (1/5)
6. Mark lesson 2 complete → Progress = 40% (2/5)
7. Mark lesson 3 complete → Progress = 60% (3/5)
8. Mark lesson 4 complete → Progress = 80% (4/5)
9. Mark lesson 5 complete → Progress = 100% (5/5)

### Test Lesson Navigation
- [ ] On lesson view, see "Next Lesson" button
- [ ] Click next lesson
- [ ] Navigate to next lesson successfully
- [ ] On last lesson, "Next Lesson" is disabled
- [ ] On first lesson, "Previous Lesson" is disabled
- [ ] Can navigate backwards through lessons

### Test Progress Sidebar
- [ ] On lesson view, see progress section in sidebar
- [ ] Shows "X/Y Lessons"
- [ ] Shows progress percentage
- [ ] Progress bar is visible
- [ ] Progress updates after marking complete

### Test Multiple Users
- [ ] Log out as learner 1
- [ ] Log in as learner 2
- [ ] View same course
- [ ] Learner 2 sees 0% progress (not affected by learner 1)
- [ ] Mark a lesson complete as learner 2
- [ ] Log in as learner 1
- [ ] Learner 1 still shows original progress
- [ ] Data is properly isolated

## Phase 5: Integration Testing

### Test Quiz Integration
- [ ] After viewing lessons, see "Ready for Assessment?" section
- [ ] Click "Take Quiz"
- [ ] Existing quiz.php loads (should work as before)
- [ ] Can complete quiz
- [ ] Can get certificate

### Test Certificate Integration
- [ ] After passing quiz, see certificate button
- [ ] Click to download certificate
- [ ] Certificate download works

### Test Flow
1. Go to dashboard
2. View course content ✅
3. View all lessons ✅
4. Complete lessons ✅
5. See progress ✅
6. Take quiz ✅
7. Download certificate ✅

## Phase 6: Edge Case Testing

### Empty Content
- [ ] Create module with no lessons
- [ ] Learner views course
- [ ] Module shows but with no lessons listed
- [ ] No errors

### No Modules
- [ ] Course with no modules at all
- [ ] Learner views course
- [ ] Shows "No modules" message
- [ ] No errors

### Special Characters in Content
- [ ] Add lesson with special characters (é, ñ, 中文, etc.)
- [ ] Content displays correctly
- [ ] No encoding issues

### Long Content
- [ ] Add lesson with very long content (>10,000 characters)
- [ ] Page loads without freezing
- [ ] Content displays correctly
- [ ] No performance issues

### Large Courses
- [ ] Create course with 50+ lessons
- [ ] Course content page loads quickly
- [ ] No timeout errors
- [ ] Navigation works smoothly

## Phase 7: Security Testing

### SQL Injection Test
- [ ] Try adding module with title: `'; DROP TABLE course_modules; --`
- [ ] System safely handles special characters
- [ ] Data stored correctly
- [ ] Table is not dropped

### XSS Test
- [ ] Try adding lesson content: `<script>alert('test')</script>`
- [ ] Script does not execute
- [ ] Content is displayed as text

### Access Control
- [ ] Learner tries to access admin page directly
- [ ] Redirected to login
- [ ] Cannot add content
- [ ] Admin can access all content
- [ ] Learner cannot modify course content

### Session Hijacking
- [ ] Log in as learner
- [ ] Mark lesson complete
- [ ] Log out
- [ ] Log in as different learner
- [ ] Original learner's progress is not visible

## Phase 8: Cross-Browser Testing

### Chrome/Edge
- [ ] Course content displays correctly
- [ ] Rich text editor works
- [ ] Videos embed and play
- [ ] Progress tracking works
- [ ] All buttons functional

### Firefox
- [ ] Same tests as Chrome
- [ ] No Firefox-specific issues

### Safari
- [ ] Same tests as Chrome
- [ ] No Safari-specific issues

## Phase 9: Mobile Responsiveness Testing

- [ ] Dashboard course card visible on mobile
- [ ] "View Content" button accessible
- [ ] Course content page responsive on mobile
- [ ] Modules collapse/expand on mobile
- [ ] Lesson view readable on mobile
- [ ] Video player works on mobile
- [ ] Sidebar visible/accessible on mobile
- [ ] Buttons are mobile-friendly size

## Phase 10: Performance Testing

### Page Load Times
- [ ] course_content.php loads in < 2 seconds
- [ ] view_lesson.php loads in < 1 second
- [ ] manage_course_content.php loads in < 1 second

### Database Performance
- [ ] Queries with multiple lessons execute quickly
- [ ] No N+1 query problems
- [ ] Progress calculations are fast

## Final Validation

### Code Quality
- [ ] All PHP files follow same coding style
- [ ] No syntax errors
- [ ] No undefined variables
- [ ] Proper error handling
- [ ] Comments are clear

### User Experience
- [ ] Workflow is intuitive
- [ ] Error messages are helpful
- [ ] Success messages are clear
- [ ] Navigation is logical

### Documentation
- [ ] QUICK_START_GUIDE is complete
- [ ] IMPLEMENTATION_SUMMARY is accurate
- [ ] ARCHITECTURE_DIAGRAM is helpful
- [ ] All code has comments

## Post-Launch Checklist

- [ ] Back up database with new tables
- [ ] Monitor for errors
- [ ] Get user feedback
- [ ] Track usage statistics
- [ ] Plan for future features

## Rollback Plan (If Needed)

If something goes wrong:
```sql
-- To remove all new tables:
DROP TABLE IF EXISTS lesson_resources;
DROP TABLE IF EXISTS user_lesson_progress;
DROP TABLE IF EXISTS course_lessons;
DROP TABLE IF EXISTS course_modules;

-- Restore dashboard.php and manage_courses.php from backup
```

## Success Criteria

You know it's working when:

✅ Admin can create modules and lessons
✅ Learners can view course content
✅ Learners can mark lessons complete
✅ Progress tracking works accurately
✅ Video embeds display correctly
✅ All navigation works as expected
✅ No errors in browser console
✅ Database stores data correctly
✅ Multiple users don't interfere
✅ System is responsive on mobile
✅ All security tests pass
✅ Performance is acceptable

---

**Estimated Setup Time**: 1-2 hours
**Testing Time**: 2-3 hours
**Total Time**: 3-5 hours

Good luck! 🚀
