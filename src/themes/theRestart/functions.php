<?php

$fonts_url = 'https://fonts.googleapis.com/css2?family=Libre+Caslon+Display&family=Libre+Caslon+Text:ital,wght@0,400;0,700;1,400&family=Montserrat:ital,wght@0,400;0,500;0,700;0,900;1,400;1,500;1,700;1,900&display=swap';

add_action('wp_enqueue_scripts', function () use ($fonts_url) {
    wp_enqueue_style('therestart-fonts', $fonts_url, [], null);
    wp_enqueue_style('therestart-style', get_stylesheet_uri(), ['therestart-fonts'], wp_get_theme()->get('Version'));
});

add_action('admin_enqueue_scripts', function () use ($fonts_url) {
    wp_enqueue_style('therestart-fonts', $fonts_url, [], null);
});

// Enqueue the Start a Registry JS with localized credentials when on that page
add_action('wp_enqueue_scripts', function () {
    if (!is_page('start-a-registry') || !is_user_logged_in()) {
        return;
    }
    $user    = wp_get_current_user();
    $api_key = get_user_meta($user->ID, '_restart_api_key', true);

    if (!$api_key && class_exists('WP_Application_Passwords')) {
        $result = WP_Application_Passwords::create_new_application_password($user->ID, ['name' => 'Restart Registry']);
        if (!is_wp_error($result)) {
            $api_key = $result[0];
            update_user_meta($user->ID, '_restart_api_key', $api_key);
        }
    }

    if (!$api_key) {
        return;
    }

    wp_enqueue_script(
        'restart-start-registry',
        get_stylesheet_directory_uri() . '/assets/js/start-registry.js',
        [],
        wp_get_theme()->get('Version'),
        true
    );
    wp_localize_script('restart-start-registry', 'restartRegistry', [
        'lambdaUrl'    => defined('RESTART_LAMBDA_URL') ? constant('RESTART_LAMBDA_URL') : 'http://localhost:5000',
        'username'     => $user->user_login,
        'apiKey'       => $api_key,
        'myAccountUrl' => home_url('/my-account/'),
    ]);
});

add_shortcode('restart_start_registry', function () {
    if (!is_user_logged_in()) {
        ob_start(); ?>
        <div class="restart-login-prompt">
            <p>You need to be logged in to create a registry.</p>
            <a href="<?php echo esc_url(wp_login_url(get_permalink())); ?>" class="restart-btn">Log In</a>
        </div>
        <?php return ob_get_clean();
    }

    ob_start(); ?>
    <form id="restart-registry-form" class="restart-form" novalidate>

        <div class="restart-form__group">
            <label class="restart-form__label" for="registry-title">Registry Name <span aria-hidden="true">*</span></label>
            <input class="restart-form__input" type="text" id="registry-title" name="title" required maxlength="200" placeholder="e.g. My New Beginning">
        </div>

        <div class="restart-form__group">
            <label class="restart-form__label" for="event-type">My Situation</label>
            <select class="restart-form__select" id="event-type" name="event_type">
                <option value="">— Select your situation —</option>
                <option value="divorce">Divorce</option>
                <option value="separation">Separation</option>
                <option value="moving-on">Moving On</option>
                <option value="getting-out">Getting Out</option>
                <option value="other">Other</option>
            </select>
        </div>

        <div class="restart-form__group">
            <label class="restart-form__label" for="event-date">My Date</label>
            <input class="restart-form__input" type="date" id="event-date" name="event_date">
        </div>

        <div class="restart-form__group">
            <label class="restart-form__label" for="registry-story">My Story</label>
            <textarea class="restart-form__textarea" id="registry-story" name="story" rows="5" maxlength="2000" placeholder="Share what's brought you to this moment, and what this fresh start means to you…"></textarea>
            <p class="restart-form__hint">This will appear on your public registry page.</p>
        </div>

        <div class="restart-form__group">
            <span class="restart-form__label">Private Registry?</span>
            <label class="restart-toggle">
                <input type="checkbox" id="is-private" name="is_private">
                <span class="restart-toggle__track"></span>
                <span class="restart-toggle__label">Only visible to people I invite</span>
            </label>
        </div>

        <div class="restart-form__group" id="invitees-group" hidden>
            <label class="restart-form__label" for="invitees">Invite by Username</label>
            <input class="restart-form__input" type="text" id="invitees" name="invitees" placeholder="username1, username2">
            <p class="restart-form__hint">Enter WordPress usernames separated by commas.</p>
        </div>

        <div id="restart-form-error" class="restart-form__error" hidden></div>

        <button type="submit" class="restart-btn">Create Registry</button>

    </form>
    <?php return ob_get_clean();
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
