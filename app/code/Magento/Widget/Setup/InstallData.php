<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Widget\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @codeCoverageIgnore
 * @since 2.0.0
 */
class InstallData implements InstallDataInterface
{
    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup->createMigrationSetup();
        $setup->startSetup();

        $installer->appendClassAliasReplace(
            'widget_instance',
            'instance_type',
            \Magento\Framework\Module\Setup\Migration::ENTITY_TYPE_BLOCK,
            \Magento\Framework\Module\Setup\Migration::FIELD_CONTENT_TYPE_PLAIN,
            ['instance_id']
        );

        $installer->appendClassAliasReplace(
            'layout_update',
            'xml',
            \Magento\Framework\Module\Setup\Migration::ENTITY_TYPE_BLOCK,
            \Magento\Framework\Module\Setup\Migration::FIELD_CONTENT_TYPE_XML,
            ['layout_update_id']
        );

        $installer->doUpdateClassAliases();

        $setup->endSetup();
    }
}
