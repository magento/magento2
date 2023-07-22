<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Widget\Setup\Patch\Data;

use Magento\Framework\Module\Setup\Migration;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

/**
 * Class UpgradeModelInstanceClassAliases
 * @package Magento\Widget\Setup\Patch
 */
class UpgradeModelInstanceClassAliases implements DataPatchInterface, PatchVersionInterface
{
    /**
     * UpgradeModelInstanceClassAliases constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $installer = $this->moduleDataSetup->createMigrationSetup();
        $this->moduleDataSetup->startSetup();

        $installer->appendClassAliasReplace(
            'widget_instance',
            'instance_type',
            Migration::ENTITY_TYPE_BLOCK,
            Migration::FIELD_CONTENT_TYPE_PLAIN,
            ['instance_id']
        );
        $installer->appendClassAliasReplace(
            'layout_update',
            'xml',
            Migration::ENTITY_TYPE_BLOCK,
            Migration::FIELD_CONTENT_TYPE_XML,
            ['layout_update_id']
        );
        $installer->doUpdateClassAliases();
        $this->moduleDataSetup->endSetup();
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
