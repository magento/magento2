<?php

namespace League\CLImate\Decorator\Parser;

trait ParserImporter
{
    /**
     * An instance of the Parser class
     *
     * @var \League\CLImate\Decorator\Parser\Parser $parser
     */
    protected $parser;

    /**
     * Import the parser and set the property
     *
     * @param \League\CLImate\Decorator\Parser\Parser $parser
     */
    public function parser(Parser $parser)
    {
        $this->parser = $parser;
    }
}
