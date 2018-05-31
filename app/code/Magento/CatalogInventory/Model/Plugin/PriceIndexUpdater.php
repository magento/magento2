<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Model\Plugin;

use Magento\CatalogInventory\Model\Stock\Item as ItemModel;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Item as ItemResourceModel;
use Magento\Catalog\Model\Indexer\Product\Price\Processor;

/**
 * Update product price index after product stock status changed.
 */
class PriceIndexUpdater
{
    /**
     * @var Processor
     */
    private $priceIndexProcessor;

    /**
     * @param Processor $priceIndexProcessor
     */
    public function __construct(Processor $priceIndexProcessor)
    {
        $this->priceIndexProcessor = $priceIndexProcessor;
    }

    /**
     * @param ItemResourceModel $subject
     * @param ItemResourceModel $result
     * @param ItemModel $model
     * @return ItemResourceModel
     * SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(ItemResourceModel $subject, ItemResourceModel $result, ItemModel $model)
    {
        $fields = [
            'is_in_stock',
            'use_config_manage_stock',
            'manage_stock',
        ];
        foreach ($fields as $field) {
            if ($model->dataHasChangedFor($field)) {
                $this->priceIndexProcessor->reindexRow($model->getProductId());
                break;
            }
        }

        return $result;
    }

    /**
     * @param ItemResourceModel $subject
     * @param $result
     * @param int $websiteId
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterUpdateSetOutOfStock(ItemResourceModel $subject, $result, int $websiteId)
    {
        $this->priceIndexProcessor->markIndexerAsInvalid();
    }

    /**
     * @param ItemResourceModel $subject
     * @param $result
     * @param int $websiteId
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterUpdateSetInStock(ItemResourceModel $subject, $result, int $websiteId)
    {
        $this->priceIndexProcessor->markIndexerAsInvalid();
    }
}
