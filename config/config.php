<?php

return [
    'wp_bookings_ui' => [
        'wp_rest_api_nonce' => 'wp_rest',
        'settings' => [
            'wizard_labels' => [
                'general' => [
                    'title' => 'Book an Appointment',
                    'buttons' => [
                        'back' => 'Back',
                        'next' => 'Next',
                        'book' => 'Book Now',
                    ],
                ],
                'preview' => [
                    'price' => 'Starting at %s for a %s appointment.',
                    'available' => 'More appointment durations available in the next step.',
                    'booking' => 'You are booking:',
                ],
                'confirmation' => [
                    'booking' => 'You are booking: *%s* - %s appointment',
                    'starting' => 'Starting at *%s*',
                    'price' => 'The price is of *%s*',
                ],
                'fields' => [
                    'service' => [
                        'title' => 'Select a service',
                        'placeholder' => 'Select option',
                    ],
                    'duration' => [
                        'title' => 'Select duration',
                    ],
                    'date' => [
                        'title' => 'Select a date',
                        'empty' => 'No appointments are available this month.',
                    ],
                    'time' => [
                        'title' => 'Select available time',
                        'placeholder' => 'Select a date to pick a time from.',
                    ],
                    'timezone' => [
                        'title' => 'Change timezone',
                    ],
                    'notes' => [
                        'title' => 'Additional notes',
                        'placeholder' => 'If you have got any special requests for this service, please note them down here.',
                    ],
                ],
            ],
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
                ],
            ],
            'fields' => [
                'week_starts_on',
                'default_calendar_view',
                'booking_wizard_color',
                'booking_statuses_colors',
                'booking_wizard_labels'
            ],
            'prefix' => 'eddbk',
            'default_values' => [
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
                'booking_wizard_labels' => []
            ],
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
            'services' => [
                'page_title' => 'Services',
                'menu_title' => 'Services',
                'capability' => 'publish_posts',
                'menu_slug' => 'eddbk-services',
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
            'get_started' => 'https://docs.eddbookings.com/category/22-getting-started',
            'roadmap' => 'https://eddbookings.com/roadmap/',
            'edd_ref' => 'https://easydigitaldownloads.com/?ref=15',
            'rebelcode' => 'https://rebelcode.com/',
            'how_to_wizard' => 'https://docs.eddbookings.com/article/31-how-to-add-the-booking-wizard-to-your-website',
            'feature_request' => 'https://eddbookings.com/roadmap/#feature-request',
            'contact_us' => 'https://eddbookings.com/contact/',
            'license' => 'edit.php?post_type=download&page=edd-settings&tab=licenses'
        ],
        'assets_urls_map_path' => WP_BOOKINGS_UI_MODULE_CONFIG_DIR.'/assets_urls_map.php',
        'assets' => [
            'bookings' => [
                'app.min.js' => 'bookings_ui/dist/app.min.js',
            ],
            'styles' => [
                'app' => 'bookings_ui/dist/app.min.css',
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
            'services' => [
                'fetch' => [
                    'method' => 'get',
                    'endpoint' => '/eddbk/v1/services/',
                ],
                'delete' => [
                    'method' => 'delete',
                    'endpoint' => '/eddbk/v1/services/',
                ],
                'update' => [
                    'method' => 'patch',
                    'endpoint' => '/eddbk/v1/services/',
                ],
                'create' => [
                    'method' => 'post',
                    'endpoint' => '/eddbk/v1/services/',
                ],
            ],
        ],

        /*
         * Validators configurations. Corresponding validator will be created and
         * will be injectable into components.
         */
        'validators' => [
            'complexSetupValidator' => [
                /*
                 * First possible complex setup indicator is a big amount of available sessions lengths.
                 * If the amount > 2, complex setup validation wouldn't pass.
                 */
                [
                    'field' => 'sessions',
                    'rule' => 'length',
                    'value' => [0, 2] // Max count of session lengths is 2.
                ],
                /*
                 * Second indicator is a very long availability duration for service.
                 * If duration is longer that 2 years (730 days), complex setup validation wouldn't pass.
                 */
                [
                    'field' => 'maxAvailabilitiesDuration',
                    'rule' => 'max_value',
                    'value' => 730 // Max availability duration in days is 730 (2 years).
                ]
            ]
        ],

        /*
         * List of different UI actions pipes. Each of pipe is configurable and can be ran
         * on client on some action.
         */
        'ui_actions' => [
            /*
             * Actions for booking enabled tick change.
             */
            'bookingsEnabledChanged' => [
                /*
                 * Add message to "Download Prices" and hide it. Added message will be shown
                 * if user manually unhide this metabox.
                 */
                [
                    'action' => 'addBlock',
                    'arguments' => [
                        'block' => 'rc-message-box',
                        'mode' => 'prepend',
                        'selector' => '#edd_product_prices .inside',
                        'text' => 'The EDD Download Prices are no longer applicable when enabling Bookings. Prices are to be set from the Session Length tab in the Bookings meta box.'
                    ]
                ],
                [
                    'action' => 'checkboxClick',
                    'arguments' => [
                        'value' => false, // value that should be set on "act"
                        'selector' => '#edd_product_prices-hide'
                    ]
                ],

                /*
                 * Automatically check the "Disable the automatic output of the purchase button"
                 * option in the Download Settings meta box
                 */
                [
                    'action' => 'checkboxClick',
                    'arguments' => [
                        'value' => true, // value that should be set on "act"
                        'selector' => '#_edd_hide_purchase_link'
                    ]
                ],

                /*
                 * Add message to the "Download Files" meta box.
                 */
                [
                    'action' => 'addBlock',
                    'arguments' => [
                        'block' => 'rc-message-box',
                        'mode' => 'prepend',
                        'selector' => '#edd_product_files .inside',
                        'text' => 'With Bookings enabled, any downloadable files added here will be included in the purchase price of a booking, and they can be included in the EDD Purchase Confirmation page and Purchase Receipt email. Price assignment is not currently applicable.'
                    ]
                ],
            ]
        ],
    ],
];
