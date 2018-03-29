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
    ]
];