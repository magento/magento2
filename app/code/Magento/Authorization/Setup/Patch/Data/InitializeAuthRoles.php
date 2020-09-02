<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Authorization\Setup\Patch\Data;

use Magento\Authorization\Model\ResourceModel\Role;
use Magento\Authorization\Model\Rules;
use Magento\Authorization\Setup\AuthorizationFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Authorization\Model\Acl\Role\Group as RoleGroup;
use Magento\Authorization\Model\UserContextInterface;

/**
 * Class for Initialize Auth Roles
 */
class InitializeAuthRoles implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var AuthorizationFactory
     */
    private $authFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param AuthorizationFactory $authorizationFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        AuthorizationFactory $authorizationFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->authFactory = $authorizationFactory;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $roleCollection = $this->authFactory->createRoleCollection()
            ->addFieldToFilter('parent_id', 0)
            ->addFieldToFilter('tree_level', 1)
            ->addFieldToFilter('role_type', RoleGroup::ROLE_TYPE)
            ->addFieldToFilter('user_id', 0)
            ->addFieldToFilter('user_type', UserContextInterface::USER_TYPE_ADMIN)
            ->addFieldToFilter('role_name', 'Administrators');

        if ($roleCollection->count() == 0) {
            $admGroupRole = $this->authFactory->createRole()->setData(
                [
                    'parent_id' => 0,
                    'tree_level' => 1,
                    'sort_order' => 1,
                    'role_type' => RoleGroup::ROLE_TYPE,
                    'user_id' => 0,
                    'user_type' => UserContextInterface::USER_TYPE_ADMIN,
                    'role_name' => 'Administrators',
                ]
            )->save();
        } else {
            /** @var Role $item */
            foreach ($roleCollection as $item) {
                $admGroupRole = $item;
                break;
            }
        }

        $rulesCollection = $this->authFactory->createRulesCollection()
            ->addFieldToFilter('role_id', $admGroupRole->getId())
            ->addFieldToFilter('resource_id', 'all');

        if ($rulesCollection->count() == 0) {
            $this->authFactory->createRules()->setData(
                [
                    'role_id' => $admGroupRole->getId(),
                    'resource_id' => 'Magento_Backend::all',
                    'privileges' => null,
                    'permission' => 'allow',
                ]
            )->save();
        } else {
            /** @var Rules $rule */
            foreach ($rulesCollection as $rule) {
                $rule->setData('resource_id', 'Magento_Backend::all')->save();
            }
        }

        /**
         * Delete rows by condition from authorization_rule
         */
        $tableName = $this->moduleDataSetup->getTable('authorization_rule');
        if ($tableName) {
            $this->moduleDataSetup->getConnection()->delete(
                $tableName,
                ['resource_id = ?' => 'admin/system/tools/compiler']
            );
        }
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function getVersion()
    {
        return '2.0.0';
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
