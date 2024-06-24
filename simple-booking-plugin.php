<?php
/*
Plugin Name: Simple Booking Plugin
Description: A simple booking plugin with date and time slots.
Version: 1.0
Author: Your Name
*/

// Enqueue scripts and styles
function sbp_enqueue_scripts() {
    wp_enqueue_style('sbp-style', plugin_dir_url(__FILE__) . 'assets/style.css');
    wp_enqueue_script('sbp-script', plugin_dir_url(__FILE__) . 'assets/script.js', array('jquery'), null, true);

    wp_localize_script('sbp-script', 'sbp_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('sbp_nonce')
    ));
}
add_action('wp_enqueue_scripts', 'sbp_enqueue_scripts');

// Create custom table on plugin activation
function sbp_create_custom_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'appointments';
    
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        date date NOT NULL,
        time varchar(10) NOT NULL,
        name varchar(50) NOT NULL,
        email varchar(50) NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'sbp_create_custom_table');

// Shortcode for booking form
function sbp_booking_form() {
    ob_start();
    ?>
    <div class="container">
        <h1>Appointment Booking</h1>
        <form id="booking-form">
            <label for="booking-date">Date:</label>
            <input type="date" id="booking-date" name="booking-date" required>

            <div id="time-slots-container">
                <!-- Time slots will be loaded here -->
            </div>

            <label for="name">Name:</label>
            <input type="text" id="name" name="name" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>

            <button type="submit">Book</button>
        </form>

        <div id="booking-result"></div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('sbp_booking_form', 'sbp_booking_form');

// Handle booking form submission
function sbp_handle_booking() {
    check_ajax_referer('sbp_nonce', 'nonce');

    global $wpdb;
    $table_name = $wpdb->prefix . 'appointments';

    $date = sanitize_text_field($_POST['date']);
    $time = sanitize_text_field($_POST['time']);
    $name = sanitize_text_field($_POST['name']);
    $email = sanitize_email($_POST['email']);

    $result = $wpdb->insert(
        $table_name,
        array(
            'date' => $date,
            'time' => $time,
            'name' => $name,
            'email' => $email,
        ),
        array(
            '%s',
            '%s',
            '%s',
            '%s',
        )
    );

    if ($result) {
        wp_send_json_success('Booking successful! You will receive a confirmation email shortly.');
    } else {
        wp_send_json_error('Booking failed. Please try again.');
    }
}
add_action('wp_ajax_sbp_handle_booking', 'sbp_handle_booking');
add_action('wp_ajax_nopriv_sbp_handle_booking', 'sbp_handle_booking');
