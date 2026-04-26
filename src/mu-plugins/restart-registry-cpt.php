<?php
/**
 * MU-Plugin: Local dev helpers + restart-registry CPT registration.
 *
 * Enables Application Password auth over plain HTTP (safe for local only).
 */
add_filter('wp_is_application_passwords_available', '__return_true');

/**
 * MU-Plugin: Register the restart-registry custom post type with REST API support.
 *
 * Mirrors the CPT UI configuration from the live site so the local dev environment
 * exposes the same /wp/v2/restart-registry REST endpoint and post meta fields.
 */

add_action('init', function () {
    if (!get_role('registry_user')) {
        add_role('registry_user', 'Registry User', ['read' => true]);
    }

    // Use add_cap so caps stay current even if the role was already persisted.
    $registry_user_caps = [
        'edit_restart_registries',
        'publish_restart_registries',
        'delete_restart_registries',
        'read_private_restart_registries',
    ];
    $registry_user = get_role('registry_user');
    foreach ($registry_user_caps as $cap) {
        $registry_user->add_cap($cap);
    }

    // Grant administrator the full set of custom caps so admin users can
    // create/edit/delete any restart-registry post (including setting author).
    $admin_caps = [
        'edit_restart_registries',
        'edit_others_restart_registries',
        'edit_private_restart_registries',
        'edit_published_restart_registries',
        'publish_restart_registries',
        'read_private_restart_registries',
        'delete_restart_registries',
        'delete_others_restart_registries',
        'delete_private_restart_registries',
        'delete_published_restart_registries',
    ];
    $admin = get_role('administrator');
    if ($admin) {
        foreach ($admin_caps as $cap) {
            $admin->add_cap($cap);
        }
    }
});

add_action('init', function () {
    register_post_type('restart-registry', [
        'label'               => 'Registries',
        'labels'              => [
            'name'          => 'Registries',
            'singular_name' => 'Registry',
            'add_new_item'  => 'Create a Registry',
            'edit_item'     => 'Edit Registry',
            'not_found'     => 'No Registries Found',
        ],
        'description'         => "Consists of a user's story, info, and list of items they wished for.",
        'public'              => true,
        'show_in_rest'        => true,
        'rest_base'           => 'restart-registry',
        'supports'            => ['title', 'editor', 'excerpt', 'custom-fields', 'author'],
        'taxonomies'          => ['category', 'post_tag'],
        'has_archive'         => 'registry',
        'hierarchical'        => false,
        'rewrite'             => ['slug' => 'registry'],
        'capability_type'     => ['restart_registry', 'restart_registries'],
        'map_meta_cap'        => true,
    ]);
});

add_action('init', function () {
    $meta_args = [
        'object_subtype' => 'restart-registry',
        'show_in_rest'   => true,
        'single'         => true,
        'type'           => 'string',
    ];

    register_post_meta('restart-registry', 'restart_invitees', $meta_args);
    register_post_meta('restart-registry', 'restart_item_ids', $meta_args);
    register_post_meta('restart-registry', 'restart_event_type', $meta_args);
    register_post_meta('restart-registry', 'restart_event_date', $meta_args);
});