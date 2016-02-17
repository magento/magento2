<?php

namespace League\CLImate\Settings;

class Manager
{
    /**
     * An array of settings that have been... set
     *
     * @var array $settings
     */
    protected $settings = [];

    /**
     * Check and see if the requested setting is a valid, registered setting
     *
     * @param  string  $name
     *
     * @return boolean
     */
    public function exists($name)
    {
        return class_exists($this->getPath($name));
    }

    /**
     * Add a setting
     *
     * @param string $name
     * @param mixed  $value
     */
    public function add($name, $value)
    {
        $setting = $this->getPath($name);
        $key     = $this->getClassName($name);

        // If the current key doesn't exist in the settings array, set it up
        if (!array_key_exists($name, $this->settings)) {
            $this->settings[$key] = new $setting();
        }

        $this->settings[$key]->add($value);
    }

    /**
     * Get the value of the requested setting if it exists
     *
     * @param  string $key
     *
     * @return mixed
     */
    public function get($key)
    {
        if (array_key_exists($key, $this->settings)) {
            return $this->settings[$key];
        }

        return false;
    }

    /**
     * Get the short name for the requested settings class
     *
     * @param  string $name
     *
     * @return string
     */
    protected function getPath($name)
    {
        return '\\League\CLImate\\Settings\\' . $this->getClassName($name);
    }

    /**
     * Get the short class name for the setting
     *
     * @param  string $name
     *
     * @return string
     */
    protected function getClassName($name)
    {
        return ucwords(str_replace('add_', '', $name));
    }

}
