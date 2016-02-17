<?php

namespace League\CLImate\TerminalObject\Basic;

class Border extends BasicTerminalObject
{
    /**
     * The character to repeat for the border
     *
     * @var string $char
     */
    protected $char = '-';

    /**
     * The length of the border
     *
     * @var integer $length
     */
    protected $length;

    public function __construct($char = null, $length = null)
    {
        $this->char($char)->length($length);
    }

    /**
     * Set the character to repeat for the border
     *
     * @param string $char
     *
     * @return Border
     */
    public function char($char)
    {
        $this->set('char', $char);

        return $this;
    }

    /**
     * Set the length of the border
     *
     * @param integer $length
     *
     * @return Border
     */
    public function length($length)
    {
        $this->set('length', $length);

        return $this;
    }

    /**
     * Return the border
     *
     * @return string
     */
    public function result()
    {
        $length = $this->length ?: $this->util->width() ?: 100;
        $str    = str_repeat($this->char, $length);
        $str    = substr($str, 0, $length);

        return $str;
    }
}
