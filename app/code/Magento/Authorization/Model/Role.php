<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Authorization\Model;

use Magento\Authorization\Model\ResourceModel\Role\Collection;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Model\AbstractModel;

/**
 * Admin Role Model
 *
 * @api
 * @method int getParentId()
 * @method Role setParentId(int $value)
 * @method int getTreeLevel()
 * @method Role setTreeLevel(int $value)
 * @method int getSortOrder()
 * @method Role setSortOrder(int $value)
 * @method string getRoleType()
 * @method Role setRoleType(string $value)
 * @method int getUserId()
 * @method Role setUserId(int $value)
 * @method string getUserType()
 * @method Role setUserType(string $value)
 * @method string getRoleName()
 * @method Role setRoleName(string $value)
 * @api
 * @since 100.0.2
 */
class Role extends AbstractModel
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'authorization_roles';

    /**
     * @var string
     */
    protected $_cacheTag = 'user_assigned_role';

    /**
     * @inheritDoc
     */
    public function __sleep()
    {
        $properties = parent::__sleep();
        return array_diff($properties, ['_resource', '_resourceCollection']);
    }

    /**
     * @inheritDoc
     */
    public function __wakeup()
    {
        parent::__wakeup();
        $objectManager = ObjectManager::getInstance();
        $this->_resource = $objectManager->get(ResourceModel\Role::class);
        $this->_resourceCollection = $objectManager->get(Collection::class);
    }

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\Role::class);
    }

    /**
     * Obsolete method of update
     *
     * @return $this
     * @deprecated Method was never implemented and used.
     */
    public function update()
    {
        // phpcs:disable Magento2.Functions.DiscouragedFunction
        trigger_error('Method was never implemented and used.', E_USER_DEPRECATED);

        return $this;
    }

    /**
     * Return users for role
     *
     * @return array
     */
    public function getRoleUsers()
    {
        return $this->getResource()->getRoleUsers($this);
    }
}
