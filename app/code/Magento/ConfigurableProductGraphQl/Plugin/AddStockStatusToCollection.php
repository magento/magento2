<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProductGraphQl\Plugin;

use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Product\Collection;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Status;

/**
 * Add stock data to configurable child products collection
 */
class AddStockStatusToCollection
{
    /**
     * @var Status
     */
    private $stockStatusResourceModel;

    /**
     * @param Status $stockStatusResourceModel
     */
    public function __construct(
        Status $stockStatusResourceModel
    ) {
        $this->stockStatusResourceModel = $stockStatusResourceModel;
    }

    /**
     * Add stock data to the collection.
     *
     * @param Collection $productCollection
     * @param bool $printQuery
     * @param bool $logQuery
     * @return array
     */
    public function beforeLoad(Collection $productCollection, $printQuery = false, $logQuery = false): array
    {
        $stockFlag = 'has_stock_status_filter';
        if (!$productCollection->hasFlag($stockFlag)) {
            $this->stockStatusResourceModel->addStockDataToCollection($productCollection, false);
            $productCollection->setFlag($stockFlag, true);
        }
        return [$printQuery, $logQuery];
    }
}
