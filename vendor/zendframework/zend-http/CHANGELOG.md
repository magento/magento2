# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 2.4.8 - 2015-09-14

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#23](https://github.com/zendframework/zend-http/pull/23) fixes a BC break
  introduced with fixes for [ZF2015-04](http://framework.zend.com/security/advisory/ZF2015-04),
  pertaining specifically to the `SetCookie` header. The fix backs out a
  check for message splitting syntax, as that particular class already encodes
  the value in a manner that prevents the attack. It also adds tests to ensure
  the security vulnerability remains patched.
