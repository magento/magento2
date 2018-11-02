<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

/**
 * @api
 * @since 100.0.2
 */
class StockStatus extends \Magento\Ui\Component\Listing\Columns\Column
{
    const NAME = 'quantity_and_stock_status';

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * @var \Magento\Catalog\Model\Product\Attribute\Source\StockStatus
     */
    protected $stockStatus;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\Catalog\Model\Product\Attribute\Source\StockStatus $stockStatus
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Catalog\Model\Product\Attribute\Source\StockStatus $stockStatus,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->stockRegistry = $stockRegistry;
        $this->stockStatus = $stockStatus;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource) : array
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $stockStatus = $this->stockRegistry->getProductStockStatus($item['entity_id']);
                $item[self::NAME] = $this->stockStatus->getOptionText($stockStatus);
            }
        }

        return $dataSource;
    }
}
