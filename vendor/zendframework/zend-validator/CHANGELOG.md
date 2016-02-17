# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 2.4.8 - 2015-09-08

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#25](https://github.com/zendframework/zend-validator/pull/25) updates the
  `Date` validator to perform checks against `DateTimeImmutable` instead of
  `DateTimeInterface` (as the latter has engine-level restrictions against
  when it is valid for type hints).
- [#35](https://github.com/zendframework/zend-validator/pull/35) ensures that
  the default behavior of the `NotEmpty` validator is to treat `0.0` as
  non-empty.
