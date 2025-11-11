<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Session\Test\Unit\Helper;

use Magento\Framework\Session\StorageInterface;

/**
 * Test helper for creating StorageInterface mocks
 *
 * This helper implements StorageInterface and provides a simple
 * data storage mechanism for testing purposes.
 *
 * WHY THIS HELPER IS REQUIRED:
 * - Concrete Storage class accesses global $_SESSION (line 51: $_SESSION[$namespace] = & $this->_data;)
 * - This creates side effects and dependencies unsuitable for unit tests
 * - StorageTestHelper provides clean, isolated storage without $_SESSION access
 * - Used by SessionTestHelper for constructor initialization
 * - Provides __call magic method for dynamic get/set/uns/has operations
 *
 * Cannot be replaced with concrete Storage class due to $_SESSION dependency.
 */
class StorageTestHelper implements StorageInterface
{
    /**
     * @var array
     */
    private $data = [];
    
    /**
     * Initialize storage data
     *
     * @param array $data
     * @return $this
     */
    public function init(array $data)
    {
        $this->data = $data;
        return $this;
    }
    
    /**
     * Get current storage namespace
     *
     * @return string
     */
    public function getNamespace()
    {
        return 'test';
    }
    
    /**
     * Magic method to handle dynamic method calls
     *
     * Supports get*, set*, uns*, and has* methods for data manipulation
     *
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        if (strpos($method, 'get') === 0) {
            $key = lcfirst(substr($method, 3));
            return $this->data[$key] ?? null;
        } elseif (strpos($method, 'set') === 0) {
            $key = lcfirst(substr($method, 3));
            $this->data[$key] = $args[0] ?? null;
            return $this;
        } elseif (strpos($method, 'uns') === 0) {
            $key = lcfirst(substr($method, 3));
            unset($this->data[$key]);
            return $this;
        } elseif (strpos($method, 'has') === 0) {
            $key = lcfirst(substr($method, 3));
            return isset($this->data[$key]);
        }
        return null;
    }
}
