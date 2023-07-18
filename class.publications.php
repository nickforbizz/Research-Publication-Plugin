<?php
/*
Plugin Name: Scientific Publications
Plugin URI: http://your-plugin-uri.com
Description: Allows users to manage scientific publications.
Version: 1.0.0
Author: Nicholas Waruingi
Author URI: http://your-website.com
License: GPL2
*/

class ScientificPublicationsPlugin {
    // Constructor
    public function __construct() {
        // Add any initialization code here
        add_action('init', array($this, 'register_publication_categories'));
        add_action('init', array($this, 'register_publication_post_type'));
        add_shortcode('publications', array($this, 'render_publications_shortcode'));
        add_action('admin_menu', array($this, 'add_publications_menu'));
        add_action('admin_post_upload_publications', array($this, 'handle_upload_publications'));
    }

    // Method to register publication categories
    public function register_publication_categories() {
        $labels = array(
            'name' => 'Publication Categories',
            'singular_name' => 'Publication Category',
            'search_items' => 'Search Publication Categories',
            'all_items' => 'All Publication Categories',
            'edit_item' => 'Edit Publication Category',
            'update_item' => 'Update Publication Category',
            'add_new_item' => 'Add New Publication Category',
            'new_item_name' => 'New Publication Category Name',
            'menu_name' => 'Publication Categories',
        );

        $args = array(
            'hierarchical' => true,
            'labels' => $labels,
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => array('slug' => 'publication-category'),
        );

        register_taxonomy('publication_category', 'publication', $args);
    }

    // Method to register publication post type
    public function register_publication_post_type() {
        $labels = array(
            'name' => 'Publications',
            'singular_name' => 'Publication',
            'add_new' => 'Add New',
            'add_new_item' => 'Add New Publication',
            'edit_item' => 'Edit Publication',
            'new_item' => 'New Publication',
            'view_item' => 'View Publication',
            'search_items' => 'Search Publications',
            'not_found' => 'No publications found',
            'not_found_in_trash' => 'No publications found in trash',
            'menu_name' => 'Publications',
        );

        $args = array(
            'labels' => $labels,
            'public' => true,
            'has_archive' => true,
            'rewrite' => array('slug' => 'publications'),
            'supports' => array('title', 'editor', 'author', 'thumbnail', 'excerpt'),
            'taxonomies' => array('publication_category'),
        );

        register_post_type('publication', $args);
    }

    // Method to render publications using shortcode
    public function render_publications_shortcode($atts) {
        $atts = shortcode_atts(array(
            'category' => '',
        ), $atts);

        $args = array(
            'post_type' => 'publication',
            'posts_per_page' => -1,
            'tax_query' => array(
                array(
                    'taxonomy' => 'publication_category',
                    'field' => 'slug',
                    'terms' => $atts['category'],
                ),
            ),
        );

        $publications = new WP_Query($args);

        if ($publications->have_posts()) {
            $output = '<ul>';

            while ($publications->have_posts()) {
                $publications->the_post();
                $output .= '<li><a href="' . get_permalink() . '">' . get_the_title() . '</a></li>';
            }

            $output .= '</ul>';
            wp_reset_postdata();

            return $output;
        }

        return 'No publications found.';
    }

    // Method to add publications menu to the admin dashboard
    public function add_publications_menu() {
        add_menu_page('Publications', 'Publications', 'manage_options', 'publications', array($this, 'publications_page'), 'dashicons-book-alt', 20);
    }

    // Method to render the publications page in the admin dashboard
    public function publications_page() {
        echo '<div class="wrap">';
        echo '<h1>Publications</h1>';

        // Display form to add publication categories
        echo '<h2>Add Publication Category</h2>';
        echo '<form method="post" action="' . admin_url('admin.php') . '">';
        echo '<input type="hidden" name="action" value="add_publication_category">';
        echo '<label for="publication_category_name">Category Name:</label>';
        echo '<input  class="form-control" type="text" name="publication_category_name" id="publication_category_name">';
        echo '<input  class="form-control" type="submit" value="Add Category">';
        echo '</form>';
        echo '<p> <hr/> </p>';

        // Display form to add publications
        echo '<h2>Add Publication</h2>';
        echo '<form method="post" action="' . admin_url('admin.php') . '">';
        echo '<input type="hidden" name="action" value="add_publication">';
        echo '<label for="publication_title">Publication Title:</label>';
        echo '<input type="text"  class="form-control" name="publication_title" id="publication_title">';
        echo '<label for="publication_content">Publication Content:</label>';
        echo '<textarea  class="form-control" rows="5" cols="50" name="publication_content" id="publication_content"></textarea>';
        echo '<input  class="form-control" type="submit" value="Add Publication">';
        echo '</form>';

        echo '</div>';
    }

    // Method for social media sharing
    public function share_publication_social_media($publication_id) {
        $publication_permalink = get_permalink($publication_id);
        $publication_title = get_the_title($publication_id);

        // Generate the social media sharing URLs or use existing WordPress plugins to handle sharing

        echo '<a href="https://twitter.com/intent/tweet?url=' . urlencode($publication_permalink) . '&text=' . urlencode($publication_title) . '">Share on Twitter</a><br>';
        echo '<a href="https://www.facebook.com/sharer/sharer.php?u=' . urlencode($publication_permalink) . '">Share on Facebook</a>';
    }

    // Method to handle adding a publication category
    public function handle_add_publication_category() {
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

    // Method to handle adding a publication
    public function handle_add_publication() {
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

    


    // Add other necessary methods for editing, deleting publications, social media sharing, and uploading publications from an Excel file.
}

// Instantiate the ScientificPublicationsPlugin class
$scientific_publications_plugin = new ScientificPublicationsPlugin();
