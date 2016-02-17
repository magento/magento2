<?php
/*
 * This file is part of the Environment package.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SebastianBergmann\Environment;

/**
 */
class Console
{
    const STDIN  = 0;
    const STDOUT = 1;
    const STDERR = 2;

    /**
     * Returns true if STDOUT supports colorization.
     *
     * This code has been copied and adapted from
     * Symfony\Component\Console\Output\OutputStream.
     *
     * @return bool
     */
    public function hasColorSupport()
    {
        if (DIRECTORY_SEPARATOR == '\\') {
            return false !== getenv('ANSICON') || 'ON' === getenv('ConEmuANSI') || 'xterm' === getenv('TERM');
        }

        if (!defined('STDOUT')) {
            return false;
        }

        return $this->isInteractive(STDOUT);
    }

    /**
     * Returns the number of columns of the terminal.
     *
     * @return int
     */
    public function getNumberOfColumns()
    {
        // Windows terminals have a fixed size of 80
        // but one column is used for the cursor.
        if (DIRECTORY_SEPARATOR == '\\') {
            return 79;
        }

        if (!$this->isInteractive(self::STDIN)) {
            return 80;
        }

        if (preg_match('#\d+ (\d+)#', shell_exec('stty size'), $match) === 1) {
            return (int) $match[1];
        }

        if (preg_match('#columns = (\d+);#', shell_exec('stty'), $match) === 1) {
            return (int) $match[1];
        }

        return 80;
    }

    /**
     * Returns if the file descriptor is an interactive terminal or not.
     *
     * @param int|resource $fileDescriptor
     *
     * @return bool
     */
    public function isInteractive($fileDescriptor = self::STDOUT)
    {
        return function_exists('posix_isatty') && @posix_isatty($fileDescriptor);
    }
}
