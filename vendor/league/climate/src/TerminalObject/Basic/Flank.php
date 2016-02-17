<?php

namespace League\CLImate\TerminalObject\Basic;

class Flank extends BasicTerminalObject
{
    /**
     * The string that will be flanked
     *
     * @var string $str
     */
    protected $str;

    /**
     * The character(s) to repeat on either side of the string
     *
     * @var string $char
     */
    protected $char = '#';

    /**
     * How many times the character(s) should be repeated on either side
     *
     * @var integer $repeat
     */
    protected $repeat = 3;

    public function __construct($str, $char = null, $repeat = null)
    {
        $this->str = $str;

        $this->char($char)->repeat($repeat);
    }

    /**
     * Set the character(s) to repeat on either side
     *
     * @param string $char
     *
     * @return Flank
     */
    public function char($char)
    {
        $this->set('char', $char);

        return $this;
    }

    /**
     * Set the repeat of the flank character(s)
     *
     * @param integer $repeat
     *
     * @return Flank
     */
    public function repeat($repeat)
    {
        $this->set('repeat', $repeat);

        return $this;
    }

    /**
     * Return the flanked string
     *
     * @return string
     */
    public function result()
    {
        $flank = str_repeat($this->char, $this->repeat);

        return "{$flank} {$this->str} {$flank}";
    }
}
