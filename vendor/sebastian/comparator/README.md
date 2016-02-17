[![Build Status](https://travis-ci.org/sebastianbergmann/comparator.svg?branch=master)](https://travis-ci.org/sebastianbergmann/comparator)

# Comparator

This component provides the functionality to compare PHP values for equality.

## Installation

To add Comparator as a local, per-project dependency to your project, simply add a dependency on `sebastian/comparator` to your project's `composer.json` file. Here is a minimal example of a `composer.json` file that just defines a dependency on Comparator 1.2:

```JSON
{
    "require": {
        "sebastian/comparator": "~1.2"
    }
}
```

## Usage

```php
<?php
use SebastianBergmann\Comparator\Factory;
use SebastianBergmann\Comparator\ComparisonFailure;

$date1 = new DateTime('2013-03-29 04:13:35', new DateTimeZone('America/New_York'));
$date2 = new DateTime('2013-03-29 03:13:35', new DateTimeZone('America/Chicago'));

$factory = new Factory;
$comparator = $factory->getComparatorFor($date1, $date2);

try {
    $comparator->assertEquals($date1, $date2);
    print "Dates match";
}

catch (ComparisonFailure $failure) {
    print "Dates don't match";
}
```

