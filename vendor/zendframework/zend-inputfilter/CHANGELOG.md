# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 2.4.8 - 2015-09-09

### Added

- Nothing.

### Deprecated

- [#26](https://github.com/zendframework/zend-inputfilter/pull/26) Deprecate magic logic for auto attach a NonEmpty
 validator with breakChainOnFailure = true. Instead append NonEmpty validator when desired.

  ```php
  $input = new Zend\InputFilter\Input();
  $input->setContinueIfEmpty(true);
  $input->setAllowEmpty(true);
  $input->getValidatorChain()->attach(new Zend\Validator\NotEmpty(), /* break chain on failure */ true);
  ```
### Removed

- Nothing.

### Fixed

- [#22](https://github.com/zendframework/zend-inputfilter/pull/22) adds tests to
  verify two conditions around inputs with fallback values:
  - If the input was not in the data set, it should not be represented in either
    the list of valid *or* invalid inputs.
  - If the input *was* in the data set, but empty, it should be represented in
    the list of valid inputs.
- [#31](https://github.com/zendframework/zend-inputfilter/pull/31) updates the
  `InputFilterInterface::add()` docblock to match existing, shipped implementations.
- [#25](https://github.com/zendframework/zend-inputfilter/pull/25) Fix missing optional fields to be required.
  BC Break since 2.3.9.
  For completely fix this you need to setup your inputs as follow.

  ```php
  $input = new Input();
  $input->setAllowEmpty(true);         // Disable BC Break logic related to treat `null` values as valid empty value instead *not set*.
  $input->setContinueIfEmpty(true);    // Disable BC Break logic related to treat `null` values as valid empty value instead *not set*.
  $input->getValidatorChain()->attach(
      new Zend\Validator\NotEmpty(),
      true                             // break chain on failure
  );
  ```

  ```php
  $inputSpecification = array(
    'allow_empty' => true,
    'continue_if_empty' => true,
    'validators' => array(
      array(
        'break_chain_on_failure' => true,
        'name' => 'Zend\\Validator\\NotEmpty',
      ),
    ),
  );
  ```
- [Numerous fixes](https://github.com/zendframework/zend-inputfilter/milestones/2.4.8)
  aimed at bringing the functionality back to the pre-2.4 code, and improving
  quality overall of the component via increased testing and test coverage.

## 2.4.7 - 2015-08-11

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#15](https://github.com/zendframework/zend-inputfilter/pull/15) ensures that
  `ArrayAccess` data provided to an input filter using `setData()` can be
  validated, a scenario that broke with [#7](https://github.com/zendframework/zend-inputfilter/pull/7).

## 2.4.6 - 2015-08-03

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#10](https://github.com/zendframework/zend-inputfilter/pull/10) fixes an
  issue with with the combination of `required`, `allow_empty`, and presence of
  a fallback value on an input introduced in 2.4.5. Prior to the fix, the
  fallback value was no longer considered when the value was required but no
  value was provided; it now is.

## 2.4.5 - 2015-07-28

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#7](https://github.com/zendframework/zend-inputfilter/pull/7) fixes an issue
  with the combination of `required` and `allow_empty`, now properly
  invalidating a data set if the `required` input is missing entirely
  (previously, it would consider the data set valid, and auto-initialize the
  missing input to `null`).
