<?php
// includes/post-type.php

add_action('init', function () {
    register_post_type('programs', [
        'labels' => [
            'name' => 'Programs',
            'singular_name' => 'Program',
            'add_new' => 'Add New Program',
            'edit_item' => 'Edit Program',
            'new_item' => 'New Program',
            'view_item' => 'View Program',
            'search_items' => 'Search Programs',
        ],
        'public' => true,
        'has_archive' => true,
        'menu_icon' => 'dashicons-location-alt',
        'supports' => ['title','thumbnail'],
        'show_in_rest' => true,
    ]);
});
