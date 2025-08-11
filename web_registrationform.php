<?php

// 1. Create table on plugin activation
register_activation_hook(__FILE__, 'student_form_create_table');
function student_form_create_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'student_enrollments';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        full_name VARCHAR(255) NOT NULL,
        dob DATE NOT NULL,
        gender VARCHAR(20) NOT NULL,
        grade VARCHAR(50) NOT NULL,
        school_name VARCHAR(255) NOT NULL,
        student_email VARCHAR(255) NOT NULL,
        course_name VARCHAR(255) NOT NULL,
        experience TEXT NULL,
        parent_name VARCHAR(255) NOT NULL,
        parent_phone VARCHAR(50) NOT NULL,
        guardian_email VARCHAR(255) DEFAULT NULL,
        submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        is_enrolled TINYINT(1) DEFAULT 0,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}


// âœ… 3. Handle form submission
add_action('init', 'handle_student_form_submission');
function handle_student_form_submission() {
    if (isset($_POST['submit_student_form'])) {
        global $wpdb;
        $table = $wpdb->prefix . 'student_enrollments';

        $wpdb->insert($table, [
            'full_name'      => sanitize_text_field($_POST['full_name']),
            'dob'            => sanitize_text_field($_POST['dob']),
            'gender'         => sanitize_text_field($_POST['gender']),
            'grade'          => sanitize_text_field($_POST['grade']),
            'school_name'    => sanitize_text_field($_POST['school_name']),
            'student_email'  => sanitize_email($_POST['student_email']),
            'course_name'    => sanitize_text_field($_POST['course']),
            'experience'     => isset($_POST['experience']) ? sanitize_text_field($_POST['experience']) : '',
            'parent_name'    => sanitize_text_field($_POST['parent_name']),
            'parent_phone'   => sanitize_text_field($_POST['parent_phone']),
            'guardian_email' => sanitize_email($_POST['guardian_email']),
            'submitted_at'   => current_time('mysql')
        ]);
    }
}

add_action('admin_menu', function () {
    add_menu_page('Student Registrations', 'Student Registrations', 'manage_options', 'student-form-entries', function () {
        global $wpdb;
        $table = $wpdb->prefix . 'student_enrollments';
        $entries = $wpdb->get_results("SELECT * FROM $table", ARRAY_A);

        echo '<div class="wrap">';
        echo '<h1 style="margin-bottom: 20px;">ðŸ“‹ Student Registrations</h1>';

        // Add modern CSS with vertical column borders
        echo '<style>
            .modern-table {
                width: 100%;
                border-collapse: collapse;
                font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
                background: #fff;
                box-shadow: 0 4px 12px rgba(0,0,0,0.05);
                border-radius: 8px;
                overflow: hidden;
            }
            .modern-table thead {
                background: #f4f6f8;
            }
            .modern-table th, .modern-table td {
                padding: 12px 15px;
                text-align: left;
                font-size: 14px;
                border-bottom: 1px solid #e0e0e0;
                border-right: 1px solid #e0e0e0;
            }
            .modern-table th:last-child,
            .modern-table td:last-child {
                border-right: none; /* Remove right border on last column */
            }
            .modern-table tbody tr:hover {
                background-color: #f1faff;
            }
            .modern-table th {
                color: #333;
                font-weight: 600;
            }
            .modern-table td {
                color: #555;
            }
        </style>';

        if ($entries) {
            echo '<table class="modern-table"><thead><tr>
                <th>Name</th><th>DOB</th><th>Gender</th><th>Grade</th><th>School</th>
                <th>Email</th><th>Course</th><th>Exp</th>
                <th>Parent</th><th>Phone</th><th>Guardian Email</th><th>Time</th>
            </tr></thead><tbody>';
            foreach ($entries as $entry) {
                echo "<tr>
                    <td>{$entry['full_name']}</td>
                    <td>{$entry['dob']}</td>
                    <td>{$entry['gender']}</td>
                    <td>{$entry['grade']}</td>
                    <td>{$entry['school_name']}</td>
                    <td>{$entry['student_email']}</td>
                    <td>{$entry['course_name']}</td>
                    <td>{$entry['experience']}</td>
                    <td>{$entry['parent_name']}</td>
                    <td>{$entry['parent_phone']}</td>
                    <td>{$entry['guardian_email']}</td>
                    <td>{$entry['submitted_at']}</td>
                </tr>";
            }
            echo '</tbody></table>';
        } else {
            echo '<p>No entries yet.</p>';
        }

        echo '</div>';
    }, 'dashicons-list-view', 26);
});


// ðŸ§© 5. Shortcode to display the form
add_shortcode('student_registration_form', 'student_registration_form_shortcode');
function student_registration_form_shortcode() {
    ob_start();
    // âœ… Get course from URL
    $selected_course = isset($_GET['course']) ? sanitize_text_field($_GET['course']) : '';
    if (isset($_POST['submit_student_form'])) {
        echo '<div class="success-message">ðŸŽ‰ Registration successful!</div>';
    }
    ?>

    <style>
        .form-container {
            max-width: 600px;
            margin: 30px auto;
            padding: 30px;
            background: #f9f9f9;
            border-radius: 10px;
            overflow: hidden; 
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
.form-container input:not([type="radio"]):not([type="checkbox"]),
.form-container select {
    width: 100%;
    padding: 10px;
    margin-bottom: 15px;
}

        .radio-group {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        .radio-group label {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .form-container input[type="submit"] {
            width: auto;
            background: linear-gradient(135deg, #0090CD, #6C72E2);
            color: black;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            padding: 12px 24px;
            font-weight: bold;
            transition: opacity 0.3s ease;
        }
        .form-container input[type="submit"]:hover {
            opacity: 0.9;
        }
       .form-row {
           display: flex;
           align-items: center;
           margin-bottom: 15px;
       }
       
       .field-label {
           min-width: 80px;
           white-space: nowrap;
       }
       
       .radio-options {
           display: flex;
           gap: 25px;
       }
       
       .radio-options label {
           display: flex;
           align-items: center;
           gap: 10px;
       }

        .success-message {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            padding: 15px;
            margin: 20px auto;
            max-width: 600px;
            border-radius: 8px;
            font-weight: bold;
            text-align: center;
        }
/* Desktop layout: label on left, radios on right */
/* Mobile Radio Button Alignment Fix */
@media (max-width: 650px) {
  .form-row {
    margin-bottom: 20px;
  }

  .field-label {
    display: block;
    margin-bottom: 12px;
    font-weight: bold;
    font-size: 16px;
  }

  .radio-options {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-left: 5px; /* Slight indentation */
    padding-left: 5px; /* Space for bullet */
  }

  .radio-options label {
    display: flex;
    align-items: center;
    gap: 10px;
    position: relative;
    padding-left: 15px; /* Space for bullet */
    line-height: 1.4;
  }

  /* Custom bullet styling */
  .radio-options label::before {

    position: absolute;
    left: 0;
    width: 10px;
    color: #333;
  }

  /* Radio button styling */
  .radio-options input[type="radio"] {
    margin: 0;
    width: 16px;
    height: 16px;
  }
}                                      
    </style>

    <form method="post" class="form-container">
        <h3>Student Details</h3>
        <input type="text" name="full_name" placeholder="Full Name" required>
        <input type="date" name="dob" required>

        <div class="form-row">
            <label class="field-label">Gender:</label>
            <div class="radio-options">
                <label><input type="radio" name="gender" value="Male" required> Male</label>
                <label><input type="radio" name="gender" value="Female"> Female</label>
            </div>
        </div>

        <input type="text" name="grade" placeholder="Grade" required>
        <input type="text" name="school_name" placeholder="School Name" required>
        <input type="email" name="student_email" placeholder="Student Email (optional)">

        <select name="course" id="course" required>
            <option value="">Select a Course</option>
            <option value="1-Month STEM Course for Kids" <?php selected($selected_course, '1-Month STEM Course for Kids'); ?>>1-Month STEM Course for Kids</option> 
            <option value="6-Month Arduino Advanced Course" <?php selected($selected_course, '6-Month Arduino Advanced Course'); ?>>6-Month Arduino Advanced Course</option>                            
            <option value="6-Month Arduino & Electronics Learning Plan" <?php selected($selected_course, '6-Month Arduino & Electronics Learning Plan'); ?>>6-Month Arduino & Electronics Learning Plan</option>
            <option value="6-Month Advanced Arduino & IoT Learning Plan" <?php selected($selected_course, '6-Month Advanced Arduino & IoT Learning Plan'); ?>>6-Month Advanced Arduino & IoT Learning Plan</option>
            <option value="3-Month Line Follower Robot Training Plan" <?php selected($selected_course, '3-Month Line Follower Robot Training Plan'); ?>>3-Month Line Follower Robot Training Plan</option>
            <option value="3-Month Maze Solving Robot Training Plan" <?php selected($selected_course, '3-Month Maze Solving Robot Training Plan'); ?>>3-Month Maze Solving Robot Training Plan</option>
        </select>
        
<div class="form-row">
  <label class="field-label">Experience:</label>
  <div class="radio-options">
    <label><input type="radio" name="experience" value="Beginner"> Beginner</label>
    <label><input type="radio" name="experience" value="Average"> Average</label>
    <label><input type="radio" name="experience" value="Experienced"> Experienced</label>
  </div>
</div>



        <h3>Parent/Guardian Details</h3>
        <input type="text" name="parent_name" placeholder="Parent Name" required>
        <input type="text" name="parent_phone" placeholder="Parent Phone" required>
        <input type="email" name="guardian_email" placeholder="Guardian Email (required)" required>

        <input type="submit" name="submit_student_form" value="Register">
    </form>

    <script>
        function toggleExperience(val) {
            document.getElementById('experience-field').style.display = val ? 'block' : 'none';
        }
    </script>

    <?php
    return ob_get_clean();
}
