<?php

//* Protection Check
if(!defined('ABSPATH')){
    die('You cannot be here');
 }

add_shortcode( 'contact', 'show_contact_form' );
add_action('rest_api_init', 'create_rest_endpoint');
add_action('init', 'create_submissions_page');
add_action('add_meta_boxes', 'create_meta_box');
add_filter('manage_submission_posts_columns', 'custom_submission_columns');
add_action('manage_submission_posts_custom_column', 'fill_submission_columns', 10, 2);
add_action('admin_init', 'setup_search');
add_action('wp_enqueue_scripts', "enqueue_custom_scripts");
function enqueue_custom_scripts(){
    wp_enqueue_style( 'contact-form-plugin', MY_PLUGIN_URL . '/dist/css/main.css');
}


//*CREATE SUBMISSION PAGE (CUSTOM POST)*//
function create_submissions_page(){
    $args = [
        'public' => true,
        'has_archive' => true,
        'menu_icon' => 'dashicons-analytics',
        'labels' => [
            'name' => 'Submissions',
            'singular_name' => 'Submission',
            'edit_item' => 'View Submission'
        ],
        'supports' => false,
        'capability_type' => 'post',
        'capabilities' => ['create_posts' => false],
        'map_meta_cap' => true,
        'menu_position' => 30,
        'publicly_queryable' => false
    ];

    register_post_type('Submission', $args); 
}

function create_meta_box(){
    add_meta_box( 'custom_contact_form', 'Submission', 'display_submissions', 'Submission');
}

function display_submissions(){
    $postmetas = get_post_meta(get_the_ID());

    // Metadata displayed
    echo '<ul>';
        echo '<li><strong>Name</strong>:<br/>' . esc_html(get_post_meta(get_the_ID(), 'name', true)) . '</li>';
        echo '<li><strong>Email</strong>:<br/>' . esc_html(get_post_meta(get_the_ID(), 'email', true)) . '</li>';
        echo '<li><strong>Phone Number</strong>:<br/>' . esc_html(get_post_meta(get_the_ID(), 'phone', true)). '</li>';
        echo '<br/><li><strong>Message</strong>:<br/>' . esc_html(get_post_meta(get_the_ID(), 'message', true)) . '</li>';
    echo '</ul>';
}

//* CREATE COLUMNS IN SUBMISSION PAGE *//
function custom_submission_columns($columns){
    $columns = [
        'cb' => $columns['cb'],
        'name' =>  __('Name', 'contact-plugin'),
        'email' => __('Email', 'contact-plugin'),
        'phone' => __('Phone Number', 'contact-plugin'),
        'message' => __('Message', 'contact-plugin'),
    ];

    return $columns;
}

//* DATA -> COLUMNS IN SUBMISSION PAGE *//
function fill_submission_columns($column, $post_id){
    switch($column){
        case 'name':
            echo esc_html(get_post_meta($post_id, 'name', true));
            break;
        case 'email':
            echo esc_html(get_post_meta($post_id, 'email', true));
            break;
        case 'phone':
            echo esc_html(get_post_meta($post_id, 'phone', true));
            break;
        case 'message':
            echo esc_html(get_post_meta($post_id, 'message', true));
            break;

    }
}

function setup_search(){
    // only apply filter to submissions page
    global $typenow;
    if($typenow === 'submission'){
        add_filter('posts_search', 'submission_search_override', 10, 2);
    }
}

function submission_search_override($search, $query){
    // Override the submissions page search to include custom meta data

    global $wpdb;

    if ($query->is_main_query() && !empty($query->query['s'])) {
          $sql    = "
            or exists (
                select * from {$wpdb->postmeta} where post_id={$wpdb->posts}.ID
                and meta_key in ('name','email','phone')
                and meta_value like %s
            )
        ";
          $like   = '%' . $wpdb->esc_like($query->query['s']) . '%';
          $search = preg_replace(
                "#\({$wpdb->posts}.post_title LIKE [^)]+\)\K#",
                $wpdb->prepare($sql, $like),
                $search
          );
    }

    return $search;
}





//* PLUG-IN PAGE *//
function show_contact_form(){
    include MY_PLUGIN_PATH . '/includes/template/contact-form.php';
}

function create_rest_endpoint(){
    register_rest_route( 'v1/contact-form', 'submit', array(

        'methods' => 'POST',
        'callback' => 'handle_inquiry'

    ));
}



//* RUNS WHEN FORM SUBMITTED SUCCESSFULLY *//
function handle_inquiry($data){
    $params = $data->get_params();

    //* Set the fields from the form
    $field_name = sanitize_text_field( $params['name'] );
    $field_email = sanitize_text_field( $params['email'] );
    $field_phone = sanitize_text_field( $params['phone'] );
    $field_message = sanitize_text_field( $params['message'] );
    
    if(!wp_verify_nonce($params['_wpnonce'], 'wp_rest')){
        return new WP_Rest_Response('Message not sent', 422);
    }

    unset($params['_wpnonce']);
    unset($params['_wp_http_referer']);

    //* EMAIL DATA *//
    $headers = [];
    $admin_email = get_bloginfo('admin_email');
    $admin_name = get_bloginfo('name');

    $recipient_email = get_plugin_options('contact_plugin_recipients');

    // If no email is set, send to the administration email
    if (!$recipient_email){
        $recipeient_email = $admin_email;
    }

    $subject = "New inquiry from {$field_name}";

    $headers[] = "From: {$admin_name} <{$admin_email}>"; 
    $headers[] = "Reply-to: {$field_name} <{$field_email}>";
    $headers[] = "Content-Type: text/html";

    $message = '';
    $message .= "<h3>Message has been sent from {$field_name}</h3>";

    //* FOR DATA -> SUBMISSION POST *//
    $postarr = [
        'post_title' => $field_name,
        'post_type' => 'Submission',
        'post_status' => 'publish'
    ];

    $post_id = wp_insert_post($postarr);


    //* FOR EMAIL CONTENT & saving into db*//
    foreach($params as $label => $value){
        //* Protection
        switch ($label){
            case 'message':
                $value = sanitize_textarea_field($value);
                // Specific formatting for message (adds extra space above)
                $message .= '<br/>';
                break;
            case 'email':
                $value = sanitize_email( $value );
                break;
            default:
                $value = sanitize_text_field( $value );
        }
        add_post_meta( $post_id, sanitize_text_field($label), $value);
        $message .= '<strong>' . sanitize_text_field(ucfirst($label)) . '</strong>: ' . $value . '<br/>';
    }

    // actually sends the email
    wp_mail( $recipient_email, $subject, $message, $headers);

    // Set confirmation email (from plug in menu page)
    $confirmation_message = 'The message was sent successfully!';

    if(get_plugin_options('contact_plugin_message')){
        $confirmation_message = get_plugin_options('contact_plugin_message');
        $confirmation_message = str_replace('{name}', $field_name, $confirmation_message);
    } 

    return new WP_Rest_Response($confirmation_message, 200);
}
