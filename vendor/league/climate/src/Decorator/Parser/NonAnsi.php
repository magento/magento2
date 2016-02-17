<?php

namespace League\CLImate\Decorator\Parser;

class NonAnsi extends Parser
{
    /**
     * Strip the string of any tags
     *
     * @param  string $str
     *
     * @return string
     */

    public function apply($str)
    {
        return preg_replace($this->tags->regex(), '', $str);
    }
}
