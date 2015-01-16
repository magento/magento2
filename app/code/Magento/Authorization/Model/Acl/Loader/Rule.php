<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Authorization\Model\Acl\Loader;

class Rule implements \Magento\Framework\Acl\LoaderInterface
{
    /**
     * @var \Magento\Framework\App\Resource
     */
    protected $_resource;

    /**
     * @param \Magento\Framework\Acl\RootResource $rootResource
     * @param \Magento\Framework\App\Resource $resource
     * @param array $data
     * @SuppressWarnings(PHPMD.UnusedFormalParameter):
     */
    public function __construct(
        \Magento\Framework\Acl\RootResource $rootResource,
        \Magento\Framework\App\Resource $resource,
        array $data = []
    ) {
        $this->_resource = $resource;
        $this->_rootResource = $rootResource;
    }

    /**
     * Populate ACL with rules from external storage
     *
     * @param \Magento\Framework\Acl $acl
     * @return void
     */
    public function populateAcl(\Magento\Framework\Acl $acl)
    {
        $ruleTable = $this->_resource->getTableName("authorization_rule");

        $adapter = $this->_resource->getConnection('core_read');

        $select = $adapter->select()->from(['r' => $ruleTable]);

        $rulesArr = $adapter->fetchAll($select);

        foreach ($rulesArr as $rule) {
            $role = $rule['role_id'];
            $resource = $rule['resource_id'];
            $privileges = !empty($rule['privileges']) ? explode(',', $rule['privileges']) : null;

            if ($acl->has($resource)) {
                if ($rule['permission'] == 'allow') {
                    if ($resource === $this->_rootResource->getId()) {
                        $acl->allow($role, null, $privileges);
                    }
                    $acl->allow($role, $resource, $privileges);
                } elseif ($rule['permission'] == 'deny') {
                    $acl->deny($role, $resource, $privileges);
                }
            }
        }
    }
}
