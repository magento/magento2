<?php

namespace Liip\CustomerHierarchy\Model;

use \Magento\Framework\Model\AbstractModel;

class Role extends AbstractModel
{
    /**
     * @var Role\Permission\Pool
     */
    private $rolePermissionPool;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Liip\CustomerHierarchy\Model\Role\Permission\PoolInterface $rolePermissionPool,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->rolePermissionPool = $rolePermissionPool;
    }


    protected function _construct()
    {
        $this->_init(ResourceModel\Role::class);
    }

    public function afterLoad()
    {
        $storedPermissions = $this->_getResource()->getPermissionsByRoleId($this->getId());

        $permissions = [];

        foreach ($this->rolePermissionPool->getAll() as $rolePermission) {
            $permission = [
                'type' => $rolePermission->getType(),
                'code' => $rolePermission->getCode(),
                'label' => $rolePermission->getLabel(),
            ];

            $permissionValue = null;

            foreach ($storedPermissions as $storedPermissionData) {
                if ($rolePermission->getCode() == $storedPermissionData['code']) {
                    $permissionValue = ('boolean' == $rolePermission->getType())
                        ? (bool)$storedPermissionData['value']
                        : $storedPermissionData['value'];
                }
            }
            $permission['value'] = $permissionValue;
            $permissions[] = $permission;
        }

        $this->setPermissions($permissions);
        return parent::afterLoad();
    }
}
