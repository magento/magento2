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
 * Compares values for type equality.
 */
class TypeComparator extends Comparator
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
        return true;
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
        if (gettype($expected) != gettype($actual)) {
            throw new ComparisonFailure(
                $expected,
                $actual,
                // we don't need a diff
                '',
                '',
                false,
                sprintf(
                    '%s does not match expected type "%s".',
                    $this->exporter->shortenedExport($actual),
                    gettype($expected)
                )
            );
        }
    }
}
