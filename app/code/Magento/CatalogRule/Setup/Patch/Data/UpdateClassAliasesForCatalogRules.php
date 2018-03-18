<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

/**
 * Class UpdateClassAliasesForCatalogRules
 * @package Magento\CatalogRule\Setup\Patch
 */
class UpdateClassAliasesForCatalogRules implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $dataSetup;

    /**
     * UpdateClassAliasesForCatalogRules constructor.
     * @param ModuleDataSetupInterface $dataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $dataSetup
    ) {
        $this->dataSetup = $dataSetup;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $installer = $this->dataSetup->createMigrationSetup();
        $installer->appendClassAliasReplace(
            'catalogrule',
            'conditions_serialized',
            \Magento\Framework\Module\Setup\Migration::ENTITY_TYPE_MODEL,
            \Magento\Framework\Module\Setup\Migration::FIELD_CONTENT_TYPE_SERIALIZED,
            ['rule_id']
        );
        $installer->appendClassAliasReplace(
            'catalogrule',
            'actions_serialized',
            \Magento\Framework\Module\Setup\Migration::ENTITY_TYPE_MODEL,
            \Magento\Framework\Module\Setup\Migration::FIELD_CONTENT_TYPE_SERIALIZED,
            ['rule_id']
        );
        $installer->doUpdateClassAliases();
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '2.0.0';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
