<?php

namespace League\CLImate\TerminalObject\Basic;

/**
 * Tab class to enable tabs to be output without using the escape character.
 */
class Tab extends Repeatable
{

    /**
     * Check if this object requires a new line should be added after the output.
     *
     * @return boolean
     */
    public function sameLine()
    {
        return true;
    }

    /**
     * Return the relevant number of tab characters.
     *
     * @return string
     */
    public function result()
    {
        return str_repeat("\t", $this->count);
    }
}
