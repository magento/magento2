<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventoryConfigurableProduct\Plugin;

use Magento\CatalogInventory\Api\StockStatusCriteriaInterfaceFactory;
use Magento\CatalogInventory\Api\StockStatusRepositoryInterface;
use Magento\ConfigurableProduct\Model\AttributeOptionProviderInterface;

class GetInStockAttributeOptionsPlugin
{
    /**
     * @var StockStatusCriteriaInterfaceFactory
     */
    private $stockStatusCriteriaFactory;

    /**
     * @var StockStatusRepositoryInterface
     */
    private $stockStatusRepository;

    /**
     * @param StockStatusCriteriaInterfaceFactory $stockStatusCriteriaFactory
     * @param StockStatusRepositoryInterface $stockStatusRepository
     */
    public function __construct(
        StockStatusCriteriaInterfaceFactory $stockStatusCriteriaFactory,
        StockStatusRepositoryInterface $stockStatusRepository
    ) {
        $this->stockStatusCriteriaFactory = $stockStatusCriteriaFactory;
        $this->stockStatusRepository = $stockStatusRepository;
    }

    /**
     * Retrieve in stock options for attribute
     *
     * @param AttributeOptionProviderInterface $subject
     * @param array $options
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetAttributeOptions(AttributeOptionProviderInterface $subject, array $options)
    {
        $sku = [];
        foreach ($options as $option) {
            $sku[] = $option['sku'];
        }
        $criteria = $this->stockStatusCriteriaFactory->create();
        $criteria->addFilter('stock_status', 'stock_status', '1');
        $criteria->addFilter('sku', 'sku', ['in' => $sku], 'public');
        $collection = $this->stockStatusRepository->getList($criteria);

        $inStockSku = [];
        foreach ($collection->getItems() as $inStockOption) {
            $inStockSku[] = $inStockOption->getData('sku');
        }
        if (!empty($inStockSku)) {
            foreach ($options as $key => $option) {
                if (!in_array($options[$key]['sku'], $inStockSku)) {
                    unset($options[$key]);
                }
            }
        }
        $options = array_values($options);

        return $options;
    }
}
