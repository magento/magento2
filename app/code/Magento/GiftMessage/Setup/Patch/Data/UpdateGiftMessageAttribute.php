<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GiftMessage\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Setup\Model\Patch\DataPatchInterface;
use Magento\Setup\Model\Patch\PatchVersionInterface;

/**
 * Class UpdateGiftMessageAttribute
 * @package Magento\GiftMessage\Setup\Patch
 */
class UpdateGiftMessageAttribute implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var CategorySetupFactory
     */
    private $categorySetupFactory;

    /**
     * UpdateGiftMessageAttribute constructor.
     * @param ResourceConnection $resourceConnection
     * @param CategorySetupFactory $categorySetupFactory
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        CategorySetupFactory $categorySetupFactory
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->categorySetupFactory = $categorySetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->resourceConnection->getConnection()->startSetup();

        /** @var \Magento\Catalog\Setup\CategorySetup $categorySetup */
        $categorySetup = $this->categorySetupFactory->create(['resourceConnection' => $this->resourceConnection]);
        $entityTypeId = $categorySetup->getEntityTypeId(Product::ENTITY);
        $attribute = $categorySetup->getAttribute($entityTypeId, 'gift_message_available');
            $categorySetup->updateAttribute(
                $entityTypeId,
                $attribute['attribute_id'],
                'source_model',
                \Magento\Catalog\Model\Product\Attribute\Source\Boolean::class
            );
        $this->resourceConnection->getConnection()->endSetup();
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
