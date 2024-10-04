<?php
/**
 * Plugin Name: Recipe Sharing Plugin
 * Description: A plugin to manage and display recipes.
 * Version: 1.0
 * Author: Prajakta Shelke
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Register Recipe Post Type
function rsp_register_recipe_post_type() {
    register_post_type('recipe', [
        'labels' => [
            'name' => __('Recipes'),
            'singular_name' => __('Recipe')
        ],
        'public' => true,
        'has_archive' => true,
        'supports' => ['title', 'editor', 'thumbnail'],
        'rewrite' => ['slug' => 'recipes'],
    ]);
}
add_action('init', 'rsp_register_recipe_post_type');
function rsp_add_recipe_submenu() {
    add_submenu_page(
        'edit.php?post_type=recipe', // Parent slug (custom post type)
        'Add Recipes', // Page title
        'Add Recipes', // Menu title
        'manage_options', // Capability
        'manage-recipes', // Menu slug
        'rsp_manage_recipes_page' // Callback function to display the page
    );
}
add_action('admin_menu', 'rsp_add_recipe_submenu');

// Callback function for Manage Recipes page
function rsp_manage_recipes_page() {
    ?>
    <style>
        .wrap {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            font-size: 24px;
            color: #333;
            margin-bottom: 20px;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        input[type="text"],
        input[type="file"],
        textarea,
        select {
            border: 1px solid #ccc;
            border-radius: 4px;
            padding: 10px;
            margin-bottom: 15px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }

        input[type="text"]:focus,
        input[type="file"]:focus,
        textarea:focus,
        select:focus {
            border-color: #0073aa;
            outline: none;
        }

        input[type="submit"] {
            background-color: #0073aa;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        input[type="submit"]:hover {
            background-color: #005177;
        }
    </style>
    <div class="wrap">
        <h1>Add New Recipe</h1>
        <form method="POST" enctype="multipart/form-data">
            <input type="text" name="recipe_title" placeholder="Recipe Title" required>
            <input type="file" name="featured_image" required>
            <textarea name="ingredients" placeholder="Ingredients" required></textarea>
            <textarea name="preparation_steps" placeholder="Preparation Steps" required></textarea>
            <select name="recipe_category" required>
                <option value="">Select Category</option>
                <?php
                $categories = get_terms(['taxonomy' => 'recipe_category', 'hide_empty' => false]);
                foreach ($categories as $category) {
                    echo '<option value="' . esc_attr($category->term_id) . '">' . esc_html($category->name) . '</option>';
                }
                ?>
            </select>
            <input type="submit" name="add_recipe" value="Add Recipe">
        </form>
    </div>
    <?php

    // Handle form submission
    if (isset($_POST['add_recipe'])) {
        $title = sanitize_text_field($_POST['recipe_title']);
        $ingredients = sanitize_textarea_field($_POST['ingredients']);
        $preparation_steps = sanitize_textarea_field($_POST['preparation_steps']);
        $category_id = intval($_POST['recipe_category']);

        // Insert the recipe into the database
        $recipe_id = wp_insert_post([
            'post_title' => $title,
            'post_content' => '<strong>Ingredients:</strong> ' . $ingredients . '<br><strong>Preparation Steps:</strong> ' . $preparation_steps,
            'post_type' => 'recipe',
            'post_status' => 'publish',
        ]);

        // Assign category
        if ($category_id) {
            wp_set_object_terms($recipe_id, $category_id, 'recipe_category');
        }

        // Upload featured image
        if ($_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['featured_image'];
            $upload = wp_upload_bits($file['name'], null, file_get_contents($file['tmp_name']));
            if (empty($upload['error'])) {
                $attachment = [
                    'guid' => $upload['url'],
                    'post_mime_type' => $file['type'],
                    'post_title' => sanitize_file_name($file['name']),
                    'post_status' => 'inherit',
                ];
                $attachment_id = wp_insert_attachment($attachment, $upload['file'], $recipe_id);
                set_post_thumbnail($recipe_id, $attachment_id);
            }
        }

        echo '<div class="updated"><p>Recipe added successfully!</p></div>';
    }
}

// Register Recipe Taxonomy (Categories)
function rsp_register_recipe_taxonomy() {
    register_taxonomy('recipe_category', 'recipe', [
        'labels' => [
            'name' => __('Recipe Categories'),
            'singular_name' => __('Recipe Category')
        ],
        'hierarchical' => true,
        'public' => true,
        'rewrite' => ['slug' => 'recipe-category'],
    ]);
}
add_action('init', 'rsp_register_recipe_taxonomy');

// Display Recipes Grid on Homepage with Categories
function rsp_display_recipes() {
    $categories = get_terms(['taxonomy' => 'recipe_category', 'hide_empty' => false]);

    ob_start();
    ?>
    <div class="container">
        <div class="row">
            <!-- Sidebar: Recipe Categories -->
            <div class="col-md-3">
                <h3>Categories</h3>
                <ul>
                    <?php foreach ($categories as $category) : ?>
                        <li><a href="<?php echo get_term_link($category); ?>"><?php echo esc_html($category->name); ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
<div class="col-md-9">
                <div class="row">
                    <?php
                    $recipes = new WP_Query(['post_type' => 'recipe', 'posts_per_page' => -1]);
                    while ($recipes->have_posts()) : $recipes->the_post(); ?>
                        <div class="col-md-4">
                            <div class="card mb-4">
                                <a href="<?php the_permalink(); ?>">
                                    <?php the_post_thumbnail('medium', ['class' => 'card-img-top']); ?>
                                    <div class="card-body">
                                        <h5 class="card-title"><?php the_title(); ?></h5>
                                    </div>
                                </a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                    <?php wp_reset_postdata(); ?>
                </div>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('display_recipes', 'rsp_display_recipes');

// Single Recipe Page Template
function rsp_single_recipe_template($single_template) {
    global $post;
    if ($post->post_type == 'recipe') {
        $single_template = plugin_dir_path(__FILE__) . 'single-recipe.php';
    }
    return $single_template;
}
add_filter('single_template', 'rsp_single_recipe_template');

// Remove Add New option from custom post type
function rsp_remove_add_new_recipe() {
    global $submenu;

    // Check if the custom post type exists
    if (isset($submenu['edit.php?post_type=recipe'])) {
        // Remove the "Add New" submenu item
        unset($submenu['edit.php?post_type=recipe'][10]); // 10 is the position of "Add New"
    }
}
add_action('admin_menu', 'rsp_remove_add_new_recipe');
function rsp_enqueue_bootstrap() {
    wp_enqueue_style('bootstrap-css', 'https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css');
    wp_enqueue_script('bootstrap-js', 'https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js', [], null, true);
}
add_action('wp_enqueue_scripts', 'rsp_enqueue_bootstrap');
?>
