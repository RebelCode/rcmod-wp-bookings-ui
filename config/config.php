<?php

return [
    'wp_bookings_ui' => [
        'screen_options' => [
            'key' => WP_BOOKINGS_UI_SCREEN_STATUSES_KEY,
            'endpoint' => admin_url('admin-ajax.php?action=set_' . WP_BOOKINGS_UI_SCREEN_STATUSES_KEY)
        ],
        'metabox' => [
            'id' => 'service_booking_settings',
            'post_type' => 'download',
            'title' => 'Booking Options',
        ],
        'menu' => [
            'root' => [
                'page_title' => 'Bookings',
                'menu_title' => 'Bookings',
                'capability' => 'publish_bookings',
                'menu_slug' => 'eddbk-bookings',
                'icon' => 'dashicons-calendar-alt',
                'position' => 20
            ],
            'settings' => [
                'page_title' => 'Settings',
                'menu_title' => 'Settings',
                'capability' => 'publish_bookings',
                'menu_slug' => 'eddbk-settings'
            ],
            'about' => [
                'page_title' => 'About',
                'menu_title' => 'About',
                'capability' => 'publish_bookings',
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
        'assets_urls_map_path' => WP_BOOKINGS_UI_MODULE_CONFIG_DIR . '/assets_urls_map.php',
        'assets' => [
            'require.js' => 'require',
            'bookings' => [
                'app.min.js' => 'bookings_ui/dist/app.min.js',
                'main.js' => 'bookings_ui/assets/js/main.js',
            ],
            'styles' => [
                'app' => 'bookings_ui/dist/wp-booking-ui.css',
                'fullcalendar' => 'cdn/fullcalendar.css',
            ]
        ],
        'endpoints_config' => [
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
    ]
];