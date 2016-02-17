<?php

namespace League\CLImate\Decorator\Component;

class Command extends BaseDecorator
{

    /**
     * Commands that correspond to a color in the $colors property
     *
     * @var array
     */
    public $commands = [];

    /**
     * The default commands available
     *
     * @var array $defaults
     */
    protected $defaults = [
            'info'    => 'green',
            'comment' => 'yellow',
            'whisper' => 'light_gray',
            'shout'   => 'red',
            'error'   => 'light_red',
        ];

    /**
     * Add a command into the mix
     *
     * @param string $key
     * @param mixed  $value
     */
    public function add($key, $value)
    {
        $this->commands[$key] = $value;
    }

    /**
     * Retrieve all of the available commands
     *
     * @return array
     */
    public function all()
    {
        return $this->commands;
    }

    /**
     * Get the style that corresponds to the command
     *
     * @param  string  $val
     *
     * @return string
     */
    public function get($val)
    {
        if (array_key_exists($val, $this->commands)) {
            return $this->commands[$val];
        }

        return null;
    }

    /**
     * Set the currently used command
     *
     * @param  string       $val
     *
     * @return string|false
     */
    public function set($val)
    {
        // Return the code because it is a string corresponding
        // to a property in another class
        return ($code = $this->get($val)) ? $code : false;
    }

}
