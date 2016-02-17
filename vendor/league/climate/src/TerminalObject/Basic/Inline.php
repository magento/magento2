<?php

namespace League\CLImate\TerminalObject\Basic;

class Inline extends Out
{
    /**
     * Check if this object requires a new line should be added after the output
     *
     * @return boolean
     */
    public function sameLine()
    {
        return true;
    }
}
