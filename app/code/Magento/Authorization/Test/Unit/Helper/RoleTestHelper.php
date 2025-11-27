<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Authorization\Test\Unit\Helper;

use Magento\Authorization\Model\Role;

/**
 * Test helper for Magento\Authorization\Model\Role
 *
 * This helper provides only the custom GWS-related methods that are not available
 * in the parent Role class. Standard methods like getData(), setData(), and load()
 * are inherited from Magento\Framework\Model\AbstractModel.
 */
class RoleTestHelper extends Role
{
    /**
     * @var array
     */
    private $gwsWebsites = [];

    /**
     * @var array
     */
    private $gwsStoreGroups = [];

    /**
     * @var bool
     */
    private $gwsDataIsset = false;

    /**
     * Skip parent constructor to avoid dependency injection requirements in tests
     */
    public function __construct()
    {
        // Intentionally empty - avoids parent constructor dependencies
    }

    /**
     * Get GWS websites
     *
     * @return array
     */
    public function getGwsWebsites()
    {
        return $this->gwsWebsites;
    }

    /**
     * Set GWS websites
     *
     * @param array $websites
     * @return $this
     */
    public function setGwsWebsites($websites)
    {
        $this->gwsWebsites = $websites;
        return $this;
    }

    /**
     * Get GWS store groups
     *
     * @return array
     */
    public function getGwsStoreGroups()
    {
        return $this->gwsStoreGroups;
    }

    /**
     * Set GWS store groups
     *
     * @param array $storeGroups
     * @return $this
     */
    public function setGwsStoreGroups($storeGroups)
    {
        $this->gwsStoreGroups = $storeGroups;
        return $this;
    }

    /**
     * Set GWS data isset flag
     *
     * @param bool $value
     * @return $this
     */
    public function setGwsDataIsset($value)
    {
        $this->gwsDataIsset = $value;
        return $this;
    }

    /**
     * Load role (overridden to avoid database access in tests)
     *
     * @param mixed $modelId
     * @param string|null $field
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function load($modelId, $field = null)
    {
        return $this;
    }
}
