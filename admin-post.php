<?php

// Handle adding a publication category
add_action('admin_post_add_publication_category', 'handle_add_publication_category');
function handle_add_publication_category() {
    if (isset($_POST['publication_category_name'])) {
        $category_name = sanitize_text_field($_POST['publication_category_name']);
        $term = wp_insert_term($category_name, 'publication_category');

        if (!is_wp_error($term)) {
            wp_redirect(admin_url('admin.php?page=publications'));
            exit;
        }
    }

    // Redirect back to the publications page if there was an error or the category name is not set
    wp_redirect(admin_url('admin.php?page=publications'));
    exit;
}

// Handle adding a publication
add_action('admin_post_add_publication', 'handle_add_publication');
function handle_add_publication() {
    if (isset($_POST['publication_title'])) {
        $publication_title = sanitize_text_field($_POST['publication_title']);
        $publication_content = wp_kses_post($_POST['publication_content']);

        $new_post = array(
            'post_title' => $publication_title,
            'post_content' => $publication_content,
            'post_type' => 'publication',
            'post_status' => 'publish',
        );

        $post_id = wp_insert_post($new_post);

        if (!is_wp_error($post_id)) {
            wp_redirect(admin_url('admin.php?page=publications'));
            exit;
        }
    }

    // Redirect back to the publications page if there was an error or the publication title is not set
    wp_redirect(admin_url('admin.php?page=publications'));
    exit;
}
