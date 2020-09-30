<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Setup\Patch\Data;

use Magento\Customer\Model\GroupManagement;
use Magento\Customer\Model\Vat;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Update default customer group id in customer configuration if it's value is NULL
 */
class UpdateDefaultCustomerGroupInConfig implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var GroupManagement
     */
    private $groupManagement;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param GroupManagement $groupManagement
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        GroupManagement $groupManagement
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->groupManagement = $groupManagement;
    }

    /**
     * @inheritDoc
     */
    public function apply()
    {
        $customerGroups = $this->groupManagement->getLoggedInGroups();
        $commonGroup = array_shift($customerGroups);

        $this->moduleDataSetup->getConnection()->update(
            $this->moduleDataSetup->getTable('core_config_data'),
            ['value' => $commonGroup->getId()],
            [
                'value is ?' => new \Zend_Db_Expr('NULL'),
                'path = ?' => GroupManagement::XML_PATH_DEFAULT_ID,
            ]
        );

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public static function getDependencies()
    {
        return [
            DefaultCustomerGroupsAndAttributes::class,
        ];
    }
}
