# RebelCode - WP Bookings UI Module

[![Build Status](https://travis-ci.org/rebelcode/rcmod-wp-bookings-ui.svg?branch=develop)](https://travis-ci.org/rebelcode/rcmod-wp-bookings-ui)
[![Code Climate](https://codeclimate.com/github/rebelcode/rcmod-wp-bookings-ui/badges/gpa.svg)](https://codeclimate.com/github/rebelcode/rcmod-wp-bookings-ui)
[![Test Coverage](https://codeclimate.com/github/rebelcode/rcmod-wp-bookings-ui/badges/coverage.svg)](https://codeclimate.com/github/rebelcode/rcmod-wp-bookings-ui/coverage)
[![Latest Stable Version](https://poser.pugx.org/rebelcode/rcmod-wp-bookings-ui/version)](https://packagist.org/packages/rebelcode/rcmod-wp-bookings-ui)
[![Latest Unstable Version](https://poser.pugx.org/rebelcode/rcmod-wp-bookings-ui/v/unstable)](https://packagist.org/packages/rebelcode/rcmod-wp-bookings-ui)

WP Bookings plugin's UI Module

## Run Front Assembling
To run front assembling just run:
```bash
$ npm install # it will install JS dependencies and build `booking-js`
$ npm run build:css # build UI css
```

## Run Application
This module's front-end logic expects to retrieve initial application state to initialize application. By [default](assets/js/main.js) it is `window.EDDBK_APP_STATE` which should be generated on server using `wp_localize_script(...)`. 

We have two separate pages that require different states to work. Bookings page and one service page. So for this 2 pages we need two different application states. 

### Service Page
#### State
Expected structure of **state** on service page (when user open some service for editing):
```
{
  // List of all availabilities for this service.
  "availabilities": [
    {
      "id": 1,
      "fromDate": "2017-01-03",
      
      "repeats": "week",
      "repeatsEvery": 2,
      "repeatsOn": ["mon", "tue", "thu"],
      
      "repeatsEnds": "afterWeeks",
      "repeatsEndsWeeks": 12,
      
      "fromTime": "09:30:00",
      "toTime": "12:30:00"
    }
    // ... other availabilities
  ],
  // List of all available session lengths. 
  "sessions": [
    {
      "id": 1,
      "length": 120, // session length in seconds
      "price": 10.00 
    }
    // ... other sessions
  ],
  // Current service's bookings display options.
  "displayOptions": {
    "useCustomerTimezone": true
    // ... other options
  }
}
```

### Bookings Page
#### State
Expected structure of **state** on bookings page:
```
{
  // Statuses that should be displayed on screen options
  "screenStatuses": [
    "draft", "approved", "scheduled", "pending", "completed"
  ],
  
  // List of all services. It will be used to filter bookings and for bookings editing.
  "services": [
    {
      "id": 1,
      "title": "Service Name",
      "color": "#ff7f50" // Service color needed to render it in the calendar
    }
  ]
}
```

#### Endpoints 

Here is endpoints required for bookings page. Paths are not real, this is just demonstration of concept which functionality is required from backend to make everything works.

##### Bookings
 
`GET /booking` - Retrieve list of bookings. It should accept next parameters to filter result:  

Calendar filtering:
- `start` - Start date to get bookings. For example: `"2017-07-11"`,
- `end` - End date to get bookings. For example: `"2017-07-18"`.

List view filtering:   
- `page` - Page number for pagination. For exmple: `1`,
- `service` - Service to filter by it. If empty - no service filtering happens. For example: `1`,
- `month` - Month number to filter bookings by it. If empty - no month filtering happens. For example: `1`,
- `search` - Search string to filter bookings by it. Allows to filter bookings via client name or client email address. If empty - no searching happens. For example: `client@rebelcode.com`,
- `statuses` - Statues to filter bookings by them. For example: `draft,scheduled`.

`POST /booking` - Create one booking.

`UPDATE /booking/{id}` - Update booking by it's ID.

`DELETE /booking/{id}` - Delete booking by it's ID.

##### Clients

This API endpoint is required for booking editing functionality. When user creates/edits booking he can search across all clients or create new one.

`GET /client?search={queryString}` - Search for client.

`POST /client` - Create new client. Should accept this two fields:  
- `name` - Client's name,
- `email` - Clien's email.
