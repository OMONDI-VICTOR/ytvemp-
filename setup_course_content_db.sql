-- SQL Setup for Course Content Management
-- Run these queries in your youth_skills_db database to set up the course content structure

-- Create skills table (Categories of interest)
CREATE TABLE IF NOT EXISTS skills (
    id INT PRIMARY KEY AUTO_INCREMENT,
    skill_name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create courses table
CREATE TABLE IF NOT EXISTS courses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    course_title VARCHAR(255) NOT NULL,
    description TEXT,
    skill_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (skill_id) REFERENCES skills(id) ON DELETE SET NULL
);

-- Create course_modules table
CREATE TABLE IF NOT EXISTS course_modules (
    id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT NOT NULL,
    module_title VARCHAR(255) NOT NULL,
    module_order INT NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

-- Create course_lessons table
CREATE TABLE IF NOT EXISTS course_lessons (
    id INT PRIMARY KEY AUTO_INCREMENT,
    module_id INT NOT NULL,
    lesson_title VARCHAR(255) NOT NULL,
    lesson_order INT NOT NULL,
    content LONGTEXT,
    video_url VARCHAR(500),
    duration_minutes INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (module_id) REFERENCES course_modules(id) ON DELETE CASCADE
);

-- Create lesson_resources table (for downloadable materials)
CREATE TABLE IF NOT EXISTS lesson_resources (
    id INT PRIMARY KEY AUTO_INCREMENT,
    lesson_id INT NOT NULL,
    resource_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500),
    resource_type ENUM('pdf', 'doc', 'video', 'image', 'other') DEFAULT 'other',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (lesson_id) REFERENCES course_lessons(id) ON DELETE CASCADE
);

-- Create user_lesson_progress table (track learner progress)
CREATE TABLE IF NOT EXISTS user_lesson_progress (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    lesson_id INT NOT NULL,
    completed BOOLEAN DEFAULT FALSE,
    completed_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (lesson_id) REFERENCES course_lessons(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_lesson (user_id, lesson_id)
);

-- Create indexes for better performance
CREATE INDEX idx_course_id ON course_modules(course_id);
CREATE INDEX idx_module_id ON course_lessons(module_id);
CREATE INDEX idx_lesson_id ON lesson_resources(lesson_id);
CREATE INDEX idx_user_lesson ON user_lesson_progress(user_id, lesson_id);
CREATE INDEX idx_lesson_completed ON user_lesson_progress(completed);

-- (Optional) Sample data to test the system
-- INSERT INTO course_modules (course_id, module_title, description, module_order) 
-- VALUES (1, 'Getting Started', 'Learn the basics', 1);

-- INSERT INTO course_lessons (module_id, lesson_title, lesson_order, content, duration_minutes)
-- VALUES (1, 'Introduction', 1, '<h2>Welcome</h2><p>This is your first lesson.</p>', 10);
