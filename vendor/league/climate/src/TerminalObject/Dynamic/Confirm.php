<?php

namespace League\CLImate\TerminalObject\Dynamic;

class Confirm extends Input
{
    /**
     * Let us know if the user confirmed
     *
     * @return boolean
     */
    public function confirmed()
    {
        $this->accept(['y', 'n'], true);
        $this->strict();

        return ($this->prompt() == 'y');
    }
}
