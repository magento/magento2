<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GiftMessage\Setup\Patch;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;


/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class Patch201
{


    /**
     * @param CategorySetupFactory $categorySetupFactory
     */
    public function __construct(CategorySetupFactory $categorySetupFactory)
    {
        $this->categorySetupFactory = $categorySetupFactory;
    }

    /**
     * Do Upgrade
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function up(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        /** @var \Magento\Catalog\Setup\CategorySetup $categorySetup */
        $categorySetup = $this->categorySetupFactory->create(['setup' => $setup]);
        $entityTypeId = $categorySetup->getEntityTypeId(Product::ENTITY);
        $attributeSetId = $categorySetup->getDefaultAttributeSetId(Product::ENTITY);
        $attribute = $categorySetup->getAttribute($entityTypeId, 'gift_message_available');

        $groupName = 'Gift Options';

        if (!$categorySetup->getAttributeGroup(Product::ENTITY, $attributeSetId, $groupName)) {
            $categorySetup->addAttributeGroup(Product::ENTITY, $attributeSetId, $groupName, 60);
        }
        $categorySetup->addAttributeToGroup(
            $entityTypeId,
            $attributeSetId,
            $groupName,
            $attribute['attribute_id'],
            10
        );


        $setup->endSetup();

    }

}
