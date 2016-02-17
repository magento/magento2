<?php

namespace League\CLImate\Util;

use League\CLImate\Util\Writer\WriterInterface;

class Output
{
    /**
     * The content to be output
     *
     * @var string $content
     */
    protected $content;

    /**
     * Whether or not to add a new line after the output
     *
     * @var boolean $new_line
     */
    protected $new_line = true;

    /**
     * Instance of a WriterInterface implementation
     *
     * @var \League\CLImate\Util\Writer\WriterInterface
     */
    protected $writer;

    public function __construct(WriterInterface $writer = null)
    {
        $this->writer = $writer ?: new Writer\StdOut();
    }

    /**
     * Dictate that a new line should not be added after the output
     */
    public function sameLine()
    {
        $this->new_line = false;

        return $this;
    }

    /**
     * Write the content using the provided writer
     *
     * @param  string $content
     */
    public function write($content)
    {
        if ($this->new_line) {
            $content .= PHP_EOL;
        }

        $this->writer->write($content);

        // Reset new line flag for next time
        $this->new_line = true;
    }

}
