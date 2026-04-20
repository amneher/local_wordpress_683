<?php

$fonts_url = 'https://fonts.googleapis.com/css2?family=Libre+Caslon+Display&family=Libre+Caslon+Text:ital,wght@0,400;0,700;1,400&family=Montserrat:ital,wght@0,400;0,500;0,700;0,900;1,400;1,500;1,700;1,900&display=swap';

add_action('wp_enqueue_scripts', function () use ($fonts_url) {
    wp_enqueue_style('therestart-fonts', $fonts_url, [], null);
    wp_enqueue_style('therestart-style', get_stylesheet_uri(), ['therestart-fonts'], wp_get_theme()->get('Version'));
});

add_action('admin_enqueue_scripts', function () use ($fonts_url) {
    wp_enqueue_style('therestart-fonts', $fonts_url, [], null);
});

add_shortcode('restart_my_account', function () {
    if (!is_user_logged_in()) {
        return '<p>' . wp_kses_post(
            sprintf(
                'Please <a href="%s">log in</a> to manage your account.',
                esc_url(wp_login_url(get_permalink()))
            )
        ) . '</p>';
    }

    $user = wp_get_current_user();

    ob_start();
    ?>
    <div class="restart-my-account">

        <p class="restart-my-account__greeting">Hello, <strong><?php echo esc_html($user->display_name); ?></strong>.</p>

        <nav class="restart-my-account__nav" aria-label="Account navigation">
            <ul>
                <li><a href="<?php echo esc_url(home_url('/registry/')); ?>">My Registries</a></li>
                <li><a href="<?php echo esc_url(get_edit_profile_url($user->ID)); ?>">Edit Profile</a></li>
                <li><a href="<?php echo esc_url(wp_logout_url(home_url('/'))); ?>">Log Out</a></li>
            </ul>
        </nav>

    </div>
    <?php
    return ob_get_clean();
});
