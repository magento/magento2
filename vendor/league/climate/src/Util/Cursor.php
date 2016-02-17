<?php

namespace League\CLImate\Util;

class Cursor
{
    /**
     * Move the cursor up in the terminal x number of lines.
     *
     * @param int $number_of_lines
     *
     * @return string
     */
    public function up($number_of_lines = 1)
    {
        return "\e[{$number_of_lines}A";
    }

    /**
     * Move cursor to the beginning of the current line.
     *
     * @return string
     */
    public function startOfCurrentLine()
    {
        return "\r";
    }

    /**
     * Delete the current line to the end.
     *
     * @return string
     */
    public function deleteCurrentLine()
    {
        return "\e[K";
    }
}
