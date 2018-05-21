<?php

use RebelCode\EddBookings\Logic\Module\BookingTransitionInterface as T;
use RebelCode\EddBookings\Logic\Module\BookingStatusInterface as S;

return [
    'wp_bookings_ui' => [
        'screen_options' => [
            'key' => WP_BOOKINGS_UI_SCREEN_STATUSES_KEY,
            'endpoint' => admin_url('admin-ajax.php?action=set_'.WP_BOOKINGS_UI_SCREEN_STATUSES_KEY),
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
            S::STATUS_DRAFT => 'Draft',
            S::STATUS_IN_CART => 'Cart',
            S::STATUS_PENDING => 'Pending',
            S::STATUS_APPROVED => 'Approved',
            S::STATUS_REJECTED => 'Rejected',
            S::STATUS_SCHEDULED => 'Scheduled',
            S::STATUS_CANCELLED => 'Cancelled',
            S::STATUS_COMPLETED => 'Completed',
        ],
        'hidden_statuses' => [
            S::STATUS_NONE,
            S::STATUS_IN_CART,
            S::STATUS_APPROVED,
            S::STATUS_REJECTED
        ],
        'transitions_labels' => [
            T::TRANSITION_CART => 'Set booking to In Cart',
            T::TRANSITION_DRAFT => 'Set booking to Draft',
            T::TRANSITION_SUBMIT => 'Set booking to Pending',
            T::TRANSITION_COMPLETE => 'Set booking to Completed',
            T::TRANSITION_APPROVE => 'Approve booking',
            T::TRANSITION_REJECT => 'Reject booking',
            T::TRANSITION_SCHEDULE => 'Schedule booking',
            T::TRANSITION_CANCEL => 'Cancel booking',
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
        ],
    ],
];
