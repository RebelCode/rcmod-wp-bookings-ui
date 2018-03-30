<?php

return [
    'metabox' => [
        'id' => 'service_booking_settings',
        'post_type' => 'download',
        'title' => __('Booking Options', 'eddbk'),
    ],
    'menu' => [
        'root' => [
            'page_title' => __('Bookings', 'eddbk'),
            'menu_title' => __('Bookings', 'eddbk'),
            'capability' => 'publish_posts', //publish_bookings
            'menu_slug' => 'eddbk-bookings',
            'icon' => 'dashicons-calendar-alt',
            'position' => 20
        ],
        'settings' => [
            'page_title' => __('Settings', 'eddbk'),
            'menu_title' => __('Settings', 'eddbk'),
            'capability' => 'publish_posts', //publish_bookings
            'menu_slug' => 'eddbk-settings'
        ],
        'about' => [
            'page_title' => __('About', 'eddbk'),
            'menu_title' => __('About', 'eddbk'),
            'capability' => 'publish_posts', //publish_bookings
            'menu_slug' => 'eddbk-about'
        ]
    ],
    'templates' => [
        'components',
        'main',

        'availability/metabox',
        'availability/service-availability-editor',
        'availability/tab-availability',
        'availability/tab-display-options',
        'availability/tab-session-length',

        'booking/booking-editor',
        'booking/bookings-calendar-view',
        'booking/bookings-list-view',
        'booking/bookings-page',
        'booking/general',
        'booking/screen-options',
    ],
    'assets' => [
        'app' => plugins_url(WP_BOOKINGS_UI_MODULE_DIR . '/assets/js/main.js', EDDBK_FILE),

        'require' => 'https://cdnjs.cloudflare.com/ajax/libs/require.js/2.3.5/require.js',
        'require_assets' => [
            'app' => plugins_url(WP_BOOKINGS_UI_MODULE_DIR . '/dist/js/app.min', EDDBK_FILE),
        ],

        'style' => plugins_url(WP_BOOKINGS_UI_MODULE_DIR . '/dist/css/app.css', EDDBK_FILE),
        'style_deps' => [
            'https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.8.0/fullcalendar.min.css'
        ]
    ]
];