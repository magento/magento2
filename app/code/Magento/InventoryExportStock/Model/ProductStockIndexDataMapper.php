<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryExportStock\Model;

use Magento\InventoryExportStockApi\Api\Data\ProductStockIndexDataInterface;

/**
 * Class ProductStockIndexDataMapper
 */
class ProductStockIndexDataMapper
{
    /**
     * @var ProductStockIndexDataFactory
     */
    private $productStockIndexDataFactory;

    /**
     * @param ProductStockIndexDataFactory $productStockIndexDataFactory
     */
    public function __construct(
        ProductStockIndexDataFactory $productStockIndexDataFactory
    ) {
        $this->productStockIndexDataFactory = $productStockIndexDataFactory;
    }

    /**
     * Creates ProductStockIndexData object and set values inside of it
     *
     * @param array $item
     * @return ProductStockIndexDataInterface
     */
    public function execute(array $item): ProductStockIndexDataInterface
    {
        /** @var ProductStockIndexDataInterface $productStockDataObject */
        $productStockDataObject = $this->productStockIndexDataFactory->create();
        $productStockDataObject->setSku($item[ProductStockIndexDataInterface::SKU]);
        $productStockDataObject->setIsSalable((bool)$item[ProductStockIndexDataInterface::IS_SALABLE]);
        $qty = $item[ProductStockIndexDataInterface::QTY];
        if ($qty !== null) {
            $qty = (float)$qty;
        }
        $productStockDataObject->setQty($qty);

        return $productStockDataObject;
    }
}
