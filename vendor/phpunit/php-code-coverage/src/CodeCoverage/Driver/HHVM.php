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
 * Driver for HHVM's code coverage functionality.
 *
 * @since Class available since Release 2.2.2
 * @codeCoverageIgnore
 */
class PHP_CodeCoverage_Driver_HHVM extends PHP_CodeCoverage_Driver_Xdebug
{
    /**
     * Start collection of code coverage information.
     */
    public function start()
    {
        xdebug_start_code_coverage();
    }
}
