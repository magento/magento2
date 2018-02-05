<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Setup\Patch;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;


/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class PatchInitial
{


    /**
     * Do Upgrade
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function up(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup->createMigrationSetup();
        $setup->startSetup();

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
        $setup->endSetup();

    }

}
