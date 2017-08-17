<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GiftMessage\Setup;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

/**
 * Class \Magento\GiftMessage\Setup\UpgradeData
 *
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var CategorySetupFactory
     */
    protected $categorySetupFactory;

    /**
     * UpgradeData constructor
     *
     * @param CategorySetupFactory $categorySetupFactory
     */
    public function __construct(CategorySetupFactory $categorySetupFactory)
    {
        $this->categorySetupFactory = $categorySetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        /** @var \Magento\Catalog\Setup\CategorySetup $categorySetup */
        $categorySetup = $this->categorySetupFactory->create(['setup' => $setup]);
        $entityTypeId = $categorySetup->getEntityTypeId(Product::ENTITY);
        $attributeSetId = $categorySetup->getDefaultAttributeSetId(Product::ENTITY);
        $attribute = $categorySetup->getAttribute($entityTypeId, 'gift_message_available');

        if (version_compare($context->getVersion(), '2.0.1', '<')) {
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
        }

        if (version_compare($context->getVersion(), '2.1.0', '<')) {
            $categorySetup->updateAttribute(
                $entityTypeId,
                $attribute['attribute_id'],
                'source_model',
                \Magento\Catalog\Model\Product\Attribute\Source\Boolean::class
            );
        }

        $setup->endSetup();
    }
}
