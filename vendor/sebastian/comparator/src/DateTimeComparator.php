<?php
/*
 * This file is part of the Comparator package.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SebastianBergmann\Comparator;

/**
 * Compares DateTimeInterface instances for equality.
 */
class DateTimeComparator extends ObjectComparator
{
    /**
     * Returns whether the comparator can compare two values.
     *
     * @param  mixed $expected The first value to compare
     * @param  mixed $actual   The second value to compare
     * @return bool
     */
    public function accepts($expected, $actual)
    {
        return ($expected instanceof \DateTime || $expected instanceof \DateTimeInterface) &&
            ($actual instanceof \DateTime || $actual instanceof \DateTimeInterface);
    }

    /**
     * Asserts that two values are equal.
     *
     * @param  mixed             $expected     The first value to compare
     * @param  mixed             $actual       The second value to compare
     * @param  float             $delta        The allowed numerical distance between two values to
     *                                         consider them equal
     * @param  bool              $canonicalize If set to TRUE, arrays are sorted before
     *                                         comparison
     * @param  bool              $ignoreCase   If set to TRUE, upper- and lowercasing is
     *                                         ignored when comparing string values
     * @throws ComparisonFailure Thrown when the comparison
     *                                        fails. Contains information about the
     *                                        specific errors that lead to the failure.
     */
    public function assertEquals($expected, $actual, $delta = 0.0, $canonicalize = false, $ignoreCase = false)
    {
        $delta = new \DateInterval(sprintf('PT%sS', abs($delta)));

        $expectedLower = clone $expected;
        $expectedUpper = clone $expected;

        if ($actual < $expectedLower->sub($delta) ||
            $actual > $expectedUpper->add($delta)) {
            throw new ComparisonFailure(
                $expected,
                $actual,
                $this->dateTimeToString($expected),
                $this->dateTimeToString($actual),
                false,
                'Failed asserting that two DateTime objects are equal.'
            );
        }
    }

    /**
     * Returns an ISO 8601 formatted string representation of a datetime or
     * 'Invalid DateTimeInterface object' if the provided DateTimeInterface was not properly
     * initialized.
     *
     * @param  \DateTimeInterface $datetime
     * @return string
     */
    protected function dateTimeToString($datetime)
    {
        $string = $datetime->format(\DateTime::ISO8601);

        return $string ? $string : 'Invalid DateTimeInterface object';
    }
}
