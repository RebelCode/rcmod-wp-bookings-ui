<?php

return [
    'require' => 'https://cdnjs.cloudflare.com/ajax/libs/require.js/2.3.5/require.js',

    'bookings_ui/assets/js/main.js' => plugins_url(WP_BOOKINGS_UI_MODULE_RELATIVE_DIR . '/assets/js/main.js', EDDBK_FILE),
    'bookings_ui/dist/app.min.js' => plugins_url(WP_BOOKINGS_UI_MODULE_RELATIVE_DIR . '/dist/booking-js/app.min', EDDBK_FILE),
    'bookings_ui/dist/wp-booking-ui.css' => plugins_url(WP_BOOKINGS_UI_MODULE_RELATIVE_DIR . '/dist/wp-booking-ui.css', EDDBK_FILE),

    'cdn/fullcalendar.css' => 'https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.8.0/fullcalendar.min.css'
];