<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Acl\Role;

use Magento\Framework\ObjectManager\ResetAfterRequestInterface;

/**
 * Holds the current role id during ACL building within a single request.
 */
class CurrentRoleContext implements ResetAfterRequestInterface
{
    /**
     * @var int|null
     */
    private $roleId = null;

    /**
     * Set the current role ID.
     *
     * @param int|null $roleId
     * @return void
     */
    public function setRoleId(int|null $roleId): void
    {
        $this->roleId = $roleId;
    }

    /**
     * Get the current role ID.
     *
     * @return int|null
     */
    public function getRoleId(): int|null
    {
        return $this->roleId;
    }

    /**
     * Reset the state after a request.
     *
     * @return void
     */
    public function _resetState(): void
    {
        $this->roleId = null;
    }
}
