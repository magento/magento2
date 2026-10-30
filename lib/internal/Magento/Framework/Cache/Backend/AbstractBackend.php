<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Backend;

/**
 * Abstract cache backend base class
 *
 * Provides common functionality for all backend implementations.
 */
abstract class AbstractBackend implements BackendInterface
{
    /**
     * Backend options
     *
     * @var array
     */
    protected $_options = [];

    /**
     * Frontend or Core object
     *
     * @var object|null
     */
    protected $_frontend = null;

    /**
     * Available options with default values
     *
     * Subclasses should override this with their specific options
     *
     * @var array
     */
    protected $_availableOptions = [
        'cache_dir' => null,
        'file_locking' => true,
        'read_control' => true,
        'read_control_type' => 'crc32',
        'hashed_directory_level' => 0,
        'hashed_directory_umask' => 0700,
        'file_name_prefix' => 'mage',
        'cache_file_umask' => 0600,
        'metadatas_array_max_size' => 100,
    ];

    /**
     * Directives
     *
     * Cumulative since 1.7:
     * - (int) lifetime: cache lifetime (in seconds), null => infinite lifetime
     * - (int) priority: integer between 0 (very low priority) and 10 (maximum priority)
     *   used by some particular backends
     * - (boolean) logging: if set to true, a logging is done through the frontend->log() method
     *
     * @var array
     */
    protected $_directives = [
        'lifetime' => 3600,
        'priority' => 8,
        'logging' => false,
        'logger' => null,
        'ignore_user_abort' => false,
    ];

    /**
     * Constructor
     *
     * @param array $options Associative array of options
     */
    public function __construct(array $options = [])
    {
        // Merge available options with directives
        $availableOptions = array_merge($this->_availableOptions, $this->_directives);

        // Set default values
        foreach ($availableOptions as $name => $value) {
            $this->_options[$name] = $value;
        }

        // Override with provided options
        foreach ($options as $name => $value) {
            $this->setOption($name, $value);
        }
    }

    /**
     * Set the frontend directives
     *
     * @param array $directives Assoc of directives
     * @return void
     */
    public function setDirectives($directives)
    {
        foreach ($directives as $name => $value) {
            if (array_key_exists($name, $this->_directives)) {
                $this->_directives[$name] = $value;
                $this->_options[$name] = $value;
            }
        }
    }

    /**
     * Set an option
     *
     * @param string $name Option name
     * @param mixed $value Option value
     * @return void
     */
    public function setOption($name, $value)
    {
        $this->_options[$name] = $value;
    }

    /**
     * Get an option value
     *
     * @param string $name Option name
     * @return mixed Option value or null if not set
     */
    public function getOption($name)
    {
        return $this->_options[$name] ?? null;
    }

    /**
     * Set the frontend
     *
     * @param object $frontend Frontend object
     * @return void
     */
    public function setFrontend($frontend)
    {
        $this->_frontend = $frontend;
    }

    /**
     * Get the frontend
     *
     * @return object|null
     */
    public function getFrontend()
    {
        return $this->_frontend;
    }

    /**
     * Get the lifetime
     *
     * @param int|null $specificLifetime Specific lifetime
     * @return int Lifetime in seconds
     */
    protected function getLifetime(?int $specificLifetime = null): int
    {
        if ($specificLifetime === null) {
            return (int)$this->_directives['lifetime'];
        }

        if ($specificLifetime === 0) {
            return 0;
        }

        return (int)$specificLifetime;
    }

    /**
     * Log a message if logging is enabled
     *
     * @param string $message Message to log
     * @param int $priority Priority level
     * @return void
     */
    protected function log(string $message, int $priority = 4): void
    {
        if ($this->_options['logging']) {
            if ($this->_options['logger']) {
                $this->_options['logger']->log($message, $priority);
            } elseif ($this->_frontend && method_exists($this->_frontend, 'log')) {
                $this->_frontend->log($message, $priority);
            }
        }
    }
}
