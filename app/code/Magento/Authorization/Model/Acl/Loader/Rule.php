<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Authorization\Model\Acl\Loader;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class \Magento\Authorization\Model\Acl\Loader\Rule
 *
 * @since 2.0.0
 */
class Rule implements \Magento\Framework\Acl\LoaderInterface
{
    /**
     * Rules array cache key
     */
    const ACL_RULE_CACHE_KEY = 'authorization_rule_cached_data';

    /**
     * @var \Magento\Framework\App\ResourceConnection
     * @since 2.0.0
     */
    protected $_resource;

    /**
     * @var \Magento\Framework\Acl\RootResource
     * @since 2.2.0
     */
    private $_rootResource;

    /**
     * @var \Magento\Framework\Acl\Data\CacheInterface
     * @since 2.2.0
     */
    private $aclDataCache;

    /**
     * @var Json
     * @since 2.2.0
     */
    private $serializer;

    /**
     * @var string
     * @since 2.2.0
     */
    private $cacheKey;

    /**
     * @param \Magento\Framework\Acl\RootResource $rootResource
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param array $data
     * @param \Magento\Framework\Acl\Data\CacheInterface $aclDataCache
     * @param Json $serializer
     * @param string $cacheKey
     * @SuppressWarnings(PHPMD.UnusedFormalParameter):
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Acl\RootResource $rootResource,
        \Magento\Framework\App\ResourceConnection $resource,
        array $data = [],
        \Magento\Framework\Acl\Data\CacheInterface $aclDataCache = null,
        Json $serializer = null,
        $cacheKey = self::ACL_RULE_CACHE_KEY
    ) {
        $this->_resource = $resource;
        $this->_rootResource = $rootResource;
        $this->aclDataCache = $aclDataCache ?: ObjectManager::getInstance()->get(
            \Magento\Framework\Acl\Data\CacheInterface::class
        );
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
        $this->cacheKey = $cacheKey;
    }

    /**
     * Populate ACL with rules from external storage
     *
     * @param \Magento\Framework\Acl $acl
     * @return void
     * @since 2.0.0
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
     * @since 2.2.0
     */
    private function getRulesArray()
    {
        $rulesCachedData = $this->aclDataCache->load($this->cacheKey);
        if ($rulesCachedData) {
            return $this->serializer->unserialize($rulesCachedData);
        }

        $ruleTable = $this->_resource->getTableName("authorization_rule");
        $connection = $this->_resource->getConnection();
        $select = $connection->select()
            ->from(['r' => $ruleTable]);

        $rulesArr = $connection->fetchAll($select);

        $this->aclDataCache->save($this->serializer->serialize($rulesArr), $this->cacheKey);

        return $rulesArr;
    }
}
