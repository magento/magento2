<?php

namespace League\CLImate\TerminalObject\Dynamic;

class Padding extends DynamicTerminalObject
{
    /**
     * The length that lines should be padded too
     *
     * @var integer $length
     */
    protected $length = 0;

    /**
     * The character(s) that should be used to pad
     *
     * @var string $char
     */
    protected $char = '.';


    /**
     * If they pass in a padding character, set the char
     *
     * @param string $char
     */
    public function __construct($length = null, $char = null)
    {
        if ($length) {
            $this->length($length);
        }

        if (is_string($char)) {
            $this->char($char);
        }
    }

    /**
     * Set the character(s) that should be used to pad
     *
     * @param string $char
     *
     * @return \League\CLImate\TerminalObject\Dynamic\Padding
     */
    public function char($char)
    {
        $this->char = $char;

        return $this;
    }

    /**
     * Set the length of the line that should be generated
     *
     * @param integer $length
     *
     * @return \League\CLImate\TerminalObject\Dynamic\Padding
     */
    public function length($length)
    {
        $this->length = $length;

        return $this;
    }

    /**
     * Get the length of the line based on the width of the terminal window
     *
     * @return integer
     */
    protected function getLength()
    {
        if (!$this->length) {
            $this->length = $this->util->width();
        }

        return $this->length;
    }

    /**
     * Pad the content with the characters
     *
     * @param string $content
     *
     * @return string
     */
    protected function padContent($content)
    {
        if (strlen($this->char) > 0) {
            $length = $this->getLength();
            $padding_length = ceil($length / strlen($this->char));

            $padding = str_repeat($this->char, $padding_length);
            $content .= substr($padding, 0, $length - strlen($content));
        }

        return $content;
    }

    /**
     * Output the content and pad to the previously defined length
     *
     * @param string $content
     *
     * @return \League\CLImate\TerminalObject\Dynamic\Padding
     */
    public function label($content)
    {
        // Handle long labels by splitting them across several lines
        $lines   = str_split($content, $this->util->width());
        $content = array_pop($lines);

        foreach ($lines as $line) {
            $this->output->write($line);
        }

        $content = $this->padContent($content);

        $this->output->sameLine();
        $this->output->write($content);

        return $this;
    }

    /**
     * Output result
     *
     * @param string $content
     */
    public function result($content)
    {
        $this->output->write(' ' . $content);
    }
}
