<?php

namespace League\CLImate\TerminalObject\Basic;

class Out extends BasicTerminalObject
{
    /**
     * The content to output
     *
     * @var string $content
     */
    protected $content;

    public function __construct($content)
    {
        $this->content = $content;
    }

    /**
     * Return the content to output
     *
     * @return string
     */
    public function result()
    {
        return $this->content;
    }
}
