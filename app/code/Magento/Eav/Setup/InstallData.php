<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Eav\Setup;

use Magento\Eav\Setup\EavSetupFactory;
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
     * EAV setup factory
     *
     * @var EavSetupFactory
     * @since 2.0.0
     */
    private $eavSetupFactory;

    /**
     * Init
     *
     * @param EavSetupFactory $eavSetupFactory
     * @since 2.0.0
     */
    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        /** @var \Magento\Framework\Module\Setup\Migration $migrationSetup */
        $migrationSetup = $setup->createMigrationSetup();

        $migrationSetup->appendClassAliasReplace(
            'eav_attribute',
            'attribute_model',
            \Magento\Framework\Module\Setup\Migration::ENTITY_TYPE_MODEL,
            \Magento\Framework\Module\Setup\Migration::FIELD_CONTENT_TYPE_PLAIN,
            ['attribute_id']
        );
        $migrationSetup->appendClassAliasReplace(
            'eav_attribute',
            'backend_model',
            \Magento\Framework\Module\Setup\Migration::ENTITY_TYPE_MODEL,
            \Magento\Framework\Module\Setup\Migration::FIELD_CONTENT_TYPE_PLAIN,
            ['attribute_id']
        );
        $migrationSetup->appendClassAliasReplace(
            'eav_attribute',
            'frontend_model',
            \Magento\Framework\Module\Setup\Migration::ENTITY_TYPE_MODEL,
            \Magento\Framework\Module\Setup\Migration::FIELD_CONTENT_TYPE_PLAIN,
            ['attribute_id']
        );
        $migrationSetup->appendClassAliasReplace(
            'eav_attribute',
            'source_model',
            \Magento\Framework\Module\Setup\Migration::ENTITY_TYPE_MODEL,
            \Magento\Framework\Module\Setup\Migration::FIELD_CONTENT_TYPE_PLAIN,
            ['attribute_id']
        );

        $migrationSetup->appendClassAliasReplace(
            'eav_entity_type',
            'entity_model',
            \Magento\Framework\Module\Setup\Migration::ENTITY_TYPE_MODEL,
            \Magento\Framework\Module\Setup\Migration::FIELD_CONTENT_TYPE_PLAIN,
            ['entity_type_id']
        );
        $migrationSetup->appendClassAliasReplace(
            'eav_entity_type',
            'attribute_model',
            \Magento\Framework\Module\Setup\Migration::ENTITY_TYPE_MODEL,
            \Magento\Framework\Module\Setup\Migration::FIELD_CONTENT_TYPE_PLAIN,
            ['entity_type_id']
        );
        $migrationSetup->appendClassAliasReplace(
            'eav_entity_type',
            'increment_model',
            \Magento\Framework\Module\Setup\Migration::ENTITY_TYPE_MODEL,
            \Magento\Framework\Module\Setup\Migration::FIELD_CONTENT_TYPE_PLAIN,
            ['entity_type_id']
        );
        $migrationSetup->appendClassAliasReplace(
            'eav_entity_type',
            'entity_attribute_collection',
            \Magento\Framework\Module\Setup\Migration::ENTITY_TYPE_RESOURCE,
            \Magento\Framework\Module\Setup\Migration::FIELD_CONTENT_TYPE_PLAIN,
            ['entity_type_id']
        );

        $migrationSetup->doUpdateClassAliases();

        /** @var \Magento\Eav\Setup\EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $groups = $eavSetup->getAttributeGroupCollectionFactory();
        foreach ($groups as $group) {
            /** @var $group \Magento\Eav\Model\Entity\Attribute\Group*/
            $group->save();
        }

        $setup->endSetup();
    }
}
