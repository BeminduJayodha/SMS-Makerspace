<?php

// Create DB table on activation
register_activation_hook(__FILE__, 'student_registration_create_table');
function student_registration_create_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'student_registrations';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        student_id varchar(12) NOT NULL UNIQUE,
        student_name varchar(255) NOT NULL,
        dob date NOT NULL,
        gender VARCHAR(10),
        email varchar(255) NOT NULL,
        phone varchar(20) NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// Drop DB table on deactivation
register_deactivation_hook(__FILE__, 'student_registration_drop_table');
function student_registration_drop_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'student_registrations';
    $wpdb->query("DROP TABLE IF EXISTS $table_name");
}

// Add admin menu
add_action('admin_menu', 'student_registration_admin_menu');
function student_registration_admin_menu() {
    add_menu_page(
        'Student Registration', // Page title (shown in <title> and heading)
        'Makerspace',           // Menu title (shown in sidebar menu)
        'edit_pages',
        'student-registration',
        'render_student_registration_admin_page',
        'dashicons-welcome-learn-more',
        25
    );
    add_submenu_page(
        'student-registration', 
        'Student Registration',           
        'New Students',  
        'edit_pages',
        'student-registration',
        'render_student_registration_admin_page',
);
    add_submenu_page(
        'student-registration',         // parent slug
        'List Students',                // page title
        'Students List',                // menu title
        'manage_options',
        'students-list',
        'render_students_list_page'     // function to display content
    );
    //  2. New Course second
    add_submenu_page(
        'student-registration',
        'New Course',
        'New Course',
        'manage_options',
        'course-new',
        'render_course_new_page'
    );

    //  3. Course List third
    add_submenu_page(
        'student-registration',
        'List Course',
        'Course List',
        'manage_options',
        'course-selection',
        'render_course_selection_list_page'
    );
    // 4. New Instructor
    add_submenu_page(
        'student-registration',
        'New Instructor',
        'New Instructor',
        'manage_options',
        'instructor-new',
        'render_instructor_new_page'
    );

    // 5. List of Instructors
    add_submenu_page(
        'student-registration',
        'List Instructors',
        'List  Instructors',
        'manage_options',
        'instructors-list',
        'render_instructors_list_page'
    );
add_submenu_page(
    null, // ← Hidden from menu
    'Create New Batch', // Page title
    'Create New Batch', // Menu title (doesn't matter since it's hidden)
    'manage_options',   // Capability
    'create-new-batch', // Menu slug (used in admin.php?page=...)
    'create_new_batch_page' // Callback function
);

}


// Render admin page with form and process submission inline
function render_student_registration_admin_page() {
    $success = false;

    if (isset($_POST['submit_student_registration'])) {
        $success = student_registration_form_handler();
    }
    ?>
    <div class="wrap">
        <h1 style="text-align:center;">🖊️ Student Registration</h1>

        <?php if ($success) : ?>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const redirectTo = '<?php echo admin_url("admin.php?page=course-selection"); ?>'; // Change this to your target page URL

                    if (confirm("✅ Student registered successfully!\n\nClick OK to go to Course Selection.")) {
                        window.location.href = redirectTo;
                    }
                });
            </script>
        <?php endif; ?>

        <style>
            .student-form-wrapper {
                display: flex;
                justify-content: center;
                align-items: flex-start;
                margin-top: 40px;
            }

            .student-form-container {
                background: #fff;
                border: 1px solid #ccd0d4;
                padding: 30px;
                border-radius: 8px;
                max-width: 600px;
                width: 100%;
                box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            }

            .student-form-container .form-row {
                margin-bottom: 20px;
            }

            .student-form-container label {
                font-weight: 600;
                display: block;
                margin-bottom: 6px;
                color: #111;
            }

            .student-form-container input[type="text"],
            .student-form-container input[type="email"],
            .student-form-container input[type="date"] {
                width: 100%;
                padding: 8px 12px;
                font-size: 14px;
                border: 1px solid #ccd0d4;
                border-radius: 4px;
            }

            .student-form-container .dashicons {
                color: #000; /* black icons */
                margin-right: 6px;
            }
           .form-row label input[type="radio"] {
                margin-right: 5px;
                margin-left: 10px;
            }
           .gender-options {
               display: flex;
               gap: 20px;
               align-items: center;
           }

        </style>

        <div class="student-form-wrapper">
            <div class="student-form-container">
                <form method="post" action="">
                    <div class="form-row">
                        <label for="student_name"><span class="dashicons dashicons-admin-users"></span> Full Name</label>
                        <input type="text" name="student_name" required>
                    </div>

                    <div class="form-row">
                        <label for="dob"><span class="dashicons dashicons-calendar-alt"></span> Date of Birth</label>
                        <input type="date" name="dob" required>
                    </div>
                    <div class="form-row">
                        <label><span class="dashicons dashicons-groups"></span> Gender</label>
                        <div style="display: flex; gap: 20px; align-items: center;">
                            <label><input type="radio" name="gender" value="Male" required> Male</label>
                            <label><input type="radio" name="gender" value="Female" required> Female</label>
                        </div>
                    </div>


                    <div class="form-row">
                        <label for="email"><span class="dashicons dashicons-email-alt"></span> Email Address</label>
                        <input type="email" name="email" required>
                    </div>

                    <div class="form-row">
                        <label for="phone"><span class="dashicons dashicons-phone"></span> Phone Number</label>
                        <input type="text" name="phone" required>
                    </div>

                    <input type="submit" name="submit_student_registration" class="button button-primary" value="Register Student">
                </form>
            </div>
        </div>
    </div>
    <?php
}

// Handle form submission, return true on success
function student_registration_form_handler() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'student_registrations';

    $student_id = generate_unique_student_id($wpdb, $table_name);

    $student_name = sanitize_text_field($_POST['student_name']);
    $dob = sanitize_text_field($_POST['dob']);
    $gender = sanitize_text_field($_POST['gender']);
    $email = sanitize_email($_POST['email']);
    $phone = sanitize_text_field($_POST['phone']);

    $inserted = $wpdb->insert($table_name, [
        'student_id'    => $student_id,
        'student_name'  => $student_name,
        'dob'           => $dob,
        'email'         => $email,
        'phone'         => $phone,
        'gender'        => $gender
    ]);

    return ($inserted !== false);
}

// Generate 12-digit unique student ID
function generate_unique_student_id($wpdb, $table_name) {
    // Get max sequence number ignoring prefix
    $max_suffix = $wpdb->get_var(
        "SELECT MAX(CAST(SUBSTRING(student_id, 7, 6) AS UNSIGNED)) FROM $table_name"
    );

    if (!$max_suffix) {
        $max_suffix = 0;
    }

    $new_suffix = str_pad($max_suffix + 1, 6, '0', STR_PAD_LEFT);

    $prefix = date('Ym'); // current year and month

    return $prefix . $new_suffix; // e.g. 202507000013
}
// Add Course Selection admin page
add_action('admin_menu', 'course_selection_admin_menu');



register_activation_hook(__FILE__, 'create_course_enrollments_table');
register_deactivation_hook(__FILE__, 'delete_course_enrollments_table');

function load_dashicons_for_admin() {
    wp_enqueue_style('dashicons');
}
add_action('admin_enqueue_scripts', 'load_dashicons_for_admin');


function create_course_enrollments_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'course_enrollments';
    $charset_collate = $wpdb->get_charset_collate();

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    $sql = "CREATE TABLE $table_name (
        id INT NOT NULL AUTO_INCREMENT,
        course_id VARCHAR(100) NOT NULL,
        course_name VARCHAR(255) NOT NULL,
        modules TEXT NOT NULL,
        course_fee DECIMAL(10,2) DEFAULT 0.00,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";

    dbDelta($sql);
}


function delete_course_enrollments_table() {
    global $wpdb;
    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}course_enrollments");
}


function render_students_list_page() { 
    global $wpdb;

    $table_name = $wpdb->prefix . 'student_registrations';
    $students = $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC");

    echo '<div class="wrap">';
    echo '<h1>Students List</h1>';

    // Modern table styling (reuse batch table CSS)
    echo '<style>
        table.wp-list-table {
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
            background: #fff;
            font-family: "Segoe UI", Roboto, Arial, sans-serif;
            font-size: 14px;
            border: 1px solid #ccc;
            border-radius: 6px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        table.wp-list-table th,
        table.wp-list-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
            border-right: 1px solid #eee;
            text-align: center;
        }

        table.wp-list-table th {
            background-color: #bbbbbb;
            font-weight: 600;
            color: #333;
        }

        table.wp-list-table th:last-child,
        table.wp-list-table td:last-child {
            border-right: none;
        }

        table.wp-list-table tr:last-child td {
            border-bottom: none;
        }

        table.wp-list-table tbody tr:nth-child(even) {
            background-color: #f7f7f7;
        }

        table.wp-list-table tbody tr:nth-child(odd) {
            background-color: #ffffff;
        }

        table.wp-list-table tbody tr:hover {
            background-color: #e6f4ff !important;
            cursor: pointer;
        }

        .wp-list-table th, .wp-list-table td {
            vertical-align: middle;
        }
    </style>';

    if (!empty($students)) {
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead>
                <tr>
                    <th>No.</th>
                    <th>Student ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Registered Date</th>
                </tr>
              </thead><tbody>';

        $count = 1;
        foreach ($students as $student) {
            echo '<tr>';
            echo '<td>' . esc_html($count++) . '</td>';
            echo '<td>' . esc_html($student->student_id) . '</td>';
            echo '<td>' . esc_html($student->student_name) . '</td>';
            echo '<td>' . esc_html($student->email) . '</td>';
            echo '<td>' . esc_html($student->phone) . '</td>';
            echo '<td>' . esc_html(date('Y-m-d', strtotime($student->created_at))) . '</td>';
            echo '</tr>';
        }

        echo '</tbody></table>';
    } else {
        echo '<p>No student records found.</p>';
    }

    echo '</div>';
}

function course_selection_admin_menu() { 

    add_submenu_page(
        'student-registration',
        'Course Batches',
        'Course Batches',
        'manage_options',
        'course-batches',
        'render_course_batches_page'
    );



}
add_action('admin_menu', 'course_selection_admin_menu');

function render_course_selection_styles() {
    ?>
    <style>
        .nav-tab-wrapper .nav-tab {
            margin: 0 !important;
            padding: 0 12px;
        }
        .course-list-tab, .new-course-tab {
            margin-top: 0;
            padding-top: 0;
        }
        .course-list-tab table.wp-list-table,
        .new-course-tab table.wp-list-table {
            background: #fff;
            box-shadow: 0 2px 6px rgb(0 0 0 / 0.1);
            border-radius: 6px;
            overflow: hidden;
            font-family: Arial, sans-serif;
            font-size: 14px;
            width: 100%;
            border-collapse: collapse;
        }
        .course-list-tab table.wp-list-table th,
        .course-list-tab table.wp-list-table td {
            padding: 12px 15px;
            border: 1px solid #ddd;
            text-align: center;
        }
        .course-list-tab table.wp-list-table td:nth-child(3) {
            text-align: left; /* override only for Modules column */
        }
        .course-list-tab table.wp-list-table thead tr {
            background-color: #c3c7c9;
            color: #fff;
            font-weight: bold;
        }
        .course-list-tab table.wp-list-table tbody tr:hover {
            background-color: #e8f4fc;
        }
        .new-course-tab .form-container {
            background: #fff;
            border: 1px solid #ccd0d4;
            padding: 30px;
            border-radius: 8px;
            max-width: 700px;
            margin: 40px auto;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
        }
        .new-course-tab .form-table th {
            text-align: left;
            font-size: 15px;
            padding: 10px 0;
        }
        .new-course-tab .form-table input[type="text"] {
            width: 100%;
            padding: 10px;
            font-size: 14px;
            border: 1px solid #ccd0d4;
            border-radius: 4px;
        }
        .new-course-tab .add-button {
            padding: 6px 10px;
            font-size: 16px;
            background: #007cba;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 25px;
        }
        .new-course-tab .add-button:hover {
            background: #005a9e;
        }
        .new-course-tab .button-primary {
            background: #2271b1;
            border-color: #2271b1;
            border-radius: 4px;
            padding: 8px 16px;
            font-size: 14px;
        }
        .new-course-tab .button-primary:hover {
            background: #1d5a91;
        }
        .new-course-tab .module-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
            padding: 10px;
        }
        .new-course-tab .module-tag {
            background-color: #e0f0ff;
            border: 1px solid #007cba;
            border-radius: 20px;
            padding: 6px 12px;
            display: inline-flex;
            align-items: center;
            font-size: 14px;
            font-weight: 500;
            color: #0073aa;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }
        .new-course-tab .module-tag:hover {
            background-color: #d0eaff;
        }
        .new-course-tab .remove-module {
            margin-left: 8px;
            font-size: 16px;
            font-weight: bold;
            color: #d63638;
            cursor: pointer;
        }
        .new-course-tab .module-input-row {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }
        .new-course-tab .module-input-row input {
            flex: 1;
            padding: 8px 12px;
            font-size: 14px;
            border: 1px solid #ccd0d4;
            border-radius: 4px;
        }
    </style>
    <?php
}


function render_course_selection_list_page() {
    global $wpdb;

    echo '<div class="wrap"><h1>Course Selection</h1>';
    render_course_selection_styles();

    echo '<div class="course-list-tab">';

    $table_name = $wpdb->prefix . 'course_enrollments';
    $courses = $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC");

    // Handle update form with nonce check
    if (
        isset($_POST['update_course']) &&
        isset($_POST['update_course_nonce_field']) &&
        wp_verify_nonce($_POST['update_course_nonce_field'], 'update_course_nonce_action')
    ) {
        $course_db_id = intval($_POST['course_db_id']);
        $course_id = sanitize_text_field($_POST['course_id']);
        $course_name = sanitize_text_field($_POST['course_name']);
        $modules_input = sanitize_text_field($_POST['modules'] ?? '');
        $modules = array_map('trim', explode(',', $modules_input));
        $modules_json = json_encode(array_values(array_filter($modules)));
        $course_fee = floatval($_POST['course_fee']);

        $wpdb->update(
            $wpdb->prefix . 'course_enrollments',
            [
                'course_id' => $course_id,
                'course_name' => $course_name,
                'modules' => $modules_json,
                'course_fee' => $course_fee
            ],
            ['id' => $course_db_id]
        );

        echo '<div class="notice notice-success is-dismissible"><p>✅ Course updated successfully.</p></div>';
    }

    if ($courses) {
        $editing_id = isset($_GET['edit']) ? intval($_GET['edit']) : 0;

        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr><th>Course ID</th><th>Course Name</th><th>Course Fee</th><th>Modules</th><th>Actions</th></tr></thead>';

        foreach ($courses as $course) {
            $is_editing = ($editing_id === intval($course->id));
            $modules = json_decode($course->modules, true);
            $modules = is_array($modules) ? $modules : [];

            echo '<tr>';
            if ($is_editing) {
                echo '<form method="post">';
                echo wp_nonce_field('update_course_nonce_action', 'update_course_nonce_field', true, false);
                echo '<input type="hidden" name="course_db_id" value="' . esc_attr($course->id) . '">';
                echo '<td><input type="text" name="course_id" value="' . esc_attr($course->course_id) . '" required></td>';
                echo '<td><input type="text" name="course_name" value="' . esc_attr($course->course_name) . '" required></td>';
                echo '<td><input type="text" name="modules" value="' . esc_attr(implode(', ', $modules)) . '" style="width:100%;"></td>';
                echo '<td><input type="number" step="0.01" name="course_fee" value="' . esc_attr($course->course_fee) . '" required></td>';
                echo '<td>';
                echo '<input type="submit" name="update_course" class="button button-primary" value="Save"> ';
                echo '<a href="' . admin_url('admin.php?page=course-selection') . '" class="button">Cancel</a>';
                echo '</td>';
                echo '</form>';
            } else {
                echo '<td>' . esc_html($course->course_id) . '</td>';
                echo '<td>' . esc_html($course->course_name) . '</td>';
                echo '<td>' . esc_html(number_format($course->course_fee, 2)) . '</td>';
                echo '<td>';
                foreach ($modules as $module) {
                    echo esc_html($module) . '<br>';
                }
                echo '</td>';

                echo '<td>';
                echo '<a href="' . admin_url('admin.php?page=course-selection&edit=' . intval($course->id)) . '" class="button button-primary">Edit</a> ';
                echo '<a href="' . admin_url('admin.php?page=course-batches&course_name=' . urlencode($course->course_name)) . '" class="button button-primary">Batch</a>';
                echo '</td>';
            }
            echo '</tr>';
        }

        echo '</tbody></table>';
    } else {
        echo '<p>No courses found.</p>';
    }

    echo '</div></div>';
}

function render_course_new_page() {
    global $wpdb;

    echo '<div class="wrap"><h1>Course Selection</h1>';
    render_course_selection_styles();

    echo '<div class="new-course-tab">';

    // Handle form submission
    if (isset($_POST['save_course'])) {
        $course_id = sanitize_text_field($_POST['course_id']);
        $course_name = sanitize_text_field($_POST['course_name']);
        $modules = array_map('sanitize_text_field', $_POST['modules'] ?? []);
        $modules_json = json_encode(array_values(array_filter($modules)));
        $course_fee = floatval($_POST['course_fee']);

        $wpdb->insert(
            $wpdb->prefix . 'course_enrollments',
            [
                'course_id' => $course_id,
                'course_name' => $course_name,
                'modules' => $modules_json,
                'course_fee'  => $course_fee
            ]
        );

        echo '<div class="notice notice-success is-dismissible"><p>✅ Course <strong>' . esc_html($course_name) . '</strong> saved with ID <strong>' . esc_html($course_id) . '</strong>.</p></div>';
    }
    ?>

    <div class="form-container">
        <form method="post">
            <table class="form-table">
                <tr>
                    <th><label for="course_id">Course ID</label></th>
                    <td><input type="text" name="course_id" id="course_id" required></td>
                </tr>
                <tr>
                    <th><label for="course_name">Course Name</label></th>
                    <td><input type="text" name="course_name" id="course_name" required></td>
                </tr>
                <tr>
                    <th>Modules</th>
                    <td>
                        <div id="module-entry">
                            <div class="module-input-row">
                                <input type="text" id="new-module-name" placeholder="Enter Module Name" />
                                <button type="button" class="add-button" onclick="addModule()">+</button>
                            </div>
                            <div id="module-list" class="module-list"></div>
                        </div>
                    </td>
                </tr>
                    <tr>
                    <th><label for="course_fee">Course Fee (Rs)</label></th>
                    <td><input type="number" step="0.01" name="course_fee" id="course_fee" required></td>
                </tr>

            </table>
            <p style="text-align:center;">
                <input type="submit" name="save_course" class="button button-primary" value="Save Course">
            </p>
        </form>
    </div>

    <script>
        function addModule() {
            const input = document.getElementById('new-module-name');
            const value = input.value.trim();
            if (!value) return;

            const list = document.getElementById('module-list');
            const item = document.createElement('div');
            item.className = 'module-tag';
            item.innerHTML = `
                ${value}
                <input type="hidden" name="modules[]" value="${value}">
                <span class="remove-module" onclick="this.parentElement.remove()">×</span>
            `;
            list.appendChild(item);
            input.value = '';
        }
    </script>

    <?php
    echo '</div></div>';
}



// NEW INSTRUCTOR PAGE START
function render_instructor_new_page() {
    $success = false;

    if (isset($_POST['submit_instructor_registration'])) {
        $success = instructor_registration_form_handler();
    }
    ?>
    <div class="wrap">
        <h1 style="text-align:center;">👨‍🏫 New Instructor Registration</h1>

        <?php if ($success) : ?>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const redirectTo = '<?php echo admin_url("admin.php?page=instructors-list"); ?>';
                    if (confirm("✅ Instructor registered successfully!\n\nClick OK to view Instructor List.")) {
                        window.location.href = redirectTo;
                    }
                });
            </script>
        <?php endif; ?>

        <style>
            .instructor-form-wrapper {
                display: flex;
                justify-content: center;
                align-items: flex-start;
                margin-top: 40px;
            }

            .instructor-form-container {
                background: #fff;
                border: 1px solid #ccd0d4;
                padding: 30px;
                border-radius: 8px;
                max-width: 600px;
                width: 100%;
                box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            }

            .instructor-form-container .form-row {
                margin-bottom: 20px;
            }

            .instructor-form-container label {
                font-weight: 600;
                display: block;
                margin-bottom: 6px;
                color: #111;
            }

            .instructor-form-container input[type="text"],
            .instructor-form-container input[type="email"],
            .instructor-form-container input[type="date"] {
                width: 100%;
                padding: 8px 12px;
                font-size: 14px;
                border: 1px solid #ccd0d4;
                border-radius: 4px;
            }

            .instructor-form-container .dashicons {
                color: #000;
                margin-right: 6px;
            }
            .gender-options {
                display: flex;
                gap: 20px;
                align-items: center;
            }
        </style>

        <div class="instructor-form-wrapper">
            <div class="instructor-form-container">
                <form method="post" action="">
                    <div class="form-row">
                        <label for="instructor_name"><span class="dashicons dashicons-businessman"></span> Full Name</label>
                        <input type="text" name="instructor_name" required>
                    </div>
                    <div class="form-row">
                        <label><span class="dashicons dashicons-groups"></span> Gender</label>
                        <div class="gender-options">
                            <label><input type="radio" name="gender" value="Male" required> Male</label>
                            <label><input type="radio" name="gender" value="Female" required> Female</label>
                        </div>
                    </div>
                    <div class="form-row">
                        <label for="subject"><span class="dashicons dashicons-welcome-learn-more"></span> Subject / Expertise</label>
                        <input type="text" name="subject" required>
                    </div>

                    <div class="form-row">
                        <label for="email"><span class="dashicons dashicons-email-alt"></span> Email Address</label>
                        <input type="email" name="email" required>
                    </div>

                    <div class="form-row">
                        <label for="phone"><span class="dashicons dashicons-phone"></span> Phone Number</label>
                        <input type="text" name="phone" required>
                    </div>

                    <input type="submit" name="submit_instructor_registration" class="button button-primary" value="Register Instructor">
                </form>
            </div>
        </div>
    </div>
    <?php
}
function instructor_registration_form_handler() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'instructors';

    $instructor_name = sanitize_text_field($_POST['instructor_name']);
    $gender = sanitize_text_field($_POST['gender']);
    $subject = sanitize_text_field($_POST['subject']);
    $email = sanitize_email($_POST['email']);
    $phone = sanitize_text_field($_POST['phone']);

    $inserted = $wpdb->insert($table_name, [
        'instructor_name' => $instructor_name,
        'gender'          => $gender,
        'subject'          => $subject,
        'email'            => $email,
        'phone'            => $phone,
        'created_at'       => current_time('mysql', 1)
    ]);

    return ($inserted !== false);
}
register_activation_hook(__FILE__, 'create_instructors_table_on_activation');

function create_instructors_table_on_activation() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'instructors';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id INT(11) NOT NULL AUTO_INCREMENT,
        instructor_name VARCHAR(255) NOT NULL,
        gender VARCHAR(20) NOT NULL,
        subject VARCHAR(255) NOT NULL,
        email VARCHAR(100) NOT NULL,
        phone VARCHAR(50) NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}

register_deactivation_hook(__FILE__, 'my_plugin_deactivate');

function my_plugin_deactivate() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'instructors';

    // Drop the table if it exists
    $wpdb->query("DROP TABLE IF EXISTS $table_name");
}

// NEW INSTRUCTOR PAGE END

function render_instructors_list_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'instructors';

    $instructors = $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC");

    echo '<div class="wrap"><h1>List of Instructors</h1>';

    echo '<style>
        table.wp-list-table {
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
            background: #fff;
            font-family: "Segoe UI", Roboto, Arial, sans-serif;
            font-size: 14px;
            border: 1px solid #ccc;
            border-radius: 6px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        table.wp-list-table th,
        table.wp-list-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
            border-right: 1px solid #eee;
            text-align: center;
        }

        table.wp-list-table th {
            background-color: #bbbbbb;
            font-weight: 600;
            color: #333;
        }

        table.wp-list-table th:last-child,
        table.wp-list-table td:last-child {
            border-right: none;
        }

        table.wp-list-table tr:last-child td {
            border-bottom: none;
        }

        table.wp-list-table tbody tr:nth-child(even) {
            background-color: #f7f7f7;
        }

        table.wp-list-table tbody tr:nth-child(odd) {
            background-color: #ffffff;
        }

        table.wp-list-table tbody tr {
            transition: background-color 0.2s ease;
        }

        table.wp-list-table tbody tr:hover {
            background-color: #e6f4ff !important;
            cursor: pointer;
        }

        table.wp-list-table th:first-child,
        table.wp-list-table td:first-child {
            width: 40px;
            text-align: center;
        }

        .status-button {
            padding: 6px 12px;
            border-radius: 4px;
            border: none;
            font-size: 13px;
            font-weight: 600;
            cursor: default;
            pointer-events: none;
        }

        .status-button.enabled {
            background-color: #4caf50;
            color: white;
        }

        .status-button.disabled {
            background-color: #ccc;
            color: #666;
        }

        .disabled-row {
            color: #999 !important;
            opacity: 0.6;
            pointer-events: none;
        }

        .disabled-row td {
            text-decoration: line-through;
        }

        table.wp-list-table tbody tr:not(.disabled-row):hover {
            background-color: #e6f4ff !important;
        }
    </style>';

    if ($instructors) {
        echo '<table class="wp-list-table">';
        echo '<thead>
                <tr>
                    <th>No.</th>
                    <th>Instructor ID</th>
                    <th>Full Name</th>
                    <th>Gender</th>
                    <th>Email</th>
                    <th>Phone</th>

                </tr>
              </thead>';
        echo '<tbody>';

        $count = 1;
        foreach ($instructors as $inst) {
            echo "<tr class='$row_class'>";
            echo '<td>' . esc_html($count++) . '</td>';
            echo '<td>' . esc_html($inst->id) . '</td>';
            echo '<td>' . esc_html($inst->instructor_name) . '</td>';
            echo '<td>' . esc_html($inst->gender) . '</td>';
            echo '<td>' . esc_html($inst->email) . '</td>';
            echo '<td>' . esc_html($inst->phone) . '</td>';

            echo '</tr>';
        }

        echo '</tbody></table>';
    } else {
        echo '<p>No instructors found.</p>';
    }

    echo '</div>';
}


add_action('admin_enqueue_scripts', function() {
    wp_enqueue_style('dashicons');
});


add_action('wp_ajax_get_applicants_by_batch', 'get_applicants_by_batch_callback');
function get_applicants_by_batch_callback() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized', 403);
    }

    $batch_no = sanitize_text_field($_POST['batch_no']);
    global $wpdb;

    // Get course_name for the batch
    $course_name = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT course_name FROM {$wpdb->prefix}custom_batches WHERE batch_no = %s LIMIT 1",
            $batch_no
        )
    );

    if (!$course_name) {
        echo "<p>No applicants found for this batch.</p>";
        wp_die();
    }

    $table_name = $wpdb->prefix . 'student_enrollments';

    // Query applicants from the enrollments table by course name
    $applicants = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT full_name, dob, gender, student_email, parent_phone 
             FROM $table_name 
             WHERE course_name = %s AND is_enrolled = 0",
            $course_name
        ),
        ARRAY_A
    );

if (empty($applicants)) {
    echo "<p>No applicants found for course: <strong>" . esc_html($course_name) . "</strong></p>";
} else {
    echo "<div style='max-height:500px; overflow:auto;'>
        <table style='width:100%; border-collapse:collapse;'>
            <thead>
                <tr>
                    <th style='border-bottom:1px solid #ccc; padding:8px;'>Student Name</th>
                    <th style='border-bottom:1px solid #ccc; padding:8px;'>DOB</th>
                    <th style='border-bottom:1px solid #ccc; padding:8px;'>Gender</th>
                    <th style='border-bottom:1px solid #ccc; padding:8px;'>Email</th>
                    <th style='border-bottom:1px solid #ccc; padding:8px;'>Phone Number</th>
                    <th style='border-bottom:1px solid #ccc; padding:8px; text-align:center;'>Actions</th>
                </tr>
            </thead>
            <tbody>";

foreach ($applicants as $index => $applicant) {
    echo "<tr data-email='" . esc_attr($applicant['student_email']) . "'>
        <td>
          <span class='view-field'>" . esc_html($applicant['full_name']) . "</span>
          <input class='edit-field' type='text' value='" . esc_attr($applicant['full_name']) . "' style='display:none;' />
        </td>
        <td>
          <span class='view-field'>" . esc_html($applicant['dob']) . "</span>
          <input class='edit-field' type='date' value='" . esc_attr($applicant['dob']) . "' style='display:none;' />
        </td>
        <td>
          <span class='view-field'>" . esc_html($applicant['gender']) . "</span>
          <select class='edit-field' style='display:none;'>
            <option value='Male'" . selected($applicant['gender'], 'Male', false) . ">Male</option>
            <option value='Female'" . selected($applicant['gender'], 'Female', false) . ">Female</option>
          </select>
        </td>
        <td><span class='view-field'>" . esc_html($applicant['student_email']) . "</span></td>
        <td>
          <span class='view-field'>" . esc_html($applicant['parent_phone']) . "</span>
          <input class='edit-field' type='text' value='" . esc_attr($applicant['parent_phone']) . "' style='display:none;' />
        </td>
<td style='padding:8px; border-bottom:1px solid #eee; text-align:center;'>
    <div style='display: flex; justify-content: center; gap: 8px;'>
        <button class='button button-primary enroll-btn' data-id='{$index}' data-email='" . esc_attr($applicant['student_email']) . "'>Enroll</button>
        
        <button class='button edit-btn' data-id='{$index}' style='border:none;'>
            <span class='dashicons dashicons-edit'></span>
        </button>
        
        <button class='button save-btn' data-id='{$index}' style='border:none; display:none;'>
            Save
        </button>
        
        <button class='button delete-btn' data-id='{$index}' style='border:none; color:red;'>
            <span class='dashicons dashicons-trash'></span>
        </button>
    </div>
</td>

    </tr>";
}


    echo "</tbody></table></div>";
}


    wp_die();
}




// BATCH PAGE //
function render_course_batches_page() {
global $wpdb;

    // Get course_name from URL if set
    $course_name_filter = isset($_GET['course_name']) ? sanitize_text_field($_GET['course_name']) : '';

    // Get all distinct courses for buttons
    $courses = $wpdb->get_results("SELECT DISTINCT course_name FROM {$wpdb->prefix}course_enrollments");

    echo '<div class="wrap"><h1>Ongoing Batches</h1>';

    // Show buttons for each course to create new batch with that course_name
if ($course_name_filter !== '') {
    echo '<p style="margin: 20px 0;">
        <a href="' . admin_url('admin.php?page=create-new-batch&course_name=' . urlencode($course_name_filter)) . '" 
           class="button button-primary" style="font-size:13px; padding: 6px 14px;">
            + Create New Batch
        </a>
    </p>';
}




$batch_table = $wpdb->prefix . 'custom_batches';

if ($course_name_filter !== '') {
    // Use prepared statements to avoid SQL injection
$batches = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT batch_no, instructor_name, course_name,
                MIN(start_date) AS start_date,
                MAX(end_date) AS end_date,
                start_time, end_time,
                MIN(created_at) AS created_at
         FROM $batch_table
         WHERE course_name = %s
         GROUP BY batch_no
         ORDER BY start_date DESC",
        $course_name_filter
    )
);


    echo '<h2>Batches for course: ' . esc_html($course_name_filter) . '</h2>';
} else {
    $batches = $wpdb->get_results("SELECT * FROM $batch_table ORDER BY start_date DESC");
}


// Get course fee from wp_course_enrollments
$course_fee = $wpdb->get_var(
    $wpdb->prepare(
        "SELECT course_fee FROM {$wpdb->prefix}course_enrollments WHERE course_name = %s LIMIT 1",
        $course_name_filter
    )
);

if ($course_fee !== null) {
    echo '<p style="font-size: 16px; font-weight: bold; margin-bottom: 20px;">Course Fee: Rs. ' . esc_html(number_format($course_fee, 2)) . '</p>';
} else {
    echo '<p style="font-size: 16px; font-weight: bold; margin-bottom: 20px; color: red;">Course fee not found.</p>';
}

    // Styles
    echo '<style>
        table.wp-list-table {
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
            background: #fff;
            font-family: "Segoe UI", Roboto, Arial, sans-serif;
            font-size: 14px;
            border: 1px solid #ccc;
            border-radius: 6px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        table.wp-list-table th, table.wp-list-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
            border-right: 1px solid #eee;
            text-align: center;
        }
        table.wp-list-table th {
            background-color: #bbbbbb;
            font-weight: 600;
            color: #333;
        }
        table.wp-list-table th:last-child,
        table.wp-list-table td:last-child {
            border-right: none;
        }
        table.wp-list-table tr:last-child td {
            border-bottom: none;
        }
        table.wp-list-table tbody tr:nth-child(even) {
            background-color: #f7f7f7;
        }
        table.wp-list-table tbody tr:nth-child(odd) {
            background-color: #ffffff;
        }
        table.wp-list-table tbody tr:hover {
            background-color: #e6f4ff !important;
            cursor: pointer;
        }
        .status-button {
            padding: 6px 12px;
            border-radius: 4px;
            border: none;
            font-size: 13px;
            font-weight: 600;
            cursor: default;
            pointer-events: none;
        }
        .status-button.enabled {
            background-color: #4caf50;
            color: white;
        }
        .status-button.disabled {
            background-color: #ccc;
            color: #666;
        }
        .disabled-row {
            color: #999 !important;
            opacity: 0.6;
            pointer-events: none;
        }
        .disabled-row td {
            text-decoration: line-through;
        }
        .dropdown-row td {
            border-top: none;
            border-bottom: 1px solid #ddd;
            background-color: #f9f9f9;
        }

    </style>';

    if ($batches) {
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr>
            <th>No.</th>
            <th>Batch No</th>
            <th>Instructor Name</th>
            <th>Course Name</th>
            <th>Start Date</th>
            <th>End Date</th>
            <th>Start Time</th>
            <th>End Time</th>
            <th>Created At</th>
            <th>Status</th>
        </tr></thead>';
        echo '<tbody>';

        $current_date = date('Y-m-d');
        $row_number = 1;

        foreach ($batches as $batch) {
            $is_expired = ($batch->end_date < $current_date);
            $row_class = $is_expired ? 'disabled-row' : '';
            $status_button = $is_expired
                ? '<button class="status-button disabled" disabled>Disabled</button>'
                : '<button class="status-button enabled">Enabled</button>';

            echo '<tr class="' . $row_class . '">';
            echo '<td>' . $row_number++ . '</td>';
            echo '<td>' . esc_html($batch->batch_no) . '</td>';
            echo '<td>' . esc_html($batch->instructor_name) . '</td>';
            echo '<td>' . esc_html($batch->course_name) . '</td>';
            echo '<td>' . esc_html($batch->start_date) . '</td>';
            echo '<td>' . esc_html($batch->end_date) . '</td>';
            echo '<td>' . esc_html($batch->start_time) . '</td>';
            echo '<td>' . esc_html($batch->end_time) . '</td>';
            echo '<td>' . esc_html($batch->created_at) . '</td>';
            echo '<td>' . $status_button . '</td>';
            echo '</tr>';
        }

        echo '</tbody></table>';
    } else {
        echo '<p>No batches found.</p>';
    }
echo '
<div id="batchModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background-color:rgba(0,0,0,0.4); z-index:9999;">
  <div style="background:#fff; max-width:800px; margin:5% auto; padding:20px; border-radius:10px; text-align:center; position:relative;">
    <span id="closeModal" style="position:absolute; top:10px; right:15px; font-size:20px; cursor:pointer;">&times;</span>
    <h2 id="batchTitle">Batch Details</h2>
    <div id="batchDetails" style="margin-top:15px; font-size:14px; text-align:left;">
    <table style="width:100%; border-collapse:collapse;">
        <tbody>
            <tr>
                <td style="padding:6px; font-weight:bold; width:30%;">Course:</td>
                <td style="padding:6px;" id="detailCourse"></td>
            </tr>
            <tr>
                <td style="padding:6px; font-weight:bold;">Instructor:</td>
                <td style="padding:6px;" id="detailInstructor"></td>
            </tr>
            <tr>
                <td style="padding:6px; font-weight:bold;">Date Range:</td>
                <td style="padding:6px;" id="detailDateRange"></td>
            </tr>
            <tr>
                <td style="padding:6px; font-weight:bold;">Time Range:</td>
                <td style="padding:6px;" id="detailTimeRange"></td>
            </tr>
        </tbody>
    </table>
</div>

<div style="margin-top: 20px; text-align:left;">
  <h3 id="enrolledHeading" style="display:none; text-align:center;">Enrolled Students</h3>
  <div id="enrolledList" style="display:none;"></div>
  <p id="enrolledCount" style="font-weight: bold; margin: 10px 0; text-align:center; ">
    👨‍🎓 Enrolled Students: 
    <span id="enrolledCountNumber" style="color:green;">0</span> 
    <span style="color:#888;">/ 16</span>
  </p>
</div>

<div id="modalContent" style="margin-top:20px;">
  <button id="viewApplicantsBtn" class="button">
  View Applicants (<span id="applicantCount">0</span>)
</button>

  <a href="#" id="viewRegisteredBtn" class="button button-primary">View Registered Students</a>
</div>

      <div id="registeredList" style="display:none; margin-top:20px; text-align:left;"></div>
      <div id="applicantsList" style="margin-top:20px; text-align:left;"></div>

    </div>
  </div>
</div>
<div id="studentRegisterModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:10000;">
  <div style="background:#fff; width:500px; margin:5% auto; padding:20px; border-radius:8px; position:relative;">
    <span id="closeStudentModal" style="position:absolute; top:10px; right:15px; font-size:20px; cursor:pointer;">&times;</span>
    <h2 style="margin-top:0;">Student Registration</h2>
    <form id="studentRegisterForm">
        <input type="hidden" name="action" value="save_student_modal">
        <div class="form-row"><label>Full Name</label><input type="text" name="student_name" required></div>
        <div class="form-row"><label>Date of Birth</label><input type="date" name="dob" required></div>
        <div class="form-row">
            <label>Gender</label>
            <label><input type="radio" name="gender" value="Male"> Male</label>
            <label><input type="radio" name="gender" value="Female"> Female</label>
        </div>
        <div class="form-row"><label>Email</label><input type="email" name="email" required></div>
        <div class="form-row"><label>Phone</label><input type="text" name="phone" required></div>
        <button type="submit" class="button button-primary">Save</button>
    </form>
  </div>
</div>
<div id="newStudentOnlyModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:10000;">
  <div style="background:#fff; width:500px; margin:5% auto; padding:20px; border-radius:8px; position:relative;">
    <span id="closeNewStudentOnlyModal" style="position:absolute; top:10px; right:15px; font-size:20px; cursor:pointer;">&times;</span>
    <h2 style="margin-top:0;">New Student Registration</h2>
    <form id="newStudentOnlyForm">
        <input type="hidden" name="action" value="save_student_modal">
        <div class="form-row"><label>Full Name</label><input type="text" name="student_name" required></div>
        <div class="form-row"><label>Date of Birth</label><input type="date" name="dob" required></div>
        <div class="form-row">
            <label>Gender</label>
            <label><input type="radio" name="gender" value="Male"> Male</label>
            <label><input type="radio" name="gender" value="Female"> Female</label>
        </div>
        <div class="form-row"><label>Email</label><input type="email" name="email" required></div>
        <div class="form-row"><label>Phone</label><input type="text" name="phone" required></div>
        <button type="submit" class="button button-primary">Register</button>
    </form>
  </div>
</div>
';

echo ' 
<script> 
document.addEventListener("DOMContentLoaded", function () {
      // Delegate click inside registeredList for the new student button
  document.getElementById("registeredList").addEventListener("click", function(e) {
    if (e.target && e.target.id === "openStudentRegisterModal") {
      const form = document.getElementById("studentRegisterForm");
      form.reset();
      document.getElementById("studentRegisterModal").style.display = "block";
    }
  });

  // Close student modal as before
  document.getElementById("closeStudentModal").onclick = function() {
    document.getElementById("studentRegisterModal").style.display = "none";
  };
    const modal = document.getElementById("batchModal");
    const closeModal = document.getElementById("closeModal");
    const enrolledBtn = document.getElementById("viewEnrolledBtn");
    const applicantsBtn = document.getElementById("viewApplicantsBtn");
    const applicantsList = document.getElementById("applicantsList");
    

    

    document.querySelectorAll(".wp-list-table tbody tr").forEach(row => {
        row.addEventListener("click", function () {
            const batchNo = this.children[1].textContent.trim();
            const instructor = this.children[2].textContent.trim();
            const course = this.children[3].textContent.trim();
            const startDate = this.children[4].textContent.trim();
            const endDate = this.children[5].textContent.trim();
            const startTime = this.children[6].textContent.trim();
            const endTime = this.children[7].textContent.trim();

            currentBatchNo = batchNo;
            updateEnrolledCount(currentBatchNo);
            updateApplicantCount(currentBatchNo);
            document.getElementById("batchTitle").textContent = "Batch No: " + batchNo;
            document.getElementById("detailCourse").textContent = course;
            document.getElementById("detailInstructor").textContent = instructor;
            document.getElementById("detailDateRange").textContent = `${startDate} → ${endDate}`;
            document.getElementById("detailTimeRange").textContent = `${startTime} - ${endTime}`;
            document.getElementById("enrolledHeading").style.display = "block";
            document.getElementById("enrolledList").style.display = "block";





           const enrolledList = document.getElementById("enrolledList");
           const registeredList = document.getElementById("registeredList");
           
           // Hide other sections
           applicantsList.style.display = "none";
           registeredList.style.display = "none";
           enrolledList.innerHTML = "<p>Loading enrolled students...</p>";
           enrolledList.style.display = "block";
           document.getElementById("enrolledHeading").style.display = "block";
           
           // Fetch enrolled students automatically when modal opens
           fetch(ajaxurl, {
               method: "POST",
               headers: { "Content-Type": "application/x-www-form-urlencoded" },
               body: "action=get_enrolled_students_by_batch&batch_no=" + encodeURIComponent(currentBatchNo)
           })
           .then(res => res.text())
           .then(html => {
               enrolledList.innerHTML = html;
               // ✅ Reload enrolled list
                fetch(ajaxurl, {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: "action=get_enrolled_students_by_batch&batch_no=" + encodeURIComponent(currentBatchNo)
                })
                .then(res => res.text())
                .then(html => {
                    document.getElementById("enrolledList").innerHTML = html;
                    document.getElementById("enrolledList").style.display = "block";
                    document.getElementById("enrolledHeading").style.display = "block";
                });

           })
           .catch(() => {
               enrolledList.innerHTML = "<p style=\'color:red;\'>Failed to load enrolled students.</p>";
           });
           
           // Show modal
           modal.style.display = "block";
           
                   });
               });
           
let isApplicantsVisible = false;

applicantsBtn.onclick = function () {
    const enrolledList = document.getElementById("enrolledList");
    const registeredList = document.getElementById("registeredList");

    if (isApplicantsVisible) {
        // Hide applicants and show enrolled
        applicantsList.style.display = "none";
        enrolledList.style.display = "block";
        isApplicantsVisible = false;
    } else {
        // Show applicants, hide others
        //enrolledList.style.display = "none";
        registeredList.style.display = "none";

        applicantsList.innerHTML = "<p>Loading applicants...</p>";
        applicantsList.style.display = "block";
        isApplicantsVisible = true;

        fetch(ajaxurl, {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "action=get_applicants_by_batch&batch_no=" + encodeURIComponent(currentBatchNo)
        })
        .then(res => res.text())
        .then(html => {
            applicantsList.innerHTML = html;
        })
        .catch(() => {
            applicantsList.innerHTML = "<p style=\'color:red;\'>Failed to load applicants.</p>";
        });
    }

    return false;
};

         
         
             closeModal.addEventListener("click", function () {
                 modal.style.display = "none";
             });
         
             window.addEventListener("click", function (event) {
                 if (event.target === modal) {
                     modal.style.display = "none";
                 }
             });
         });
         let registeredEmails = [];
         let currentBatchNo = "";
         document.addEventListener("click", function(e) {
             if (e.target.classList.contains("enroll-btn")) {
                 const row = e.target.closest("tr");
                 const email = row.children[3].textContent.trim();
                 const phone = row.children[4].textContent.trim();
                 const name = row.children[0].textContent.trim();
                 const dob = row.children[1].textContent.trim();
                 const gender = row.children[2].textContent.trim();
         
                 if (registeredEmails.includes(email)) {
                     alert("✅ Student already registered. Proceeding to enroll...");
                     enrollStudent(email, currentBatchNo);
                 } else {
                     // Open Modal and Autofill
                     const modal = document.getElementById("studentRegisterModal");
                     const form = document.getElementById("studentRegisterForm");
         
                     form.student_name.value = name;
                     form.dob.value = dob;
                     form.gender.forEach(r => r.checked = (r.value === gender));
                     form.email.value = email;
                     form.phone.value = phone;
         
                     modal.style.display = "block";
                 }
             }
         });
         
         document.getElementById("closeStudentModal").onclick = () => {
             document.getElementById("studentRegisterModal").style.display = "none";
         };
         
         document.getElementById("studentRegisterForm").addEventListener("submit", function(e) {  
             e.preventDefault();
             const formData = new FormData(this);
             const email = formData.get("email"); // Moved here
             const modal = document.getElementById("studentRegisterModal");
         
             fetch(ajaxurl, {
                 method: "POST",
                 body: formData
             })
             .then(res => res.json())
             .then(res => {
                 if (res.success) {
                     modal.style.display = "none";
         
                     if (res.data.status === "exists") {
                         alert("⚠️ Student already registered!\nID: " + res.data.student_id);
                     } else {
                         alert("🎉 Student registered successfully!\nID: " + res.data.student_id);
                         registeredEmails.push(email); // prevent repeat
                     }
         
                     // ✅ Always enroll (even if already registered)
                     enrollStudent(email, currentBatchNo)
                     .then(() => {
                         alert("✅ Student enrolled successfully!");
                         updateEnrolledCount(currentBatchNo);
             // 🔍 Find the row by matching the email in 4th column (index 3)
             const rows = document.querySelectorAll("#applicantsList tbody tr");
             rows.forEach(row => {
                 const rowEmail = row.children[3].textContent.trim();
                 if (rowEmail === email) {
                     row.remove(); // 🗑️ Remove row from applicants list
                 }
             });
         })
                     .catch(error => {
                         alert("❌ Enrollment failed: " + error);
                     });
         
                 } else {
                     alert("❌ Failed to register student.\n" + res.data);
                 }
             })
         
             .catch((errorMessage) => {
                 alert("⚠️ Enrollment failed: " + errorMessage);
             });
         });
function enrollStudent(email, batchNo) {
    return new Promise((resolve, reject) => {
        // First, get current enrolled count
        fetch(ajaxurl, {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: new URLSearchParams({
                action: "get_enrolled_student_count",
                batch_no: batchNo
            })
        })
        .then(res => res.json())
        .then(res => {
            if (!res.success) {
                return reject("Failed to check enrollment count");
            }

            const enrolledCount = res.data.count;

            if (enrolledCount >= 16) {
                alert("This batch is full. No more students can be enrolled.");
                return reject("Batch full");
            }

            //Continue to enroll
            fetch(ajaxurl, {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: new URLSearchParams({
                    action: "enroll_student_in_course",
                    email: email,
                    batch_no: batchNo
                })
            })
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    resolve(true);
                } else {
                    reject(res.data || "Unknown error");
                }
            })
            .catch(() => {
                reject("Server error. Please try again.");
            });

        })
        .catch(() => {
            reject("Error checking batch capacity");
        });
    });
}

function updateEnrolledCount(batchNo) {
    fetch(ajaxurl, {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams({
            action: "get_enrolled_student_count",
            batch_no: batchNo
        })
    })
    .then(res => res.json())
    .then(res => {
        if (res.success) {
            const enrolled = res.data.count;
            document.getElementById("enrolledCountNumber").textContent = enrolled;

            // Optional: Change color if full
            if (enrolled >= 16) {
                document.getElementById("enrolledCountNumber").style.color = "red";
            } else {
                document.getElementById("enrolledCountNumber").style.color = "green";
            }
        } else {
            document.getElementById("enrolledCountNumber").textContent = "0";
        }
    })
    .catch(() => {
        document.getElementById("enrolledCountNumber").textContent = "0";
    });
}
function updateApplicantCount(batchNo) {
  fetch(ajaxurl, {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: new URLSearchParams({
      action: "get_applicant_count_by_batch",
      batch_no: batchNo
    })
  })
  .then(res => res.json())
  .then(res => {
    if (res.success) {
      document.getElementById("applicantCount").textContent = res.data.count;
    } else {
      document.getElementById("applicantCount").textContent = "0";
    }
  })
  .catch(() => {
    document.getElementById("applicantCount").textContent = "0";
  });
}

const registeredBtn = document.getElementById("viewRegisteredBtn");
const registeredList = document.getElementById("registeredList");

let isRegisteredVisible = false;

registeredBtn.onclick = function () {
    const enrolledList = document.getElementById("enrolledList");
    const applicantsList = document.getElementById("applicantsList");

    if (isRegisteredVisible) {
        // Hide registered, show enrolled
        registeredList.style.display = "none";
        enrolledList.style.display = "block";
        isRegisteredVisible = false;
    } else {
        // Show registered, hide others
        //enrolledList.style.display = "none";
        applicantsList.style.display = "none";

        registeredList.innerHTML = "<p>Loading registered students...</p>";
        registeredList.style.display = "block";
        isRegisteredVisible = true;

        fetch(ajaxurl, {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "action=get_registered_students_not_enrolled&batch_no=" + encodeURIComponent(currentBatchNo)
        })
        .then(res => res.text())
        .then(html => {
            registeredList.innerHTML = html;
        })
        .catch(() => {
            registeredList.innerHTML = "<p style=\'color:red;\'>Failed to load registered students.</p>";
        });
    }

    return false;
};



document.addEventListener("DOMContentLoaded", function () {
    // Close modal
    document.getElementById("closeNewStudentOnlyModal").addEventListener("click", function() {
        document.getElementById("newStudentOnlyModal").style.display = "none";
    });

    // Handle submit
    document.getElementById("newStudentOnlyForm").addEventListener("submit", function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const modal = document.getElementById("newStudentOnlyModal");

        fetch(ajaxurl, {
            method: "POST",
            body: formData
        })
        .then(res => res.json())
        .then(res => {
            if (res.success) {
                alert("🎉 Student registered successfully!\nID: " + res.data.student_id);
                modal.style.display = "none";
            } else {
                alert("❌ Failed to register student.\n" + res.data);
            }
        });
    });
});

// 
document.addEventListener("click", function(e) {
    if (e.target && e.target.id === "openNewStudentOnlyModal") {
        const form = document.getElementById("newStudentOnlyForm");
        form.reset();
        document.getElementById("newStudentOnlyModal").style.display = "block";
    }
});

document.addEventListener("click", function (e) {
  // Handle Edit button click
  if (e.target.closest(".edit-btn")) {
    const row = e.target.closest("tr");
    row.querySelectorAll(".view-field").forEach(el => el.style.display = "none");
    row.querySelectorAll(".edit-field").forEach(el => el.style.display = "inline-block");

    row.querySelector(".edit-btn").style.display = "none";
    row.querySelector(".save-btn").style.display = "inline-block";
  }

  // Handle Save button click
  if (e.target.closest(".save-btn")) {
    const row = e.target.closest("tr");
    const email = row.dataset.email;

    const inputs = row.querySelectorAll(".edit-field");
    const updatedData = {
      full_name: inputs[0].value,
      dob: inputs[1].value,
      gender: inputs[2].value,
      phone: inputs[3].value,
      email: email
    };

    fetch(ajaxurl, {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: new URLSearchParams({
        action: "update_applicant_data",
        ...updatedData
      })
    })
    .then(res => res.json())
    .then(res => {
      if (res.success) {
        alert("✅ Applicant updated successfully!");

        // Update spans
        const spans = row.querySelectorAll(".view-field");
        spans[0].textContent = updatedData.full_name;
        spans[1].textContent = updatedData.dob;
        spans[2].textContent = updatedData.gender;
        spans[4].textContent = updatedData.phone; // student_email doesnt change

        // Toggle views back
        row.querySelectorAll(".view-field").forEach(el => el.style.display = "inline-block");
        row.querySelectorAll(".edit-field").forEach(el => el.style.display = "none");
        row.querySelector(".edit-btn").style.display = "inline-block";
        row.querySelector(".save-btn").style.display = "none";
      } else {
        alert("❌ Update failed: " + res.data);
      }
    })
    .catch(() => {
      alert("❌ Server error.");
    });
  }
  // Handle delete button click
if (e.target.closest(".delete-btn")) {
    const btn = e.target.closest(".delete-btn");
    const row = btn.closest("tr");
    const email = btn.getAttribute("data-email");

    if (!confirm("Are you sure you want to delete this applicant?")) {
        return;
    }

    fetch(ajaxurl, {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams({
            action: "delete_applicant",
            email: email
        })
    })
    .then(res => res.json())
    .then(res => {
        if (res.success) {
            alert("Applicant deleted successfully.");
            row.remove(); // remove row from DOM
        } else {
            alert("❌ Failed to delete applicant.\n" + res.data);
        }
    })
    .catch(() => {
        alert("❌ Server error while deleting.");
    });
}

});
document.addEventListener("click", function(e) {
    if (e.target.classList.contains("enroll-registered-btn")) {
        const button = e.target;
        const email = button.getAttribute("data-email");

        enrollStudent(email, currentBatchNo)
            .then(() => {
                alert("✅ Student enrolled successfully!");
                updateEnrolledCount(currentBatchNo);
                // Optionally remove row or disable button
                const row = button.closest("tr");
                row.remove(); // or button.disabled = true;
            })
            .catch(error => {
                alert("❌ Enrollment failed: " + error);
            });
    }
});

</script>';



    echo '</div>';
}
add_action('wp_ajax_save_student_modal', 'save_student_modal_handler');
function save_student_modal_handler() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'student_registrations';

    $student_name = sanitize_text_field($_POST['student_name']);
    $dob = sanitize_text_field($_POST['dob']);
    $gender = sanitize_text_field($_POST['gender']);
    $email = sanitize_email($_POST['email']);
    $phone = sanitize_text_field($_POST['phone']);

    // Check if student already exists by email or phone
    $existing_student = $wpdb->get_row($wpdb->prepare(
        "SELECT student_id FROM $table_name WHERE email = %s OR phone = %s LIMIT 1",
        $email, $phone
    ), ARRAY_A);

    if ($existing_student) {
        wp_send_json_success([
            'message' => "Student already registered",
            'student_id' => $existing_student['student_id'],
            'status' => 'exists'
        ]);
    }

    //  Generate unique student ID
    $student_id = generate_unique_student_id($wpdb, $table_name);

    //  Insert new student with timestamp
    $inserted = $wpdb->insert($table_name, [
        'student_id'   => $student_id,
        'student_name' => $student_name,
        'dob'          => $dob,
        'gender'       => $gender,
        'email'        => $email,
        'phone'        => $phone,
        'created_at'   => current_time('mysql')
    ]);

    if ($inserted !== false) {
        wp_send_json_success([
            'message' => "Student registered successfully",
            'student_id' => $student_id,
            'status' => 'new'
        ]);
    } else {
        wp_send_json_error("Insert failed: " . $wpdb->last_error);
    }
}



register_activation_hook(__FILE__, 'create_students_course_enrollment_table');
function create_students_course_enrollment_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'students_course_enrollment';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        student_id VARCHAR(12) NOT NULL,
        course_name VARCHAR(255) NOT NULL,
        batch_no VARCHAR(50) DEFAULT NULL,
        enrolled_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY student_course (student_id, course_name)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

register_deactivation_hook(__FILE__, 'delete_students_course_enrollment_table');
function delete_students_course_enrollment_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'students_course_enrollment';
    $wpdb->query("DROP TABLE IF EXISTS $table_name");
}


add_action('wp_ajax_enroll_student_in_course', 'enroll_student_in_course_handler');
function enroll_student_in_course_handler() {
    global $wpdb;

    $email = sanitize_email($_POST['email']);
    $batch_no = sanitize_text_field($_POST['batch_no']);

    // Get student_id from email
    $student = $wpdb->get_row($wpdb->prepare(
        "SELECT student_id FROM {$wpdb->prefix}student_registrations WHERE email = %s",
        $email
    ));

    if (!$student) {
        wp_send_json_error("Student not found");
    }

    $student_id = $student->student_id;

    // Get course_name using batch_no
    $course_name = $wpdb->get_var($wpdb->prepare(
        "SELECT course_name FROM {$wpdb->prefix}custom_batches WHERE batch_no = %s LIMIT 1",
        $batch_no
    ));

    if (!$course_name) {
        wp_send_json_error("Course not found for the batch");
    }

    $enroll_table = $wpdb->prefix . 'students_course_enrollment';

    // Check if already enrolled
    $exists = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $enroll_table WHERE student_id = %s AND course_name = %s",
        $student_id, $course_name
    ));

    if ($exists) {
        wp_send_json_error("Student already enrolled");
    }

    // Insert enrollment record
    $result = $wpdb->insert($enroll_table, [
        'student_id'   => $student_id,
        'course_name'  => $course_name,
        'batch_no'     => $batch_no,
        'enrolled_at'  => current_time('mysql')
    ]);

    if ($result === false) {
        wp_send_json_error("Insert failed: " . $wpdb->last_error);
    }

    // Mark as enrolled in wp_student_enrollments table
    $student_enrollments_table = $wpdb->prefix . 'student_enrollments';
    $wpdb->update(
        $student_enrollments_table,
        ['is_enrolled' => 1],
        ['student_email' => $email]
    );

    wp_send_json_success("Enrolled");
}



add_action('wp_ajax_get_enrolled_students_by_batch', 'get_enrolled_students_by_batch_callback');
function get_enrolled_students_by_batch_callback() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized', 403);
    }

    global $wpdb;
    $batch_no = sanitize_text_field($_POST['batch_no']);

    $enrollments_table = $wpdb->prefix . 'students_course_enrollment';
    $students_table = $wpdb->prefix . 'student_registrations';

    // Join to get full student details
$students = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT r.student_name, r.dob, r.gender, r.email, r.phone, r.student_id
         FROM $enrollments_table e
         INNER JOIN $students_table r 
         ON BINARY e.student_id = BINARY r.student_id
         WHERE e.batch_no = %s",
        $batch_no
    ),
    ARRAY_A
);


    if (empty($students)) {
        echo "<p>No students enrolled in this batch.</p>";
        wp_die();
    }

    echo "<div style='max-height:500px; overflow:auto;'>
        <table style='width:100%; border-collapse:collapse;'>
            <thead>
                <tr>
                    <th style='border-bottom:1px solid #ccc; padding:8px;'>Student ID</th>
                    <th style='border-bottom:1px solid #ccc; padding:8px;'>Student Name</th>
                    <th style='border-bottom:1px solid #ccc; padding:8px;'>DOB</th>
                    <th style='border-bottom:1px solid #ccc; padding:8px;'>Gender</th>
                    <th style='border-bottom:1px solid #ccc; padding:8px;'>Email</th>
                    <th style='border-bottom:1px solid #ccc; padding:8px;'>Phone</th>
                </tr>
            </thead>
            <tbody>";

    foreach ($students as $student) {
        echo "<tr>
                <td style='padding:8px; border-bottom:1px solid #eee;'>" . esc_html($student['student_id']) . "</td>
                <td style='padding:8px; border-bottom:1px solid #eee;'>" . esc_html($student['student_name']) . "</td>
                <td style='padding:8px; border-bottom:1px solid #eee;'>" . esc_html($student['dob']) . "</td>
                <td style='padding:8px; border-bottom:1px solid #eee;'>" . esc_html($student['gender']) . "</td>
                <td style='padding:8px; border-bottom:1px solid #eee;'>" . esc_html($student['email']) . "</td>
                <td style='padding:8px; border-bottom:1px solid #eee;'>" . esc_html($student['phone']) . "</td>
              </tr>";
    }

    echo "</tbody></table></div>";

    wp_die();
}
add_action('wp_ajax_get_enrolled_student_count', 'get_enrolled_student_count_callback');
function get_enrolled_student_count_callback() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized', 403);
    }

    global $wpdb;
    $batch_no = sanitize_text_field($_POST['batch_no']);

    $table = $wpdb->prefix . 'students_course_enrollment';
    $count = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table WHERE batch_no = %s", $batch_no
    ));

    wp_send_json_success(['count' => intval($count)]);
}

add_action('wp_ajax_get_registered_students_not_enrolled', 'get_registered_students_not_enrolled_callback');
function get_registered_students_not_enrolled_callback() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized', 403);
    }

    global $wpdb;
    $batch_no = sanitize_text_field($_POST['batch_no']);
    $reg_table = $wpdb->prefix . 'student_registrations';
    $enroll_table = $wpdb->prefix . 'students_course_enrollment';

    // Get students not enrolled in this batch
    $results = $wpdb->get_results($wpdb->prepare("
        SELECT r.student_id, r.student_name, r.dob, r.gender, r.email, r.phone
        FROM $reg_table r
        WHERE r.student_id NOT IN (
            SELECT student_id FROM $enroll_table WHERE batch_no = %s
        )
    ", $batch_no));

    if (empty($results)) {
        echo "<p>No registered students found.</p>";
        wp_die();
    }

echo "<div style='margin-bottom: 12px; text-align: left;'>
    <button id='openNewStudentOnlyModal' class='button button-primary'>+ New Student</button>
</div>";



    echo "<table style='width:100%; border-collapse: collapse;'>";

    echo "<thead><tr>
        <th style='padding: 8px; border-bottom: 1px solid #ccc;'>Name</th>
        <th style='padding: 8px; border-bottom: 1px solid #ccc;'>DOB</th>
        <th style='padding: 8px; border-bottom: 1px solid #ccc;'>Gender</th>
        <th style='padding: 8px; border-bottom: 1px solid #ccc;'>Email</th>
        <th style='padding: 8px; border-bottom: 1px solid #ccc;'>Phone</th>
        <th style='padding: 8px; border-bottom: 1px solid #ccc;'>Action</th>
    </tr></thead><tbody>";

    foreach ($results as $student) {
        echo "<tr>
            <td style='padding: 6px;'>{$student->student_name}</td>
            <td style='padding: 6px;'>{$student->dob}</td>
            <td style='padding: 6px;'>{$student->gender}</td>
            <td style='padding: 6px;'>{$student->email}</td>
            <td style='padding: 6px;'>{$student->phone}</td>
        <td style='padding: 6px;'>
            <button class='button enroll-registered-btn' data-email='{$student->email}'>Enroll</button>
        </td>
        </tr>";
    }

    echo "</tbody></table>";
    wp_die();
}

// View Applicants Count//
add_action('wp_ajax_get_applicant_count_by_batch', 'get_applicant_count_by_batch_callback');

add_action('wp_ajax_get_applicant_count_by_batch', 'get_applicant_count_by_batch_callback');

function get_applicant_count_by_batch_callback() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized', 403);
    }

    global $wpdb;
    $batch_no = sanitize_text_field($_POST['batch_no']);

    // Get course name for the batch
    $course_name = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT course_name FROM {$wpdb->prefix}custom_batches WHERE batch_no = %s LIMIT 1",
            $batch_no
        )
    );

    if (!$course_name) {
        wp_send_json_success(['count' => 0]);
        wp_die();
    }

    // Count applicants (not enrolled) for that course
    $count = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}student_enrollments 
             WHERE course_name = %s AND is_enrolled = 0",
            $course_name
        )
    );

    wp_send_json_success(['count' => intval($count)]);
}

add_action('wp_ajax_update_applicant_data', 'update_applicant_data_callback');

function update_applicant_data_callback() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized', 403);
    }

    global $wpdb;
    $table = $wpdb->prefix . 'student_enrollments';

    $email     = sanitize_email($_POST['email']);
    $full_name = sanitize_text_field($_POST['full_name']);
    $dob       = sanitize_text_field($_POST['dob']);
    $gender    = sanitize_text_field($_POST['gender']);
    $phone     = sanitize_text_field($_POST['phone']);

    $updated = $wpdb->update(
        $table,
        [
            'full_name'    => $full_name,
            'dob'          => $dob,
            'gender'       => $gender,
            'parent_phone' => $phone
        ],
        ['student_email' => $email]
    );

    if ($updated !== false) {
        wp_send_json_success("Updated");
    } else {
        wp_send_json_error("Database update failed");
    }
}
add_action('wp_ajax_delete_applicant', 'delete_applicant_callback');

function delete_applicant_callback() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized', 403);
    }

    global $wpdb;
    $email = sanitize_email($_POST['email']);

    $table = $wpdb->prefix . 'student_enrollments';

    $deleted = $wpdb->delete($table, ['student_email' => $email]);

    if ($deleted !== false) {
        wp_send_json_success("Applicant deleted successfully");
    } else {
        wp_send_json_error("Failed to delete applicant");
    }
}




register_activation_hook(__FILE__, 'create_custom_batch_table');
register_deactivation_hook(__FILE__, 'drop_custom_batch_table');

function create_custom_batch_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'custom_batches';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        batch_id INT NOT NULL AUTO_INCREMENT,
        instructor_name VARCHAR(255),
        course_name VARCHAR(255),
        start_date DATE,
        end_date DATE,
        start_time TIME,
        end_time TIME,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (batch_id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

function drop_custom_batch_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'custom_batches';
    $wpdb->query("DROP TABLE IF EXISTS $table_name");
}



// CREATE NEW BATCH USING CALENDER //
function create_new_batch_page() {
    global $wpdb;
    $selected_course = isset($_GET['course_name']) ? sanitize_text_field($_GET['course_name']) : '';
    $course_fee = null;

if ($selected_course) {
    $course_fee = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT course_fee FROM {$wpdb->prefix}course_enrollments WHERE course_name = %s LIMIT 1",
            $selected_course
        )
    );
}

    $instructors = $wpdb->get_results("SELECT instructor_name, subject AS course_name FROM {$wpdb->prefix}instructors");

    ?>
    <style>
        .batch-form {
            max-width: 450px;
            background: #fff;
            padding: 25px 30px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgb(0 0 0 / 0.1);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
            margin: 20px auto;
        }
        .batch-form h1 {
            font-weight: 600;
            margin-bottom: 20px;
            font-size: 1.8rem;
            color: #222;
            text-align: center;
        }
        .batch-form label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            font-size: 0.9rem;
        }
        .batch-form select,
        .batch-form input[type="text"] {
            width: 100%;
            padding: 10px 14px;
            font-size: 1rem;
            border: 1.8px solid #ccc;
            border-radius: 6px;
            transition: border-color 0.3s ease;
        }
        .batch-form select:focus,
        .batch-form input[type="text"]:focus {
            border-color: #0073aa;
            outline: none;
        }
        .batch-form .form-group {
            margin-bottom: 20px;
        }
        .batch-form button {
            background-color: #0073aa;
            color: #fff;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            font-size: 1rem;
            transition: background-color 0.3s ease;
            width: 100%;
        }
        .batch-form button:hover {
            background-color: #005a87;
        }
        .batch-form #saveBatchButton {
            background-color: #28a745;
            margin-top: 10px;
            display: none;
        }
        .batch-form #saveBatchButton:hover {
            background-color: #1e7e34;
        }
        .batch-form #selectedDateTime {
            font-weight: 600;
            color: #555;
            display: inline-block;
            margin-left: 8px;
        }
        .batch-form .button-container {
            margin-top: 10px;
        }

    </style>

    <div class="batch-form">
        <h1>Create New Batch</h1>

<div class="form-group">
    <label for="main_instructor_name">Instructor Name:</label>
    <select id="main_instructor_name" name="main_instructor_name">
        <option value="">-- Select Instructor --</option>
        <?php foreach ($instructors as $instructor): ?>
            <option value="<?= esc_attr($instructor->instructor_name) ?>" data-course="<?= esc_attr($instructor->course_name) ?>">
                <?= esc_html($instructor->instructor_name) ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>

<div class="form-group">
    <label for="main_course_name">Course:</label>
    <input 
        type="text" 
        id="main_course_name" 
        name="main_course_name" 
        readonly 
        value="<?php echo esc_attr($selected_course); ?>" 
        <?php echo $selected_course ? 'data-from-url="true"' : ''; ?> 
    />
</div>


<?php if ($selected_course && $course_fee !== null): ?>
    <div class="form-group">
        <label>Course Fee:</label>
        <input type="text" readonly value="Rs. <?php echo esc_html(number_format($course_fee, 2)); ?>" />
    </div>
<?php endif; ?>


        <div class="button-container">
            <button id="openCalendarModal" type="button">Select Date and Time</button>
        </div>

        <input type="hidden" id="hidden_start_date" name="hidden_start_date" />
        <input type="hidden" id="hidden_end_date" name="hidden_end_date" />
        <input type="hidden" id="hidden_start_time" name="hidden_start_time" />
        <input type="hidden" id="hidden_end_time" name="hidden_end_time" />

        <p>Selected Date and Time: <span id="selectedDateTime">None</span></p>

        <button id="saveBatchButton" type="button">Save Batch</button>
    </div>
    <?php
    ?>

    <div id="calendarModal" class="modal" style="display:none;">
        <div class="modal-content" style="background:#fff; width: 450px; margin: 50px auto; padding: 20px; border-radius: 8px; max-height: 80vh; overflow-y:auto;">
            <span id="closeCalendarModal" style="cursor:pointer; float:right; font-size: 28px;">&times;</span>
            <h2>Book Time Slot For New Batch</h2>
            <form id="bookingForm">
                <label for="instructor_name">Instructor Name:</label>
                <input type="text" id="instructor_name" name="instructor_name" readonly style="width: 100%;" />

                <label for="subject_name">Subject:</label>
                <input type="text" id="course_name" name="course_name" readonly style="width: 100%; margin-bottom: 15px;" />

                <label for="start_date">Start Date:</label>
                <input type="date" id="start_date" name="start_date" required style="width: 100%; margin-bottom: 15px;">

                <label for="end_date">End Date:</label>
                <input type="date" id="end_date" name="end_date" required style="width: 100%; margin-bottom: 15px;">

                <div id="availableSlotsContainer" style="margin-bottom: 15px;">
                    <label>Available Time Slots:</label><br>
                    <div id="availableSlots"></div>
                </div>

                <div id="selected_time_container" style="margin-bottom: 15px; display: none;">
                    <div style="display: flex; align-items: center; gap: 20px;">
                        <div>
                            <label for="start_time">Start Time:</label><br>
                            <input type="time" id="start_time" name="start_time" style="width: 100%;">
                        </div>
                        <div>
                            <label for="end_time">End Time:</label><br>
                            <input type="time" id="end_time" name="end_time" style="width: 100%;">
                        </div>
                    </div>
                </div>

                <button type="button" id="ok" style="background-color: #21759b; color: white; padding: 10px 20px; border:none; border-radius:5px; cursor:pointer;">OK</button>
                <button type="button" id="cancelButton" style="background-color: gray; color: white; padding: 10px 20px; border:none; border-radius:5px; cursor:pointer;">Cancel</button>
            </form>
        </div>
    </div>

    <style>
    .modal {
        position: fixed !important;
        inset: 0;
        width: 100vw !important;
        height: 100vh !important;
        background-color: rgba(0,0,0,0.5);
        display: none;
        z-index: 9999;
        overflow: hidden;
    }
    .modal-content {
        background: white;
        box-sizing: border-box;
    }
    </style>
<script>
const ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
</script>

    <script>
document.addEventListener("DOMContentLoaded", function () {
    const mainInstructorSelect = document.getElementById("main_instructor_name");
    const mainCourseInput = document.getElementById("main_course_name");
    const openModalBtn = document.getElementById("openCalendarModal");
    const modal = document.getElementById("calendarModal");
    const closeModalBtn = document.getElementById("closeCalendarModal");
    const cancelButton = document.getElementById("cancelButton");

    const modalInstructorInput = document.getElementById("instructor_name");
    const modalCourseInput = document.getElementById("course_name");

    const startDateInput = document.getElementById("start_date");
    const endDateInput = document.getElementById("end_date");
    const availableSlotsDiv = document.getElementById("availableSlots");
    const selectedTimeContainer = document.getElementById("selected_time_container");
    const startTimeInput = document.getElementById("start_time");
    const endTimeInput = document.getElementById("end_time");

    // When instructor changes, update course input unless course was set from URL
    mainInstructorSelect.addEventListener("change", () => {
        const selected = mainInstructorSelect.options[mainInstructorSelect.selectedIndex];
        if (!mainCourseInput.dataset.fromUrl) {
            mainCourseInput.value = selected ? selected.getAttribute("data-course") || "" : "";
        }
    });

     // OPEN modal on button click
     openModalBtn.addEventListener("click", () => {
         // Validate instructor is selected
         const selectedInstructor = mainInstructorSelect.value;
         if (!selectedInstructor) {
             alert("Please select an instructor before selecting date and time.");
             return;
         }
     
         // Set modal inputs based on main form inputs
         modalInstructorInput.value = selectedInstructor;
         modalCourseInput.value = mainCourseInput.value || "";
     
         // Set start date to today and readonly
         const today = new Date().toISOString().split('T')[0];
         startDateInput.value = today;
         //startDateInput.readOnly = true;
     
         // Clear/reset other modal fields
         endDateInput.value = "";
         availableSlotsDiv.innerHTML = "";
         selectedTimeContainer.style.display = "none";
         startTimeInput.value = "";
         endTimeInput.value = "";
     
         // Show the modal
         modal.style.display = "block";
         document.body.classList.add("modal-open");
     });


    // CLOSE modal helpers
    function closeModal() {
        modal.style.display = "none";
        document.body.classList.remove("modal-open");
        document.getElementById("bookingForm").reset();
        availableSlotsDiv.innerHTML = "";
        selectedTimeContainer.style.display = "none";
        startTimeInput.value = "";
        endTimeInput.value = "";
    }
    closeModalBtn.addEventListener("click", closeModal);
    cancelButton.addEventListener("click", closeModal);
    window.addEventListener("click", (event) => {
        if (event.target === modal) closeModal();
    });

    // OK button: validate inputs, set hidden fields, update display, close modal
    document.getElementById("ok").addEventListener("click", () => {
        const startDate = startDateInput.value;
        const endDate = endDateInput.value;
        const startTime = startTimeInput.value;
        const endTime = endTimeInput.value;
        const instructor = modalInstructorInput.value;
        const course = modalCourseInput.value;

        if (!startDate || !endDate || !startTime || !endTime || !instructor || !course) {
            alert("Please fill in all required fields.");
            return;
        }

        // Save values to hidden inputs in main form
        document.getElementById("hidden_start_date").value = startDate;
        document.getElementById("hidden_end_date").value = endDate;
        document.getElementById("hidden_start_time").value = startTime;
        document.getElementById("hidden_end_time").value = endTime;

        // Show selected date and time summary
        document.getElementById("selectedDateTime").textContent =
            `From ${startDate} to ${endDate}, ${startTime} - ${endTime}`;

        // Show the Save button
        document.getElementById("saveBatchButton").style.display = "inline-block";

        // Close modal
        closeModal();
    });

    // Fetch available slots based on date range
    function fetchAvailableSlots() {
        const start = startDateInput.value;
        const end = endDateInput.value;

        if (!start || !end) {
            availableSlotsDiv.innerHTML = "";
            selectedTimeContainer.style.display = "none";
            return;
        }

        if (start > end) {
            availableSlotsDiv.innerHTML = "<p style='color:red;'>End date must be after start date.</p>";
            selectedTimeContainer.style.display = "none";
            return;
        }

        availableSlotsDiv.innerHTML = "Loading available slots...";

        fetch(`${ajaxurl}?action=get_available_class_slots&start_date=${start}&end_date=${end}`)
            .then(res => res.json())
            .then(data => {
                if (!data.slots || data.slots.length === 0) {
                    availableSlotsDiv.innerHTML = "<p>No available slots found for the selected dates.</p>";
                    selectedTimeContainer.style.display = "none";
                    return;
                }

                availableSlotsDiv.innerHTML = "";

                data.slots.forEach(slot => {
                    const checkbox = document.createElement("input");
                    checkbox.type = "checkbox";
                    checkbox.name = "time_slots[]";
                    checkbox.value = slot;
                    checkbox.id = "slot_" + slot.replace(/[^a-zA-Z0-9]/g, "_");

                    const label = document.createElement("label");
                    label.htmlFor = checkbox.id;
                    label.textContent = slot;

                    const container = document.createElement("div");
                    container.appendChild(checkbox);
                    container.appendChild(label);

                    availableSlotsDiv.appendChild(container);

                    checkbox.addEventListener("change", updateStartEndTime);
                });

                updateStartEndTime();
            })
            .catch(() => {
                availableSlotsDiv.innerHTML = "<p>Error loading available slots. Please try again.</p>";
                selectedTimeContainer.style.display = "none";
            });
    }

    // Update start/end time inputs based on selected checkboxes
    function updateStartEndTime() {
        const checkboxes = Array.from(document.querySelectorAll('input[name="time_slots[]"]'));
        const checked = checkboxes.filter(cb => cb.checked);

        if (checked.length === 0) {
            selectedTimeContainer.style.display = "none";
            startTimeInput.value = "";
            endTimeInput.value = "";
            return;
        }

        let allSlots = checkboxes.map(cb => {
            const [start, end] = cb.value.split(" - ").map(t => t.trim());
            return { start, end, value: cb.value, checkbox: cb };
        });

        allSlots.sort((a, b) => a.start.localeCompare(b.start));

        const selectedIndices = checked.map(cb => allSlots.findIndex(slot => slot.value === cb.value));
        const minIndex = Math.min(...selectedIndices);
        const maxIndex = Math.max(...selectedIndices);

        // Check all slots between min and max to make a continuous range
        for (let i = minIndex; i <= maxIndex; i++) {
            if (!allSlots[i].checkbox.checked) {
                allSlots[i].checkbox.checked = true;
            }
        }

        const selectedSlots = allSlots.slice(minIndex, maxIndex + 1);
        startTimeInput.value = selectedSlots[0].start;
        endTimeInput.value = selectedSlots[selectedSlots.length - 1].end;
        selectedTimeContainer.style.display = "block";
    }

    // Trigger fetching slots when dates change
    startDateInput.addEventListener("change", fetchAvailableSlots);
    endDateInput.addEventListener("change", fetchAvailableSlots);

    // Save batch button AJAX submission
    document.getElementById("saveBatchButton").addEventListener("click", () => {
        const instructor = mainInstructorSelect.value;
        const course = mainCourseInput.value;
        const startDate = document.getElementById("hidden_start_date").value;
        const endDate = document.getElementById("hidden_end_date").value;
        const startTime = document.getElementById("hidden_start_time").value;
        const endTime = document.getElementById("hidden_end_time").value;

        if (!instructor || !course || !startDate || !endDate || !startTime || !endTime) {
            alert("Missing values. Please complete the form first.");
            return;
        }

        const formData = new FormData();
        formData.append("action", "save_batch_data");
        formData.append("instructor_name", instructor);
        formData.append("course_name", course);
        formData.append("start_date", startDate);
        formData.append("end_date", endDate);
        formData.append("start_time", startTime);
        formData.append("end_time", endTime);

        fetch(ajaxurl, {
            method: "POST",
            body: formData
        })
        .then(res => res.json())
        .then(result => {
            if (result.success) {
                alert("Batch saved successfully!");
                document.getElementById("saveBatchButton").style.display = "none";
            } else {
                alert("Failed to save batch: " + (result.data || "Unknown error."));
            }
        })
        .catch(() => alert("AJAX error occurred."));
    });
});

    </script>
    <?php




}
add_action('wp_ajax_save_batch_data', 'handle_save_batch_data');

function handle_save_batch_data() { 
    global $wpdb;
    $table_name = $wpdb->prefix . 'custom_batches';

    // Sanitize inputs
    $instructor_name = sanitize_text_field($_POST['instructor_name']);
    $course_name     = sanitize_text_field($_POST['course_name']);
    $start_date      = sanitize_text_field($_POST['start_date']);
    $end_date        = sanitize_text_field($_POST['end_date']);
    $start_time      = sanitize_text_field($_POST['start_time']);
    $end_time        = sanitize_text_field($_POST['end_time']);

    if (!$instructor_name || !$course_name || !$start_date || !$end_date || !$start_time || !$end_time) {
        wp_send_json_error("Missing required fields.");
    }

    $start_timestamp = strtotime($start_date);
    $end_timestamp   = strtotime($end_date);

    if ($start_timestamp > $end_timestamp) {
        wp_send_json_error("Start date cannot be after end date.");
    }

    // Determine the weekday of start_date (0=Sunday ... 6=Saturday)
    $start_weekday = date('w', $start_timestamp);

    // Generate a shared batch_no
    $batch_no = 'BATCH-' . date('Ymd');

    $current_timestamp = $start_timestamp;
    $inserted_count = 0;

    while ($current_timestamp <= $end_timestamp) {
        $current_date = date('Y-m-d', $current_timestamp);

        // Insert batch for current date
        $result = $wpdb->insert(
            $table_name,
            [
                'batch_no'        => $batch_no,
                'instructor_name' => $instructor_name,
                'course_name'     => $course_name,
                'start_date'      => $current_date,
                'end_date'        => $current_date,
                'start_time'      => $start_time,
                'end_time'        => $end_time,
            ],
            ['%s', '%s', '%s', '%s', '%s', '%s', '%s']
        );

        if (!$result) {
            wp_send_json_error("Failed to insert batch for date: $current_date");
            return;
        }

        $inserted_count++;
        $current_timestamp = strtotime("+1 week", $current_timestamp);
    }

    if ($inserted_count > 0) {
        wp_send_json_success("Batch saved successfully for $inserted_count occurrences. Batch No: $batch_no");
    } else {
        wp_send_json_error("No batches saved.");
    }
}



//registration form on frontend//









