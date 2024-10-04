<?php get_header(); ?>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h1><?php the_title(); ?></h1>
            <?php if (has_post_thumbnail()) : ?>
                <div class="recipe-image">
                    <?php the_post_thumbnail('large'); ?>
                </div>
            <?php endif; ?>

            <div class="recipe-content">
                <?php the_content(); ?>
            </div>

            <div class="recipe-meta">
                <strong>Category:</strong>
                <?php
                $categories = get_the_terms(get_the_ID(), 'recipe_category');
                if ($categories) {
                    foreach ($categories as $category) {
                        echo '<a href="' . esc_url(get_term_link($category)) . '">' . esc_html($category->name) . '</a> ';
                    }
                }
                ?>
            </div>
        </div>
    </div>
</div>

<?php
    function rsp_custom_footer() {
    // Get the Contact Us page ID
    $contact_page_id = 189; // Replace with the actual ID of the Contact Us page
    ?>
    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row">
                <!-- Footer Content -->
                <div class="col-md-6">
                    <p>&copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?>. All Rights Reserved.</p>
                </div>
                <div class="col-md-6 text-md-right">
                    <ul class="list-inline">
                        <li class="list-inline-item">
                           <a href="<?php echo esc_url(home_url() . '?page_id=194'); ?>" class="text-white" target="_blank" rel="noopener noreferrer">About Us</a>
                        </li>
                        <li class="list-inline-item">
                          <a href="<?php echo esc_url(home_url() . '?page_id=' . $contact_page_id); ?>" class="text-white" target="_blank" rel="noopener noreferrer">Contact Us</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>
    <?php
}

// Hook the footer function into wp_footer
add_action('wp_footer', 'rsp_custom_footer');

// Enqueue Bootstrap for the footer styling
function rsp_enqueue_footer_styles() {
    wp_enqueue_style('bootstrap-css', 'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css');
}
add_action('wp_enqueue_scripts', 'rsp_enqueue_footer_styles');

get_footer();  // Ensure this is included to call the footer template