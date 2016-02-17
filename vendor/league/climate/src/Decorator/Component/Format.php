<?php

namespace League\CLImate\Decorator\Component;

class Format extends BaseDecorator
{

    /**
     * The available formatting options
     *
     * @var array
     */
    protected $formats = [];

    /**
     * An array of default formats
     *
     * @var array $defaults
     */
    protected $defaults = [
            'bold'          => 1,
            'dim'           => 2,
            'underline'     => 4,
            'blink'         => 5,
            'invert'        => 7,
            'hidden'        => 8,
        ];

    /**
     * Add a format into the mix
     *
     * @param string $key
     * @param mixed  $value
     */
    public function add($key, $value)
    {
        $this->formats[$key] = (int) $value;
    }

    /**
     * Retrieve all of the available formats
     *
     * @return array
     */
    public function all()
    {
        return $this->formats;
    }

    /**
     * Get the code for the format
     *
     * @param  string  $val
     *
     * @return string
     */
    public function get($val)
    {
        // If we already have the code, just return that
        if (is_numeric($val)) {
            return $val;
        }

        if (array_key_exists($val, $this->formats)) {
            return $this->formats[$val];
        }

        return null;
    }

    /**
     * Set the current format
     *
     * @param  string $val
     *
     * @return boolean
     */
    public function set($val)
    {
        $code = $this->get($val);

        if ($code) {
            $this->current[] = $code;

            return true;
        }

        return false;
    }

}
