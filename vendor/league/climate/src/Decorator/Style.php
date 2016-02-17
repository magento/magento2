<?php

namespace League\CLImate\Decorator;

use League\CLImate\Decorator\Parser\ParserFactory;
use League\CLImate\Util\System\System;

/**
 * @method void addColor(string $color, integer $code)
 * @method void addFormat(string $format, integer $code)
 * @method void addCommand(string $command, mixed $style)
 */
class Style
{

    /**
     * An array of Decorator objects
     *
     * @var array $style
     */
    protected $style = [];

    /**
     * An array of the available Decorators
     * and their corresponding class names
     *
     * @var array $available
     */
    protected $available = [
        'format'     =>  'Format',
        'color'      =>  'Color',
        'background' =>  'BackgroundColor',
        'command'    =>  'Command',
    ];

    protected $parser;

    /**
     * An array of the current styles applied
     *
     * @var array $current
     */
    protected $current = [];

    public function __construct()
    {
        foreach ($this->available as $key => $class) {
            $class = 'League\CLImate\Decorator\Component\\' . $class;
            $this->style[$key] = new $class();
        }
    }

    /**
     * Get all of the styles available
     *
     * @return array
     */
    public function all()
    {
        $all = [];

        foreach ($this->style as $style) {
            $all = array_merge($all, $this->convertToCodes($style->all()));
        }

        return $all;
    }

    /**
     * Attempt to get the corresponding code for the style
     *
     * @param  mixed $key
     *
     * @return mixed
     */
    public function get($key)
    {
        foreach ($this->style as $style) {
            if ($code = $style->get($key)) {
                return $code;
            }
        }

        return false;
    }

    /**
     * Attempt to set some aspect of the styling,
     * return true if attempt was successful
     *
     * @param  string   $key
     *
     * @return boolean
     */
    public function set($key)
    {
        foreach ($this->style as $style) {
            if ($code = $style->set($key)) {
                return $this->validateCode($code);
            }
        }

        return false;
    }

    /**
     * Reset the current styles applied
     *
     */
    public function reset()
    {
        foreach ($this->style as $style) {
            $style->reset();
        }
    }

    /**
     * Get a new instance of the Parser class based on the current settings
     *
     * @param \League\CLImate\Util\System\System $system
     *
     * @return Parser
     */
    public function parser(System $system)
    {
        return ParserFactory::getInstance($system, $this->current(), new Tags($this->all()));
    }

    /**
     * Compile an array of the current codes
     *
     * @return array
     */
    public function current()
    {
        $full_current = [];

        foreach ($this->style as $style) {
            $current = $style->current();

            if (!is_array($current)) {
                $current = [$current];
            }

            $full_current = array_merge($full_current, $current);
        }

        $full_current = array_filter($full_current);

        return array_values($full_current);
    }

    /**
     * Make sure that the code is an integer, if not let's try and get it there
     *
     * @param mixed $code
     *
     * @return boolean
     */
    protected function validateCode($code)
    {
        if (is_integer($code)) {
            return true;
        }

        // Plug it back in and see what we get
        if (is_string($code)) {
            return $this->set($code);
        }

        if (is_array($code)) {
            return $this->validateCodeArray($code);
        }

        return false;
    }

    /**
     * Validate an array of codes
     *
     * @param array $codes
     *
     * @return boolean
     */
    protected function validateCodeArray(array $codes)
    {
        // Loop through it and add each of the properties
        $adds = [];

        foreach ($codes as $code) {
            $adds[] = $this->set($code);
        }

        // If any of them came back true, we're good to go
        return in_array(true, $adds);
    }

    /**
     * Convert the array of codes to integers
     *
     * @param array $codes
     */
    protected function convertToCodes(array $codes)
    {
        foreach ($codes as $key => $code) {
            if (is_int($code)) {
                continue;
            }

            $codes[$key] = $this->getCode($code);
        }

        return $codes;
    }

    /**
     * Retrieve the integers from the mixed code input
     *
     * @param string|array $code
     *
     * @return integer|array
     */
    protected function getCode($code)
    {
        if (is_array($code)) {
            return $this->getCodeArray($code);
        }

        return $this->get($code);
    }

    /**
     * Retrieve an array of integers from the array of codes
     *
     * @param array $codes
     *
     * @return array
     */
    protected function getCodeArray(array $codes)
    {
        foreach ($codes as $key => $code) {
            $codes[$key] = $this->get($code);
        }

        return $codes;
    }

    /**
     * Parse the add method for the style they are trying to add
     *
     * @param string $method
     *
     * @return string
     */
    protected function parseAddMethod($method)
    {
        return strtolower(substr($method, 3, strlen($method)));
    }

    /**
     * Add a custom style
     *
     * @param string $style
     * @param string $key
     * @param string $value
     */
    protected function add($style, $key, $value)
    {
        $this->style[$style]->add($key, $value);

        // If we are adding a color, make sure it gets added
        // as a background color too
        if ($style == 'color') {
            $this->style['background']->add($key, $value);
        }
    }

    /**
     * Magic Methods
     *
     * List of possible magic methods are at the top of this class
     *
     * @param string $requested_method
     * @param array  $arguments
     */
    public function __call($requested_method, $arguments)
    {
        // The only methods we are concerned about are 'add' methods
        if (substr($requested_method, 0, 3) != 'add') {
            return false;
        }

        $style = $this->parseAddMethod($requested_method);

        if (array_key_exists($style, $this->style)) {
            list($key, $value) = $arguments;
            $this->add($style, $key, $value);
        }
    }

}
