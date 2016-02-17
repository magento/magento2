<?php

namespace League\CLImate\Decorator\Parser;

use League\CLImate\Decorator\Tags;

abstract class Parser
{
    /**
     * An array of the currently applied codes
     *
     * @var array $current;
     */
    protected $current = [];

    /**
     * An array of the tags that should be searched for
     * and their corresponding replacements
     *
     * @var \League\CLImate\Decorator\Tags $tags
     */
    public $tags;

    public function __construct(array $current, Tags $tags)
    {
        $this->current = $current;
        $this->tags    = $tags;
    }

    /**
     * Wrap the string in the current style
     *
     * @param  string $str
     *
     * @return string
     */
    abstract public function apply($str);
}
