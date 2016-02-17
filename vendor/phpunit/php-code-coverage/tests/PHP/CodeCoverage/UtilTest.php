<?php
/*
 * This file is part of the PHP_CodeCoverage package.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Tests for the PHP_CodeCoverage_Util class.
 *
 * @since Class available since Release 1.0.0
 */
class PHP_CodeCoverage_UtilTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers PHP_CodeCoverage_Util::percent
     */
    public function testPercent()
    {
        $this->assertEquals(100, PHP_CodeCoverage_Util::percent(100, 0));
        $this->assertEquals(100, PHP_CodeCoverage_Util::percent(100, 100));
        $this->assertEquals(
            '100.00%',
            PHP_CodeCoverage_Util::percent(100, 100, true)
        );
    }
}
