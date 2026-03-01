# Lesson Quiz Section Implementation Guide

## Overview
Added comprehensive quiz functionality to lesson creation and editing. Admins can now add two types of questions:
- **Section A:** Multiple Choice Questions
- **Section B:** Short Answer Questions

## Database Setup

**IMPORTANT:** Run this SQL script first to create the required tables:

```sql
-- SQL Setup for Lesson-Based Quiz Questions
-- Run these queries in your youth_skills_db database to add lesson quiz tables

-- Create lesson_quiz_questions table
CREATE TABLE IF NOT EXISTS lesson_quiz_questions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    lesson_id INT NOT NULL,
    question_type ENUM('multiple_choice', 'short_answer') NOT NULL,
    question_text LONGTEXT NOT NULL,
    section ENUM('A', 'B') NOT NULL,
    question_order INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (lesson_id) REFERENCES course_lessons(id) ON DELETE CASCADE
);

-- Create lesson_quiz_options table (for multiple choice answers)
CREATE TABLE IF NOT EXISTS lesson_quiz_options (
    id INT PRIMARY KEY AUTO_INCREMENT,
    question_id INT NOT NULL,
    option_text VARCHAR(500) NOT NULL,
    is_correct BOOLEAN DEFAULT FALSE,
    option_order INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (question_id) REFERENCES lesson_quiz_questions(id) ON DELETE CASCADE
);

-- Create indexes for better performance
CREATE INDEX idx_lesson_quiz ON lesson_quiz_questions(lesson_id);
CREATE INDEX idx_question_quiz_options ON lesson_quiz_options(question_id);
CREATE INDEX idx_section_type ON lesson_quiz_questions(section, question_type);
```

Or use the provided SQL file:
```
setup_lesson_quiz_tables.sql
```

## Files Modified

1. **admin/add_course_lesson.php**
   - Added YouTube embed conversion function
   - Added quiz section UI for Section A (Multiple Choice) and Section B (Short Answer)
   - Added backend logic to save quiz questions and options to the database

2. **admin/edit_course_lesson.php**
   - Added quiz section UI for editing lessons
   - Added logic to load existing quiz questions from the database
   - Added logic to update quiz questions when lesson is updated
   - Questions persist and display when re-editing a lesson

## Feature Details

### Section A: Multiple Choice Questions
- Add unlimited multiple choice questions
- Each question can have 3+ answer options
- Mark one or more options as "correct"
- Options are displayed with labels (A, B, C, D, etc.)
- Questions stored with order and linked to lesson

### Section B: Short Answer Questions
- Add unlimited short answer questions
- Learners will type their own answers
- Useful for essay-style or open-ended questions
- Questions stored with order and linked to lesson

## How to Use

### Adding Questions (New Lesson)
1. Go to Admin Panel → Manage Courses → Select Course → Select Module → "Add Lesson"
2. Fill in lesson details (Title, Content, Video, etc.)
3. Scroll down to find quiz sections:
   - **Section A: 📋 Multiple Choice Questions**
   - **Section B: 📝 Short Answer Questions**
4. Click "+ Add Multiple Choice Question" or "+ Add Short Answer Question"
5. For Multiple Choice:
   - Enter the question text
   - Add answer options (click "+ Add Option" to add more than 3)
   - Check the checkbox for the correct answer(s)
6. For Short Answer:
   - Enter the question text
   - Learners will type their answers
7. Click "Add Lesson" to save everything

### Editing Questions (Existing Lesson)
1. Go to Admin Panel → Manage Courses → Select Course → Select Module → "Edit" (on existing lesson)
2. Scroll down to quiz sections
3. Existing questions will be loaded automatically
4. Edit/add/remove questions as needed
5. Click "Update Lesson" to save changes

## Data Storage

### Questions Table (lesson_quiz_questions)
- `id`: Question ID
- `lesson_id`: Link to course_lessons
- `question_type`: 'multiple_choice' or 'short_answer'
- `question_text`: The question content
- `section`: 'A' or 'B'
- `question_order`: Order within the lesson

### Options Table (lesson_quiz_options)
- `id`: Option ID
- `question_id`: Link to lesson_quiz_questions
- `option_text`: The answer option text
- `is_correct`: Boolean (1 = correct, 0 = incorrect)
- `option_order`: Order of options (A=1, B=2, C=3, etc.)

## Frontend Considerations

When displaying lessons to learners, you can:
1. Query `lesson_quiz_questions` WHERE `lesson_id = ?`
2. For Section A (Multiple Choice), load options from `lesson_quiz_options`
3. For Section B (Short Answer), display text fields for learner input
4. Save learner responses in a `user_quiz_responses` table (create as needed)

## Example Query to Get All Questions for a Lesson

```php
$lesson_id = 123;

// Get all questions
$sql = "SELECT id, question_type, question_text, section, question_order 
        FROM lesson_quiz_questions 
        WHERE lesson_id = ? 
        ORDER BY section, question_order";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $lesson_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

while($row = mysqli_fetch_assoc($result)){
    if($row['question_type'] === 'multiple_choice'){
        // Get options for this question
        $sql_opts = "SELECT option_text, is_correct, option_order 
                     FROM lesson_quiz_options 
                     WHERE question_id = ? 
                     ORDER BY option_order";
        $stmt_opts = mysqli_prepare($conn, $sql_opts);
        mysqli_stmt_bind_param($stmt_opts, "i", $row['id']);
        mysqli_stmt_execute($stmt_opts);
        $result_opts = mysqli_stmt_get_result($stmt_opts);
        
        $options = [];
        while($opt = mysqli_fetch_assoc($result_opts)){
            $options[] = $opt;
        }
        mysqli_stmt_close($stmt_opts);
        
        // Display question and options
    } else {
        // Display short answer question with text field
    }
}
mysqli_stmt_close($stmt);
```

## Notes

- Quiz data is stored separately from lesson content
- Deleting a lesson automatically deletes its associated quiz questions (CASCADE)
- Questions and options are stored in JSON during form editing, then converted to database records on save
- The quiz interface is dynamic - add/remove questions without page reloads
- All data is validated and escaped to prevent SQL injection

## Next Steps

1. **Setup Database:** Run the SQL script to create tables
2. **Test:** Create a new lesson with quiz questions
3. **Verify:** Check that questions are saved and load when editing
4. **Display to Learners:** Create a lesson view page that displays quiz questions (not included in this implementation)

