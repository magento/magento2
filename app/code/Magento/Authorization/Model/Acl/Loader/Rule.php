<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Authorization\Model\Acl\Loader;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Serialize\Serializer\Json;

class Rule implements \Magento\Framework\Acl\LoaderInterface
{
    /**
     * Rules array cache key
     */
    const ACL_RULE_CACHE_KEY = 'authorization_rule_cached_data';

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;

    /**
     * @var \Magento\Framework\Acl\RootResource
     */
    private $_rootResource;

    /**
     * @var \Magento\Framework\Config\CacheInterface
     */
    private $cache;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @param \Magento\Framework\Acl\RootResource $rootResource
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param array $data
     * @param \Magento\Framework\Config\CacheInterface $cache
     * @param Json $serializer
     * @SuppressWarnings(PHPMD.UnusedFormalParameter):
     */
    public function __construct(
        \Magento\Framework\Acl\RootResource $rootResource,
        \Magento\Framework\App\ResourceConnection $resource,
        array $data = [],
        \Magento\Framework\Config\CacheInterface $cache = null,
        Json $serializer = null
    ) {
        $this->_resource = $resource;
        $this->_rootResource = $rootResource;
        $this->cache = $cache ?: ObjectManager::getInstance()->get(\Magento\Framework\Config\CacheInterface::class);
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
    }

    /**
     * Populate ACL with rules from external storage
     *
     * @param \Magento\Framework\Acl $acl
     * @return void
     */
    public function populateAcl(\Magento\Framework\Acl $acl)
    {
        foreach ($this->getRulesArray() as $rule) {
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

    /**
     * Get application ACL rules array.
     *
     * @return array
     */
    private function getRulesArray()
    {
 	    $rulesArr = $this->cache->load(self::ACL_RULE_CACHE_KEY);
 	    if ($rulesArr) {
 	        return $this->serializer->unserialize($rulesArr);
 	    }

 	    $ruleTable = $this->_resource->getTableName("authorization_rule");
 	    $connection = $this->_resource->getConnection();
 	    $select = $connection->select()
            ->from(['r' => $ruleTable]);

 	    $rulesArr = $connection->fetchAll($select);

        $this->cache->save($this->serializer->serialize($rulesArr), self::ACL_RULE_CACHE_KEY, ['acl_cache']);

        return $rulesArr;
 	}
}
