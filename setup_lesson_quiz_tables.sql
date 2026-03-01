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
