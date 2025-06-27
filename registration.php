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
        'student-registration', // Page title (shown in <title> and heading)
        'Student Registration',           // Menu title (shown in sidebar menu)
        'Student Registration',  
        'edit_pages',
        'student-registration',
        'render_student_registration_admin_page',
);
    add_submenu_page(
        'student-registration',         // parent slug
        'Students List',                // page title
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
        'Course List',
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
        'List of Instructors',
        'List of Instructors',
        'manage_options',
        'instructors-list',
        'render_instructors_list_page'
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
 ?>
 <style>
    .wp-list-table th, .wp-list-table td {
        text-align: center;
        vertical-align: middle;
    }
</style>

     <?php
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

    // Handle update form
    if (isset($_POST['update_course'])) {
        $course_db_id = intval($_POST['course_db_id']);
        $course_id = sanitize_text_field($_POST['course_id']);
        $course_name = sanitize_text_field($_POST['course_name']);
        $modules_input = sanitize_text_field($_POST['modules'] ?? '');
        $modules = array_map('trim', explode(',', $modules_input));
        $modules_json = json_encode(array_values(array_filter($modules)));

        $wpdb->update(
            $wpdb->prefix . 'course_enrollments',
            [
                'course_id' => $course_id,
                'course_name' => $course_name,
                'modules' => $modules_json
            ],
            ['id' => $course_db_id]
        );

        echo '<div class="notice notice-success is-dismissible"><p>✅ Course updated successfully.</p></div>';
    }

    if ($courses) {
        $editing_id = isset($_GET['edit']) ? intval($_GET['edit']) : 0;

        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr><th>Course ID</th><th>Course Name</th><th>Modules</th><th>Actions</th></tr></thead><tbody>';

        foreach ($courses as $course) {
            $is_editing = ($editing_id === intval($course->id));
            $modules = json_decode($course->modules, true);
            $modules = is_array($modules) ? $modules : [];

            echo '<tr>';
            if ($is_editing) {
                echo '<form method="post">';
                echo '<input type="hidden" name="course_db_id" value="' . esc_attr($course->id) . '">';
                echo '<td><input type="text" name="course_id" value="' . esc_attr($course->course_id) . '" required></td>';
                echo '<td><input type="text" name="course_name" value="' . esc_attr($course->course_name) . '" required></td>';
                echo '<td><input type="text" name="modules" value="' . esc_attr(implode(', ', $modules)) . '" style="width:100%;"></td>';
                echo '<td><input type="submit" name="update_course" class="button button-primary" value="Save">';
                echo ' <a href="?page=course-selection-list" class="button">Cancel</a></td>';
                echo '</form>';
            } else {
                echo '<td>' . esc_html($course->course_id) . '</td>';
                echo '<td>' . esc_html($course->course_name) . '</td>';
                echo '<td>' . esc_html(implode(', ', $modules)) . '</td>';
                echo '<td>';
                echo '<a href="?page=course-selection-list&edit=' . intval($course->id) . '" class="button button-primary" style="margin-right:5px;">Edit</a>';
                echo '<a href="admin.php?page=course-batches&course_id=' . intval($course->id) . '" class="button button-primary">Batch</a>';
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

        $wpdb->insert(
            $wpdb->prefix . 'course_enrollments',
            [
                'course_id' => $course_id,
                'course_name' => $course_name,
                'modules' => $modules_json
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


function render_course_batches_page() {
    global $wpdb;

    echo '<div class="wrap"><h1>Ongoing Batches</h1>';

    $course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;

    if ($course_id) {
        echo '<h2>Batch List</h2>';
    }

    $calendar_table = $wpdb->prefix . 'booking_calendar';
    $query = $course_id
        ? $wpdb->prepare("SELECT start_date, end_date, start_time, end_time FROM $calendar_table WHERE course_id = %d GROUP BY start_date, end_date, start_time, end_time ORDER BY start_date ASC", $course_id)
        : "SELECT start_date, end_date, start_time, end_time FROM $calendar_table GROUP BY start_date, end_date, start_time, end_time ORDER BY start_date DESC";

    $batches = $wpdb->get_results($query);

    // Modern table CSS
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

    /* Alternating row colors */
    table.wp-list-table tbody tr:nth-child(even) {
        background-color: #f7f7f7; /* Light grey */
    }

    table.wp-list-table tbody tr:nth-child(odd) {
        background-color: #ffffff; /* White */
    }

    table.wp-list-table tbody tr:hover {
        background-color: #f0f8ff;
    }
    /* Add transition and hover effect for clickable rows */
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
    cursor: pointer;
}



</style>';


    if ($batches) {
echo '<table class="wp-list-table widefat fixed striped">';
echo '<thead><tr><th>No.</th><th>Start Date</th><th>End Date</th><th>Start Time</th><th>End Time</th><th>Instructor Name</th><th>Status</th></tr></thead>';
echo '<tbody>';

$enabled_batches = [];
$disabled_batches = [];
$current_date = date('Y-m-d');

foreach ($batches as $batch) {
    $is_expired = ($batch->end_date < $current_date);
    if ($is_expired) {
        $disabled_batches[] = $batch;
    } else {
        $enabled_batches[] = $batch;
    }
}

$row_number = 1;

// Merge enabled first, then disabled
foreach (array_merge($enabled_batches, $disabled_batches) as $batch) {
    $is_expired = ($batch->end_date < $current_date);
    $row_class = $is_expired ? 'disabled-row' : '';
    $status_button = $is_expired
        ? '<button class="status-button disabled" disabled>Disabled</button>'
        : '<button class="status-button enabled">Enabled</button>';
    $instructor = !empty($batch->instructor_name) ? esc_html($batch->instructor_name) : '<em style="color:#888;">Not Assigned</em>';

    echo '<tr class="' . $row_class . '" onclick="window.location.href=\'#\'">';
    echo '<td>' . $row_number++ . '</td>';
    echo '<td>' . esc_html($batch->start_date) . '</td>';
    echo '<td>' . esc_html($batch->end_date) . '</td>';
    echo '<td>' . esc_html($batch->start_time) . '</td>';
    echo '<td>' . esc_html($batch->end_time) . '</td>';
    echo '<td>' . $instructor . '</td>';
    echo '<td>' . $status_button . '</td>';
    echo '</tr>';
}


echo '</tbody></table>';

    } else {
        echo '<p>No batches found.</p>';
    }

    echo '</div>';
}









