<?php

namespace League\CLImate\Decorator\Component;

abstract class BaseDecorator implements DecoratorInterface
{
    /**
     * An array of defaults for the decorator
     *
     * @var array $defaults;
     */
    protected $defaults = [];

    /**
     * An array of currently set codes for the decorator
     *
     * @var array $current;
     */
    protected $current  = [];

    public function __construct()
    {
        $this->defaults();
    }

    /**
     * Load up the defaults for this decorator
     */
    public function defaults()
    {
        foreach ($this->defaults as $name => $code) {
            $this->add($name, $code);
        }
    }

    /**
     * Reset the currently set decorator
     */
    public function reset()
    {
        $this->current = [];
    }

    /**
     * Retrieve the currently set codes for the decorator
     *
     * @return array
     */
    public function current()
    {
        return $this->current;
    }
}
