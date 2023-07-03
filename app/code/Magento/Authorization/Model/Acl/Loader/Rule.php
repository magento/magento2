<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Authorization\Model\Acl\Loader;

use Magento\Framework\Acl;
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
    public const ACL_RULE_CACHE_KEY = 'authorization_rule_cached_data';

    /**
     * Allow everything resource id
     */
    private const ALLOW_EVERYTHING = 'Magento_Backend::all';

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
     * @param Acl $acl
     * @return void
     */
    public function populateAcl(Acl $acl)
    {
        $result = $this->applyPermissionsAccordingToRules($acl);
        $this->applyDenyPermissionsForMissingRules($acl, ...$result);
    }

    /**
     * Apply ACL with rules
     *
     * @param Acl $acl
     * @return array[]
     */
    private function applyPermissionsAccordingToRules(Acl $acl): array
    {
        $foundResources = $foundDeniedRoles = [];
        foreach ($this->getRulesArray() as $rule) {
            $role = $rule['role_id'];
            $resource = $rule['resource_id'];
            $privileges = !empty($rule['privileges']) ? explode(',', $rule['privileges']) : null;

            if ($acl->hasResource($resource)) {
                $foundResources[$resource] = $resource;
                if ($rule['permission'] == 'allow') {
                    if ($resource === $this->_rootResource->getId()) {
                        $acl->allow($role, null, $privileges);
                    }
                    $acl->allow($role, $resource, $privileges);
                } elseif ($rule['permission'] == 'deny') {
                    $foundDeniedRoles[$role] = $role;
                    $acl->deny($role, $resource, $privileges);
                }
            }
        }
        return [$foundResources, $foundDeniedRoles];
    }

    /**
     * Apply deny permissions for missing rules
     *
     * For all rules that were not regenerated in authorization_rule table,
     * when adding a new module and without re-saving all roles,
     * consider not present rules with deny permissions
     *
     * @param Acl $acl
     * @param array $resources
     * @param array $deniedRoles
     * @return void
     */
    private function applyDenyPermissionsForMissingRules(
        Acl $acl,
        array $resources,
        array $deniedRoles
    ) {
        if (count($resources) && count($deniedRoles)
            //ignore denying missing permission if all are allowed
            && !(count($resources) === 1 && isset($resources[static::ALLOW_EVERYTHING]))
        ) {
            foreach ($acl->getResources() as $resource) {
                if (!isset($resources[$resource])) {
                    foreach ($deniedRoles as $role) {
                        $acl->deny($role, $resource, null);
                    }
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
