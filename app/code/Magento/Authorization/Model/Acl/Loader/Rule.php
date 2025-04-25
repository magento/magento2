<?php
/**
 * Copyright 2012 Adobe
 * All Rights Reserved.
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
        $this->denyPermissionsForMissingRules($acl, $result);
    }

    /**
     * Apply ACL with rules
     *
     * @param Acl $acl
     * @return array[]
     */
    private function applyPermissionsAccordingToRules(Acl $acl): array
    {
        $appliedRolePermissionsPerResource = [];
        foreach ($this->getRulesArray() as $rule) {
            $role = $rule['role_id'];
            $resource = $rule['resource_id'];
            $privileges = !empty($rule['privileges']) ? explode(',', $rule['privileges']) : null;

            if ($acl->hasResource($resource)) {

                $appliedRolePermissionsPerResource[$resource]['allow'] =
                    $appliedRolePermissionsPerResource[$resource]['allow'] ?? [];
                $appliedRolePermissionsPerResource[$resource]['deny'] =
                    $appliedRolePermissionsPerResource[$resource]['deny'] ?? [];

                if ($rule['permission'] == 'allow') {
                    if ($resource === $this->_rootResource->getId()) {
                        $acl->allow($role, null, $privileges);
                    }
                    $acl->allow($role, $resource, $privileges);
                    $appliedRolePermissionsPerResource[$resource]['allow'][] = $role;
                } elseif ($rule['permission'] == 'deny') {
                    $acl->deny($role, $resource, $privileges);
                    $appliedRolePermissionsPerResource[$resource]['deny'][] = $role;
                }
            }
        }

        return $appliedRolePermissionsPerResource;
    }

    /**
     * Deny permissions for missing rules
     *
     * For all rules that were not regenerated in authorization_rule table,
     * when adding a new module and without re-saving all roles,
     * consider not present rules with deny permissions
     *
     * @param Acl $acl
     * @param array $appliedRolePermissionsPerResource
     * @return void
     */
    private function denyPermissionsForMissingRules(
        Acl   $acl,
        array $appliedRolePermissionsPerResource,
    ) {
        $consolidatedDeniedRoleIds = array_unique(
            array_merge(
                ...array_column($appliedRolePermissionsPerResource, 'deny')
            )
        );

        $hasAppliedPermissions = count($appliedRolePermissionsPerResource) > 0;
        $hasDeniedRoles = count($consolidatedDeniedRoleIds) > 0;
        $allAllowed = count($appliedRolePermissionsPerResource) === 1
            && isset($appliedRolePermissionsPerResource[static::ALLOW_EVERYTHING]);

        if ($hasAppliedPermissions && $hasDeniedRoles && !$allAllowed) {
            // Add the resources that are not present in the rules at all,
            // assuming that they must be denied for all roles by default
            $resourcesUndefinedInAuthorizationRules =
                array_diff($acl->getResources(), array_keys($appliedRolePermissionsPerResource));
            $assumeDeniedRoleListPerResource =
                array_fill_keys($resourcesUndefinedInAuthorizationRules, $consolidatedDeniedRoleIds);

            // Add the resources that are permitted for one role and not present in others at all,
            // assuming that they must be denied for all other roles by default
            foreach ($appliedRolePermissionsPerResource as $resource => $permissions) {
                $allowedRoles = $permissions['allow'];
                $deniedRoles = $permissions['deny'];
                $assumedDeniedRoles = array_diff($consolidatedDeniedRoleIds, $allowedRoles, $deniedRoles);
                if ($assumedDeniedRoles) {
                    $assumeDeniedRoleListPerResource[$resource] = $assumedDeniedRoles;
                }
            }

            // Deny permissions for missing rules
            foreach ($assumeDeniedRoleListPerResource as $resource => $denyRoles) {
                $acl->deny($denyRoles, $resource, null);
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
