<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Authorization\Model\Acl\Loader;

use Magento\Authorization\Model\Acl\Role\Group as RoleGroup;
use Magento\Authorization\Model\Acl\Role\GroupFactory;
use Magento\Authorization\Model\Acl\Role\User as RoleUser;
use Magento\Authorization\Model\Acl\Role\UserFactory;
use Magento\Framework\Acl\Data\CacheInterface;
use Magento\Framework\Acl\LoaderInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Acl Role Loader
 */
class Role implements LoaderInterface
{
    /**
     * Cache key for ACL roles cache
     */
    const ACL_ROLES_CACHE_KEY = 'authorization_role_cached_data';

    /**
     * @var ResourceConnection
     */
    protected $_resource;

    /**
     * @var GroupFactory
     */
    protected $_groupFactory;

    /**
     * @var UserFactory
     */
    protected $_roleFactory;

    /**
     * @var CacheInterface
     */
    private $aclDataCache;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @var string
     */
    private $cacheKey;

    /**
     * @param GroupFactory $groupFactory
     * @param UserFactory $roleFactory
     * @param ResourceConnection $resource
     * @param CacheInterface $aclDataCache
     * @param Json $serializer
     * @param string $cacheKey
     */
    public function __construct(
        GroupFactory $groupFactory,
        UserFactory $roleFactory,
        ResourceConnection $resource,
        CacheInterface $aclDataCache,
        Json $serializer,
        $cacheKey = self::ACL_ROLES_CACHE_KEY
    ) {
        $this->_groupFactory = $groupFactory;
        $this->_roleFactory = $roleFactory;
        $this->_resource = $resource;
        $this->aclDataCache = $aclDataCache;
        $this->serializer = $serializer;
        $this->cacheKey = $cacheKey;
    }

    /**
     * Populate ACL with roles from external storage
     *
     * @param \Magento\Framework\Acl $acl
     * @return void
     */
    public function populateAcl(\Magento\Framework\Acl $acl)
    {
        foreach ($this->getRolesArray() as $role) {
            $parent = $role['parent_id'] > 0 ? $role['parent_id'] : null;
            switch ($role['role_type']) {
                case RoleGroup::ROLE_TYPE:
                    $acl->addRole($this->_groupFactory->create(['roleId' => $role['role_id']]), $parent);
                    break;

                case RoleUser::ROLE_TYPE:
                    if (!$acl->hasRole($role['role_id'])) {
                        $acl->addRole($this->_roleFactory->create(['roleId' => $role['role_id']]), $parent);
                    } else {
                        $acl->addRoleParent($role['role_id'], $parent);
                    }
                    break;
            }
        }
    }

    /**
     * Get application ACL roles array
     *
     * @return array
     */
    private function getRolesArray()
    {
        $rolesCachedData = $this->aclDataCache->load($this->cacheKey);
        if ($rolesCachedData) {
            return $this->serializer->unserialize($rolesCachedData);
        }

        $roleTableName = $this->_resource->getTableName('authorization_role');
        $connection = $this->_resource->getConnection();

        $select = $connection->select()
            ->from($roleTableName)
            ->order('tree_level');

        $rolesArray = $connection->fetchAll($select);
        $this->aclDataCache->save($this->serializer->serialize($rolesArray), $this->cacheKey);
        return $rolesArray;
    }
}
