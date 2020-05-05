<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Authorization\Model\Acl\Loader;

use Magento\Framework\Acl\Data\CacheInterface;
use Magento\Framework\Acl\LoaderInterface;
use Magento\Framework\Acl\RootResource;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Acl Rule Loader
 */
class Rule implements LoaderInterface
{
    /**
     * Rules array cache key
     */
    const ACL_RULE_CACHE_KEY = 'authorization_rule_cached_data';

    /**
     * @var ResourceConnection
     */
    protected $_resource;

    /**
     * @var RootResource
     */
    private $_rootResource;

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
     * @param RootResource $rootResource
     * @param ResourceConnection $resource
     * @param CacheInterface $aclDataCache
     * @param Json $serializer
     * @param array $data
     * @param string $cacheKey
     * @SuppressWarnings(PHPMD.UnusedFormalParameter):
     */
    public function __construct(
        RootResource $rootResource,
        ResourceConnection $resource,
        CacheInterface $aclDataCache,
        Json $serializer,
        array $data = [],
        $cacheKey = self::ACL_RULE_CACHE_KEY
    ) {
        $this->_rootResource = $rootResource;
        $this->_resource = $resource;
        $this->aclDataCache = $aclDataCache;
        $this->serializer = $serializer;
        $this->cacheKey = $cacheKey;
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
        $rulesCachedData = $this->aclDataCache->load($this->cacheKey);
        if ($rulesCachedData) {
            return $this->serializer->unserialize($rulesCachedData);
        }

        $ruleTable = $this->_resource->getTableName('authorization_rule');
        $connection = $this->_resource->getConnection();
        $select = $connection->select()
            ->from(['r' => $ruleTable]);

        $rulesArr = $connection->fetchAll($select);

        $this->aclDataCache->save($this->serializer->serialize($rulesArr), $this->cacheKey);

        return $rulesArr;
    }
}
