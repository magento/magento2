<?php

namespace League\CLImate\TerminalObject\Basic;

class Clear extends BasicTerminalObject
{
    /**
     * Clear the terminal
     *
     * @return string
     */
    public function result()
    {
        return "\e[2J";
    }
}
