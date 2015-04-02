<?php

/*
Plugin Name: WordPress Slider
Plugin URI: http://www.10twebdesign.com/
Description: Image Slider for WordPress designed to be easy to maintain.
Version: 0.2
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
add_action( 'admin_menu', 'wordpress_slider_add_admin_menus');
register_activation_hook(__FILE__, 'wordpress_slider_activation');
register_uninstall_hook(__FILE__, 'wordpress_slider_uninstall');

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

function wordpress_slider_activation() {
    update_option('_wordpress_slider_autoplay', true);
    update_option('_wordpress_slider_autoplay_speed', 3000);
    update_option('_wordpress_slider_dots', false);
    update_option('_wordpress_slider_animation', 1);
    update_option('_wordpress_slider_draggable', true);
    update_option('_wordpress_slider_arrows', false);
}
function wordpress_slider_uninstall() {
    global $wpdb;

    delete_option('_wordpress_slider_autoplay');
    delete_option('_wordpress_slider_autoplay_speed');
    delete_option('_wordpress_slider_dots');
    delete_option('_wordpress_slider_animation');
    delete_option('_wordpress_slider_draggable');
    delete_option('_wordpress_slider_arrows');

    $table_name = $wpdb->prefix . "posts";
    $sql = "DELETE FROM `$table_name` WHERE `post_type` = 'slider_image'";
    $wpdb->query($sql);
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
    wp_enqueue_style('wordpress_slider_css', plugin_dir_url(__FILE__) . 'style.css');
    wp_enqueue_script('jquery_210', 'http://code.jquery.com/jquery-2.1.0.min.js');
    wp_enqueue_script('jquery_migrate', 'http://code.jquery.com/jquery-migrate-1.2.1.min.js');
    wp_enqueue_script('slick_js', plugin_dir_url(__FILE__) . 'slick/slick.min.js');
}

function wordpress_slider_add_admin_menus() {
    add_submenu_page('edit.php?post_type=slider_image', 'Options', 'Options', 'manage_options', 'wordpress_slider_options_menu', 'wordpress_slider_options_menu');
}

function wordpress_slider_options_menu() {
    ?>
    <div class="wrap">
        <h2><?php _e('Slider Options', 'wordpress_slider'); ?></h2>
        <?php
        if(isset($_POST['wordpress_slider_options_nonce'])) {
            wordpress_slider_options_menu_process();
        }
        ?>
        <form id="wordpress_slider_options_form" method="post">
            <?php wp_nonce_field('wordpress_slider_options', 'wordpress_slider_options_nonce'); ?>
            <table class="form-table">
                <tbody>
                    <tr>
                        <?php $value = get_option('_wordpress_slider_autoplay', false); ?>
                        <th scope="row">
                            <label for="wordpress_slider_autoplay"><?php _e('AutoPlay:', 'wordpress_slider'); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" name="wordpress_slider_autoplay" id="wordpress_slider_autoplay"<?php if($value) { echo ' checked="checked"'; } ?>>
                            <p class="description"><?php _e('Should the slider automatically advance from image to image?', 'wordpress_slider'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <?php $value = get_option('_wordpress_slider_autoplay_speed', 3000); ?>
                        <th scope="row">
                            <label for="wordpress_slider_autoplay_speed"><?php _e('AutoPlay Speed:', 'wordpress_slider'); ?></label>
                        </th>
                        <td>
                            <input type="text" name="wordpress_slider_autoplay_speed" id="wordpress_slider_autoplay_speed"<?php if($value) { echo " value='$value'"; }?>>
                            <p class="description"><?php _e('Time between slide changes, in milliseconds. (E.G., 3 seconds = 3000)', 'wordpress_slider'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <?php $value = get_option('_wordpress_slider_arrows', false); ?>
                        <th scope="row"><label for="wordpress_slider_arrows"><?php _e('Previous/Next Buttons', 'wordpress_slider'); ?></label></th>
                        <td>
                            <input type="checkbox" name="wordpress_slider_arrows" id="wordpress_slider_arrors"<?php if($value) { echo ' checked="checked"'; } ?>>
                            <p class="description">Display previous and next buttons under slideshow?</p>
                        </td>
                    </tr>
                    <tr>
                        <?php $value = get_option('_wordpress_slider_dots', false); ?>
                        <th scope="row">
                            <label for="wordpress_slider_dots"><?php _e('Navigation Dots:', 'wordpress_slider'); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" name="wordpress_slider_dots" id="wordpress_slider_dots"<?php if($value) { echo ' checked="checked"'; } ?>>
                            <p class="description"><?php _e('Should we display navigation "dots" below the slides?', 'wordpress_slider'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <?php $value = get_option('_wordpress_slider_animation', false); ?>
                        <th scope="row"><label for="wordpress_slider_animation"><?php _e('Animation:', 'wordpress-slider'); ?></label></th>
                        <td>
                            <select name="wordpress_slider_animation" id="wordpress_slider_animation">
                                <option value="1"<?php if(!$value || $value == 1) { echo ' selected="selected"'; } ?>><?php _e('Slide', 'wordpress-slider'); ?></option>
                                <option value="2"<?php if($value == 2) { echo ' selected="selected"'; } ?>><?php _e('Fade', 'wordpress-slider'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <?php $value = get_option('_wordpress_slider_draggable', true); ?>
                        <th scope="row"><label for="wordpress_slider_draggable"><?php _e('Draggable:', 'wordpress-slider'); ?></label></th>
                        <td>
                            <input type="checkbox" name="wordpress_slider_draggable" id="wordpress_slider_draggable"<?php if($value) { echo ' checked="checked"'; }?>>
                            <p class="description"><?php _e('Enable click-and-drag, or touch-and-drag, slider navigation.', 'wordpress-slider'); ?></p>
                        </td>
                    </tr>
                </tbody>
            </table>
            <p class="submit"><input id="submit" name="submit" type="submit" class="button button-primary" value="Save Options"></p>
        </form>
    </div>
    <?php
}
function wordpress_slider_options_menu_process() {
    if(!wp_verify_nonce($_POST['wordpress_slider_options_nonce'], 'wordpress_slider_options')) {
        return;
    }
    if(!current_user_can('manage_options')) {
        return;
    }

    if($_POST['wordpress_slider_autoplay']) {
        update_option('_wordpress_slider_autoplay', true);
    } else {
        update_option('_wordpress_slider_autoplay', false);
    }
    if(is_numeric($_POST['wordpress_slider_autoplay_speed'])) {
        if($_POST['wordpress_slider_autoplay_speed']) {
            update_option( '_wordpress_slider_autoplay_speed', sanitize_text_field($_POST['wordpress_slider_autoplay_speed']));
        } else {
            update_option('_wordpress_slider_autoplay_speed', 3000);
        }
    }
    if($_POST['wordpress_slider_arrows']) {
        update_option('_wordpress_slider_arrows', true);
    } else {
        update_option('_wordpress_slider_arrows', false);
    }
    if($_POST['wordpress_slider_dots']) {
        update_option('_wordpress_slider_dots', true);
    } else {
        update_option('_wordpress_slider_dots', false);
    }
    switch($_POST['wordpress_slider_animation']) {
        case 1:
        default:
            update_option('_wordpress_slider_animation', 1);
            break;
        case 2:
            update_option('_wordpress_slider_animation', 2);
            break;
    }
    if($_POST['wordpress_slider_draggable']) {
        update_option('_wordpress_slider_draggable', true);
    } else {
        update_option('_wordpress_slider_draggable', false);
    }

    ?>
    <div class="updated"><p>Options saved.</p></div>
    <?php
}

function wordpress_slider_shortcode() {
    $settings_list = '';
    if(get_option('_wordpress_slider_autoplay', true)) {
        $settings_list .= "'autoplay': true,";
        if(get_option('_wordpress_slider_autoplay_speed', false)) {
            $settings_list .= "'autoplaySpeed': " . get_option('_wordpress_slider_autoplay_speed', 3000) . ",";
        }
    } else {
        $settings_list .= "'autoplay': false,";
    }
    if(get_option('_wordpress_slider_arrows', false)) {
        $settings_list .= "'arrows': true,";
    } else {
        $settings_list .= "'arrows': false,";
    }
    if(get_option('_wordpress_slider_dots', false)) {
        $settings_list .= "'dots': true,";
    }
    switch(get_option('_wordpress_slider_animation', 1)) {
        case 1:
        default:
            break;
        case 2:
            $settings_list .= "'fade': true,";
            break;
    }
    if(get_option('_wordpress_slider_draggable', true)) {
        $settings_list .= "'draggable': true,";
    } else {
        $settings_list .= "'draggable': false,";
    }

    $ret = "<script type='text/javascript'>";
    $ret .= "$(document).ready(function(){";
    $ret .= "$('.slides_container').slick({";
    $ret .= $settings_list;
    $ret .= "});";
    $ret .= "});";
    $ret .= "</script>";

    $args = array (
        'post_type' => 'slider_image',
        'posts_per_page' => -1
    );
    $query = new WP_Query($args);


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
            $ret .= '<div class="slider-caption">';
            $ret .= '<h5>' . get_the_title() . '</h5>';
            $ret .= get_the_content();
            $ret .= '</div>';
            if($url) {
                $ret .= "</a>";
            }
            $ret .= '</div>';
        }
        $ret .= '</div>';
    } else {
        $ret = "Nope.";
    }
    wp_reset_postdata();
    return $ret;
}