<?php

/*
Plugin Name: WordPress Slider
Plugin URI: http://www.10twebdesign.com/
Description: Image Slider for WordPress designed to be easy to maintain.
Version: 0.1a
Author: 10T Web Design
Author URI: http://www.10twebdesign.com/
GitHub Plugin URI: https://github.com/10twebdesign/wordpress-slider
License: GPL2
*/

add_action( 'init', 'register_slider_post_type', 0 );
add_action( 'add_meta_boxes', 'wordpress_slider_meta_box');
add_action( 'save_post', 'wordpress_slider_meta_box_save');
add_action( 'admin_head', 'wordpress_slider_featured_image_check');
add_shortcode ( 'image-slider', 'wordpress_slider_shortcode');
add_action( 'wp_enqueue_scripts', 'wordpress_slider_encode_scripts');

function register_slider_post_type() {

    $labels = array(
        'name'                => _x( 'Slider Images', 'Post Type General Name', 'wordpress-slider' ),
        'singular_name'       => _x( 'Slider Image', 'Post Type Singular Name', 'wordpress-slider' ),
        'menu_name'           => __( 'Slider Image', 'wordpress-slider' ),
        'name_admin_bar'      => __( 'Slider Image', 'wordpress-slider' ),
        'parent_item_colon'   => __( 'Parent Slider Image:', 'wordpress-slider' ),
        'all_items'           => __( 'All Slider Images', 'wordpress-slider' ),
        'add_new_item'        => __( 'Add New Slider Image', 'wordpress-slider' ),
        'add_new'             => __( 'Add New', 'wordpress-slider' ),
        'new_item'            => __( 'New Slider Image', 'wordpress-slider' ),
        'edit_item'           => __( 'Edit Slider Image', 'wordpress-slider' ),
        'update_item'         => __( 'Update Slider Image', 'wordpress-slider' ),
        'view_item'           => __( 'View Slider Image', 'wordpress-slider' ),
        'search_items'        => __( 'Search Slider Image', 'wordpress-slider' ),
        'not_found'           => __( 'Not found', 'wordpress-slider' ),
        'not_found_in_trash'  => __( 'Not found in Trash', 'wordpress-slider' ),
    );
    $args = array(
        'label'               => __( 'slider_image', 'wordpress-slider' ),
        'description'         => __( 'Slider Image', 'wordpress-slider' ),
        'labels'              => $labels,
        'supports'            => array( 'title', 'editor', 'thumbnail', ),
        'hierarchical'        => false,
        'public'              => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'menu_position'       => 25.2525,
        'menu_icon'           => 'dashicons-images-alt',
        'show_in_admin_bar'   => true,
        'show_in_nav_menus'   => false,
        'can_export'          => true,
        'has_archive'         => false,
        'exclude_from_search' => true,
        'publicly_queryable'  => true,
        'rewrite'             => false,
        'capability_type'     => 'page',
    );
    register_post_type( 'slider_image', $args );
}

function wordpress_slider_featured_image_check() {
    if(!current_theme_supports('post-thumbnails')) {
        ?>
        <div class="error">
            <p><?php _e('WordPress Slider requires that your theme support featured images / post thumbnails.', 'wordpress-slider');?></p>
            <p><?php _e('Unfortunately, it does not apper that your theme currently does; the slider will not function properly without this support.', 'wordpress-slider'); ?></p>
        </div>
        <?php
    }
}

function wordpress_slider_meta_box() {
    $screens = array('slider_image');
    foreach($screens as $screen) {
        add_meta_box('slider_image_meta', __("Slider Link", 'wordpress-slider'), 'wordpress_slider_meta_box_display', $screen);
    }
}

function wordpress_slider_meta_box_display($post) {
    wp_nonce_field('wordpress_slider_meta_box', 'wordpress_slider_meta_box_nonce');
    $value = get_post_meta($post->ID, '_wordpress_slider_url', true);
    ?>
    <label for="wordpress_slider_url"><?php _e('Slider Link:', 'wordpress-slider'); ?></label>
    <input type="text" name="wordpress_slider_url" id="wordpress_slider_url" value="<?php echo $value; ?>">
    <?php
}

function wordpress_slider_meta_box_save($post) {
    if(!isset($_POST['wordpress_slider_meta_box_nonce'])) {
        return;
    }
    if(!wp_verify_nonce($_POST['wordpress_slider_meta_box_nonce'], 'wordpress_slider_meta_box')) {
        return;
    }
    if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if(!current_user_can('edit_post', $post)) {
        return;
    }
    $wordpress_slider_url = sanitize_bookmark($_POST['wordpress_slider_url']);
    if($wordpress_slider_url) {
        update_post_meta($post, '_wordpress_slider_url', $wordpress_slider_url);
    } else {
        delete_post_meta($post, '_wordpress_slider_url');
    }
}

function wordpress_slider_encode_scripts() {
    wp_enqueue_style('slick_css', plugin_dir_url(__FILE__) . 'slick/slick.css');
    wp_enqueue_script('jquery_210', 'http://code.jquery.com/jquery-2.1.0.min.js');
    wp_enqueue_script('jquery_migrate', 'http://code.jquery.com/jquery-migrate-1.2.1.min.js');
    wp_enqueue_script('slick_js', plugin_dir_url(__FILE__) . 'slick/slick.min.js');
//    wp_enqueue_script('slick_start', plugin_dir_url(__FILE__) . 'slick-start.js');
}

function wordpress_slider_shortcode() {
    $args = array (
        'post_type' => 'slider_image',
        'posts_per_page' => -1
    );
    $query = new WP_Query($args);
    $ret = "<script type='text/javascript'>

$(document).ready(function(){
    $('.slides_container').slick({
        'autoplay': true,
});
});
</script>";
    if($query->have_posts()) {
        $ret .= '';
        $ret .= '<div class="slides_container">';
        while($query->have_posts()) {
            $query->the_post();
            $ret .= '<div>';
            $url = get_post_meta(get_the_ID(), '_wordpress_slider_url', true);
            if($url) {
                $ret .= "<a href='$url'>";
            }
            $ret .= get_the_post_thumbnail(get_the_ID(), 'full');
            if($url) {
                $ret .= "</a>";
            }
            $ret .= '<div class="caption">';
            $ret .= '<h5>' . get_the_title() . '</h5>';
            $ret .= get_the_content();
            $ret .= '</div>';
            $ret .= '</div>';
        }
        $ret .= '</div>';
    } else {
        $ret = "Nope.";
    }
    wp_reset_postdata();
    return $ret;
}