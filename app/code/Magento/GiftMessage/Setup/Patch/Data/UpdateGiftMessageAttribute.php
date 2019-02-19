<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GiftMessage\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

/**
 * Class UpdateGiftMessageAttribute
 * @package Magento\GiftMessage\Setup\Patch
 */
class UpdateGiftMessageAttribute implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var CategorySetupFactory
     */
    private $categorySetupFactory;

    /**
     * UpdateGiftMessageAttribute constructor.
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup
     * @param CategorySetupFactory $categorySetupFactory
     */
    public function __construct(
        \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup,
        CategorySetupFactory $categorySetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->categorySetupFactory = $categorySetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        /** @var \Magento\Catalog\Setup\CategorySetup $categorySetup */
        $categorySetup = $this->categorySetupFactory->create(['setup' => $this->moduleDataSetup]);
        $entityTypeId = $categorySetup->getEntityTypeId(Product::ENTITY);
        $attribute = $categorySetup->getAttribute($entityTypeId, 'gift_message_available');
            $categorySetup->updateAttribute(
                $entityTypeId,
                $attribute['attribute_id'],
                'source_model',
                \Magento\Catalog\Model\Product\Attribute\Source\Boolean::class
            );
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [
            MoveGiftMessageToGiftOptionsGroup::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '2.1.0';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
