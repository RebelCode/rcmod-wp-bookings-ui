# Change log
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [[*next-version*]] - YYYY-MM-DD
### Added
- Added configuration for UI action pipelines.
- Added message box template.

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
