<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
<<<<<<< HEAD
=======
declare(strict_types=1);

>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
namespace Magento\CatalogInventory\Model\Plugin;

use Magento\CatalogInventory\Model\ResourceModel\Stock\Item;
use Magento\Catalog\Model\Indexer\Product\Price\Processor;
use Magento\Framework\Model\AbstractModel;

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
     * @param Item $subject
     * @param Item $result
     * @param AbstractModel $model
     * @return Item
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
<<<<<<< HEAD
    public function afterSave(Item $subject, Item $result, AbstractModel $model)
=======
    public function afterSave(Item $subject, Item $result, AbstractModel $model): Item
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
     * @param Item $subject
<<<<<<< HEAD
     * @param $result
=======
     * @param mixed $result
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     * @param int $websiteId
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterUpdateSetOutOfStock(Item $subject, $result, int $websiteId)
    {
        $this->priceIndexProcessor->markIndexerAsInvalid();
    }

    /**
     * @param Item $subject
<<<<<<< HEAD
     * @param $result
=======
     * @param mixed $result
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     * @param int $websiteId
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterUpdateSetInStock(Item $subject, $result, int $websiteId)
    {
        $this->priceIndexProcessor->markIndexerAsInvalid();
    }
}
