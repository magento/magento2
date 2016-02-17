<?php

namespace League\CLImate\Settings;

class Art implements SettingsInterface
{
    /**
     * An array of valid art directories
     *  @var array $dirs
     */
    public $dirs = [];

    /**
     * Add directories of art
     */
    public function add()
    {
        $this->dirs = array_merge($this->dirs, func_get_args());
        $this->dirs = array_filter($this->dirs);
        $this->dirs = array_values($this->dirs);
    }

}
