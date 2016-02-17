<?php

namespace League\CLImate\Decorator;

class Tags {

    /**
     * Original keys passed in to build tags
     *
     * @var array $tags
     */

    protected $keys = [];

    /**
     * Available tags and their values
     *
     * @var array $tags
     */

    protected $tags = [];

    public function __construct(array $keys)
    {
        $this->keys = $keys;
        $this->build();
    }

    /**
     * Get all available tags
     *
     * @return array
     */

    public function all()
    {
        return $this->tags;
    }

    /**
     * Get the value of the requested tag
     *
     * @param string $key
     *
     * @return string|null
     */

    public function value($key)
    {
        return (array_key_exists($key, $this->tags)) ? $this->tags[$key] : null;
    }

    /**
     * Get the regular expression that can be used to parse the string for tags
     *
     * @return string
     */

    public function regex()
    {
        return '(<(?:(?:(?:\\\)*\/)*(?:' . implode('|', array_keys($this->keys)) . '))>)';
    }

    /**
     * Build the search and replace for all of the various style tags
     */

    protected function build()
    {
        foreach ($this->keys as $tag => $code) {
            $this->tags["<{$tag}>"]    = $code;
            $this->tags["</{$tag}>"]   = $code;
            $this->tags["<\\/{$tag}>"] = $code;
        }
    }

}
