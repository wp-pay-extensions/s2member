# Change Log

All notable changes to this project will be documented in this file.

This projects adheres to [Semantic Versioning](http://semver.org/) and [Keep a CHANGELOG](http://keepachangelog.com/).

## [Unreleased][unreleased]
- 

## [2.2.1] - 2021-01-14
- Prevent updating eot if (retry) payment period end date is (before) current eot time.
- Code quality.
- Fix using removed payment data class and multiple status update actions.
- Fix setting subscription next payment date for new subscriptions (removes payment data class).

## [2.2.0] - 2020-11-09
- Added support for new subscription phases and periods.
- Fixed processing list servers for recurring payments.

## [2.1.3] - 2020-07-22
- Fix creating empty subscriptions.

## [2.1.2] - 2020-06-02
- Add payment origin post ID.

## [2.1.1] - 2020-04-03
- Set plugin integration name.

## [2.1.0] - 2020-03-19
- Extension extends abstract plugin integration.

## [2.0.5] - 2019-12-22
- Updated subscription source details.
- Updated usage of deprecated `addItem()` method.

## [2.0.4] - 2019-10-04
- Send user first and last name to list servers.
- Added s2Member plugin dependency.
- Added support for list server opt-in.

## [2.0.3] - 2019-08-26
- Updated packages.

## [2.0.2] - 2019-05-15
- Set subscription 'total amount' instead of 'amount'.

## [2.0.1] - 2018-12-12
- Renamed menu item from 'iDEAL' to 'Pay'.
- Update item methods in payment data.

## [2.0.0] - 2018-05-14
- Switched to PHP namespaces.

## [1.2.7] - 2017-12-12
- Add support for recurring payments.

## [1.2.6] - 2017-01-25
- Added filter payment source description.

## [1.2.5] - 2016-10-20
- Added support for payment method in shortcode.

## [1.2.4] - 2016-05-06
- No changes.

## [1.2.3] - 2016-04-12
- No longer use camelCase for payment data.

## [1.2.2] - 2016-02-11
- Fixed 'Notice: Undefined index: orderID'
- Fixed password not included in registration confirmation.
- Added support for payment method in shortcode.
- Removed status code from redirect in status_update.

## [1.2.1] - 2015-10-14
- Fix incorrect period naming.

## [1.2.0] - 2015-05-06
- Added experimental support for `ccaps` in shortcode.
- Added settings field for the signup confirmation email message.
- Added HTML admin views from the Pronamic iDEAL plugin.

## [1.1.1] - 2015-03-03
- Changed WordPress pay core library requirment from `~1.0.0` to `>=1.0.0`.

## [1.1.0] - 2015-02-12
- Show errors if they occur.

## 1.0.0 - 2015-01-20
- First release.

[unreleased]: https://github.com/wp-pay-extensions/s2member/compare/2.2.1...HEAD
[2.2.1]: https://github.com/wp-pay-extensions/s2member/compare/2.2.0...2.2.1
[2.2.0]: https://github.com/wp-pay-extensions/s2member/compare/2.1.3...2.2.0
[2.1.3]: https://github.com/wp-pay-extensions/s2member/compare/2.1.2...2.1.3
[2.1.2]: https://github.com/wp-pay-extensions/s2member/compare/2.1.1...2.1.2
[2.1.1]: https://github.com/wp-pay-extensions/s2member/compare/2.1.0...2.1.1
[2.1.0]: https://github.com/wp-pay-extensions/s2member/compare/2.0.5...2.1.0
[2.0.4]: https://github.com/wp-pay-extensions/s2member/compare/2.0.4...2.0.5
[2.0.4]: https://github.com/wp-pay-extensions/s2member/compare/2.0.3...2.0.4
[2.0.3]: https://github.com/wp-pay-extensions/s2member/compare/2.0.2...2.0.3
[2.0.2]: https://github.com/wp-pay-extensions/s2member/compare/2.0.1...2.0.2
[2.0.1]: https://github.com/wp-pay-extensions/s2member/compare/2.0.0...2.0.1
[2.0.0]: https://github.com/wp-pay-extensions/s2member/compare/1.2.7...2.0.0
[1.2.7]: https://github.com/wp-pay-extensions/s2member/compare/1.2.6...1.2.7
[1.2.6]: https://github.com/wp-pay-extensions/s2member/compare/1.2.5...1.2.6
[1.2.5]: https://github.com/wp-pay-extensions/s2member/compare/1.2.4...1.2.5
[1.2.4]: https://github.com/wp-pay-extensions/s2member/compare/1.2.3...1.2.4
[1.2.3]: https://github.com/wp-pay-extensions/s2member/compare/1.2.2...1.2.3
[1.2.2]: https://github.com/wp-pay-extensions/s2member/compare/1.2.1...1.2.2
[1.2.1]: https://github.com/wp-pay-extensions/s2member/compare/1.2.0...1.2.1
[1.2.0]: https://github.com/wp-pay-extensions/s2member/compare/1.1.1...1.2.0
[1.1.1]: https://github.com/wp-pay-extensions/s2member/compare/1.1.0...1.1.1
[1.1.0]: https://github.com/wp-pay-extensions/s2member/compare/1.0.0...1.1.0
