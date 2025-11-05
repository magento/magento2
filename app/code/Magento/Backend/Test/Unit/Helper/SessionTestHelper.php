<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\Helper;

use Magento\Backend\Model\Auth\Session;
use Magento\Framework\Session\Test\Unit\Helper\StorageTestHelper;
use Magento\User\Model\User;

/**
 * Test helper for creating Auth Session mocks with various methods
 *
 * This helper extends the concrete Auth\Session class directly, providing a clean
 * way to add test-specific methods without using anonymous classes.
 * Extends Auth\Session to be compatible with type hints requiring that specific class.
 *
 * METHODS PROVIDED:
 * - setUser() / getUser() - User management
 * - setIsLoggedIn() / isLoggedIn() - Login state management
 * - setSkipLoggingAction() - Logging control
 */
class SessionTestHelper extends Session
{
    /**
     * @var User|null
     */
    private $user = null;
    
    /**
     * @var bool
     */
    private $isLoggedIn = false;
    
    public function __construct()
    {
        // Skip parent constructor for testing
        // Initialize storage with the StorageTestHelper
        $this->storage = new StorageTestHelper();
    }
    
    /**
     * Get user
     *
     * @return User|null
     */
    public function getUser()
    {
        return $this->user;
    }
    
    /**
     * Set user
     *
     * @param User $user
     * @return $this
     */
    public function setUser($user)
    {
        $this->user = $user;
        $this->isLoggedIn = true;
        return $this;
    }
    
    /**
     * Set skip logging action
     *
     * @param mixed $value
     * @return $this
     */
    public function setSkipLoggingAction($value)
    {
        // Store in parent's storage using magic method (via StorageTestHelper::__call)
        // phpcs:ignore Magento2.Functions.StaticFunction
        /** @phpstan-ignore-next-line - StorageTestHelper::__call handles dynamic methods */
        $this->storage->setSkipLoggingAction($value);
        return $this;
    }
    
    /**
     * Check if user is logged in
     *
     * @return bool
     */
    public function isLoggedIn()
    {
        return $this->isLoggedIn;
    }
    
    /**
     * Set is logged in
     *
     * @param bool $value
     * @return $this
     */
    public function setIsLoggedIn($value)
    {
        $this->isLoggedIn = $value;
        return $this;
    }
}
