<?php

return [
    'eddbk' => [
        'week_starts_on'        => 'sunday',
        'default_calendar_view' => 'week',
        'booking_wizard_color'  => '#17a7dd',
        'booking_statuses_colors'     => [
            'draft'     => '#dfe4ea',
            'pending'   => '#1e90ff',
            'scheduled' => '#2ed573',
            'completed' => '#57606f',
            'cancelled' => '#eb4d4b',
        ],
    ],
    'wp_bookings_ui' => [
        'settings' => [
            'options' => [
                'week_starts_on' => [
                    'sunday' => 'Sunday',
                    'monday' => 'Monday',
                    'tuesday' => 'Tuesday',
                    'wednesday' => 'Wednesday',
                    'thursday' => 'Thursday',
                    'friday' => 'Friday',
                    'saturday' => 'Saturday',
                ],
                'default_calendar_view' => [
                    'day'   => 'Day',
                    'week'  => 'Week',
                    'month' => 'Month',
                ]
            ],
            'fields' => [
                'week_starts_on',
                'default_calendar_view',
                'booking_wizard_color',
                'booking_statuses_colors',
            ],
            'prefix' => 'eddbk',
            'array_fields' => [
                'booking_statuses_colors'
            ],
            'action' => WP_BOOKINGS_UI_UPDATE_SETTINGS_ACTION,
            'update_endpoint' => [
                'method' => 'post',
                'url' => admin_url('admin-ajax.php?action=' . WP_BOOKINGS_UI_UPDATE_SETTINGS_ACTION)
            ],
        ],
        'screen_options' => [
            'key' => WP_BOOKINGS_UI_SCREEN_OPTIONS_KEY,
            'fields' => [
                'statuses' => 'statuses',
                'bookingsTimezone' => 'bookingsTimezone'
            ],
            'endpoint' => admin_url('admin-ajax.php?action=set_'.WP_BOOKINGS_UI_SCREEN_OPTIONS_KEY),
        ],
        'metabox' => [
            'id' => 'service_booking_settings',
            'post_type' => 'download',
            'title' => 'Booking Options',
        ],
        'config' => [
            'formats' => [
                'datetime' => [
                    'tzFree' => 'YYYY-MM-DD HH:mm:ss',
                    'store'  => 'YYYY-MM-DDTHH:mm:ssZ',
                    'sessionTime' => 'h:mm a',
                    'dayFull' => 'dddd Do MMMM YYYY',
                    'dayShort' => 'D MMMM YYYY',
                    'monthKey' => 'YYYY-MM',
                    'dayKey' => 'YYYY-MM-DD'
                ]
            ],
            'links' => [
                'service' => '/post.php?post=%d&action=edit',
                'client' => '/edit.php?post_type=download&page=edd-customers&view=overview&id=%d'
            ],
            'currency' => [
                'name'   => edd_get_currency(),
                'symbol' => edd_currency_symbol(),
            ]
        ],
        'menu' => [
            'root' => [
                'page_title' => 'Bookings',
                'menu_title' => 'Bookings',
                'capability' => 'publish_posts',
                'menu_slug' => 'eddbk-bookings',
                'icon' => 'dashicons-calendar-alt',
                'position' => 20,
            ],
            'settings' => [
                'page_title' => 'Settings',
                'menu_title' => 'Settings',
                'capability' => 'publish_posts',
                'menu_slug' => 'eddbk-settings',
            ],
            'about' => [
                'page_title' => 'About',
                'menu_title' => 'About',
                'capability' => 'publish_posts',
                'menu_slug' => 'eddbk-about',
            ],
        ],
        'statuses_labels' => [
            'draft' => 'Draft',
            'in_cart' => 'Cart',
            'pending' => 'Pending',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'scheduled' => 'Scheduled',
            'cancelled' => 'Cancelled',
            'completed' => 'Completed',
        ],
        'hidden_statuses' => [
            'none',
            'in_cart',
            'approved',
            'rejected'
        ],
        'transitions_labels' => [
            'all' => [
                'cart' => 'Set booking to In Cart',
                'draft' => 'Set booking to Draft',
                'submit' => 'Set booking to Pending',
                'complete' => 'Set booking to Completed',
                'approve' => 'Approve booking',
                'reject' => 'Reject booking',
                'schedule' => 'Set to Scheduled',
                'cancel' => 'Cancel booking',
            ],
            'draft' => [
                'submit' => 'Set to Scheduled'
            ]
        ],
        'hidden_transitions' => [
            'all' => [
                'submit',
                'approve',
                'reject'
            ],
            'draft' => [
                'approve',
                'reject'
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
        /*
         * Configuration for placeholder templates.
         */
        'templates_config' => [
            'token_start'   => '${',
            'token_end'     => '}',
            'token_default' => '',
        ],
        'urls' => [
            'beta_guidelines' => 'https://eddbookings.com/beta-download/beta-testing/',
            'beta_docs' => 'https://docs.eddbookings.com/',
        ],
        'assets_urls_map_path' => WP_BOOKINGS_UI_MODULE_CONFIG_DIR.'/assets_urls_map.php',
        'assets' => [
            'require.js' => 'require',
            'bookings' => [
                'app.min.js' => 'bookings_ui/dist/app.min.js',
                'main.js' => 'bookings_ui/assets/js/main.js',
            ],
            'styles' => [
                'app' => 'bookings_ui/dist/wp-booking-ui.css',
                'fullcalendar' => 'cdn/fullcalendar.css',
            ],
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
                ],
            ],
            'clients' => [
                'fetch' => [
                    'method' => 'get',
                    'endpoint' => '/eddbk/v1/clients/',
                ],
                'create' => [
                    'method' => 'post',
                    'endpoint' => '/eddbk/v1/clients/',
                ],
            ],
            'sessions' => [
                'fetch' => [
                    'method' => 'get',
                    'endpoint' => '/eddbk/v1/sessions/',
                ],
            ],
        ],
    ],
];
