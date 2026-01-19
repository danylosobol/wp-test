<?php
get_header();
?>

<section class="theme-section-404">
    <div class="theme-container">
        <div class="content">
            <span class="theme-heading theme-h1">404</span>
            <h1 class="theme-h2"><?php echo __('Page not found') ?></h1>
            <a href="<?php echo esc_url(get_home_url()) ?>"
                class="theme-button-primary no-icon"><?php echo __('Go To Home') ?></a>
        </div>
    </div>
</section>

<?php
the_content();
get_footer();
