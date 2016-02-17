<?php

namespace League\CLImate\Util\System;

class Linux extends System
{
    /**
     * Get the width of the terminal
     *
     * @return integer|null
     */
    public function width()
    {
        return $this->getDimension($this->exec('tput cols'));
    }

    /**
     * Get the height of the terminal
     *
     * @return integer|null
     */
    public function height()
    {
        return $this->getDimension($this->exec('tput lines'));
    }

    /**
     * Determine if dimension is numeric and return it
     *
     * @param integer|string|null $dimension
     *
     * @return integer|null
     */
    protected function getDimension($dimension)
    {
        return (is_numeric($dimension)) ? $dimension : null;
    }

    /**
     * Check if the stream supports ansi escape characters.
     *
     * Based on https://github.com/symfony/symfony/blob/master/src/Symfony/Component/Console/Output/StreamOutput.php
     *
     * @return bool
     */
    protected function systemHasAnsiSupport()
    {
        return (function_exists('posix_isatty') && @posix_isatty(STDOUT));
    }
}
