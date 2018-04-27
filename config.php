<?php

return [
    'screen_options' => [
        'key' => WP_BOOKINGS_UI_SCREEN_STATUSES_KEY,
        'endpoint' => admin_url('admin-ajax.php?action=set_' . WP_BOOKINGS_UI_SCREEN_STATUSES_KEY)
    ],
    'metabox' => [
        'id' => 'service_booking_settings',
        'post_type' => 'post', //'download',
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
        'require' => 'https://cdnjs.cloudflare.com/ajax/libs/require.js/2.3.5/require.js',

        'bookings_ui/assets/js/main.js' => plugins_url(WP_BOOKINGS_UI_MODULE_RELATIVE_DIR . '/assets/js/main.js', EDDBK_FILE),
        'bookings_ui/dist/app.min.js' => plugins_url(WP_BOOKINGS_UI_MODULE_RELATIVE_DIR . '/dist/app.min', EDDBK_FILE),
        'bookings_ui/dist/wp-booking-ui.css' => plugins_url(WP_BOOKINGS_UI_MODULE_RELATIVE_DIR . '/dist/wp-booking-ui.css', EDDBK_FILE),

        'style_deps' => [
            'https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.8.0/fullcalendar.min.css'
        ]
    ],
    'endpointsConfig' => [
        'bookings' => [
            'fetch' => [
                'method' => 'get',
                'endpoint' => '/eddbk/v1/bookings/',
            ],
            'update' => [
                'method' => 'patch',
                'endpoint' => '/eddbk/v1/bookings/',
            ],
            'create' => [
                'method' => 'post',
                'endpoint' => '/eddbk/v1/bookings/',
            ],
            'delete' => [
                'method' => 'delete',
                'endpoint' => '/eddbk/v1/bookings/',
            ]
        ],
        'clients' => [
            'fetch' => [
                'method' => 'get',
                'endpoint' => '/eddbk/v1/clients/',
            ],
            'create' => [
                'method' => 'post',
                'endpoint' => '/eddbk/v1/clients/',
            ]
        ]
    ]
];