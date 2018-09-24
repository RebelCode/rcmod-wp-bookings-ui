# Change log
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [[*next-version*]] - YYYY-MM-DD
### Changed
- Assets management ans styles moved away from this module. Now this module only enqueue built application's JS and styles.
- Using version `0.2.1` of `bookings-js`

## [0.1-alpha19] - 2018-09-13
### Changed
- Using version `0.1.8` of `booking-wizard-components`

## [0.1-alpha18] - 2018-09-11
### Fixed
- Path for `main.js` was wrong.

## [0.1-alpha17] - 2018-09-11
### Changed
- Depends on nonce factory from `eddbk_rest_api` to create nonce instance.

## [0.1-alpha16] - 2018-08-31
### Changed
- Using `booking-wizard-components` version `0.1.6`.

## [0.1-alpha15] - 2018-08-27
### Changed
- Using `0.1.26` version of Bookings JS.

## [0.1-alpha14] - 2018-08-24
### Fixed
- Pagination issue when switching to the different status.
- Pagination issue with wrong number of items expected from server.

## [0.1-alpha13] - 2018-08-13
### Added
- Configuration for UI action pipelines.
- Message box template.
- Edit mode for booking. Now user can see session date and time without loading list of sessions.
- About page.

## [0.1-alpha12] - 2018-07-14
### Changed
- Booking `statuses` information moved to general UI state handler.

### Added
- Settings page templates.
- Settings container implementation.
- Settings page state handler.
- Setting update endpoint handler.

## [0.1-alpha11] - 2018-07-12
### Changed
- Now using version `0.1.4` of `booking-wizard-components`.
- Now using version `0.1.22` of `bookings-js`.
- Session length limit removed.
- The session picker on the Edit Booking modal is no longer hidden initially, but is now always displayed.

## [0.1-alpha10] - 2018-06-13
### Changed
- Now using version `0.1.21` of `bookings-js`.
- Bookings info message style changes make the message more noticeable.
- Session length is limited to 86399 seconds.

## [0.1-alpha9] - 2018-06-12
### Added
- Text explanation of known limitations on download page.
- Text explanation of which timezone is used on bookings page.

### Changed
- Now using version `0.1.20` of `bookings-js`.

### Fixed
- Only visible status keys are sent to client.

## [0.1-alpha8] - 2018-06-11
### Changed
- Now using version `0.1.19` of `bookings-js`.

## [0.1-alpha7] - 2018-06-11
### Changed
- Now using version `0.1.18` of `bookings-js`.
- Now using version `0.1.2` of `bookings-wizard-components`.

## [0.1-alpha6] - 2018-06-07
### Changed
- Now using version `0.1.16` of `bookings-js`.
- Now using version `0.1.1` of `bookings-wizard-components`.
- Refactored names of events and methods in booking application and views.

### Fixed
- Labels in booking editor during saving.
- Session picker in booking editor will now allow to select passed sessions.
- Bookings are not disappearing from list and calendar after clearing search.

## [0.1-alpha5] - 2018-06-06
### Changed
- Now using `bookings-js` at version `0.1.15`.

## [0.1-alpha4] - 2018-06-05
### Added
- Now using wizard components to select a session for a booking.

## [0.1-alpha3] - 2018-05-24
### Changed
- Now using version `0.1.12` of `bookings-js`.

### Fixed
- Scheduling of bookings now uses `submit` transition.

## [0.1-alpha2] - 2018-05-24
### Changed
- Availability UI no longer permits repetition periods shorter than duration.
- Map of possible transitions, and their labels, now provided as state from backend.
- Now using version `0.1.10` of `bookings-js`.

### Fixed
- Links on Availability tab of Download page now lead to actual URLs.
- Links on Bookings List page now point to actual URLs.

## [0.1-alpha1] - 2018-05-21
Initial version.
