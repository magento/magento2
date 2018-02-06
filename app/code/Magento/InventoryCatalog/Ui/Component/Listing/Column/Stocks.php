<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Inventory\Model\IsSourceItemsManagementAllowedForProductTypeInterface;
use Magento\InventoryCatalog\Model\GetAssignedStocksDataBySku;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Add grid column with stocks data
 */
class Stocks extends Column
{
    /**
     * @var IsSourceItemsManagementAllowedForProductTypeInterface
     */
    private $isSourceItemsManagementAllowedForProductType;

    /**
     * @var GetAssignedStocksDataBySku
     */
    private $getAssignedStocksDataBySku;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param IsSourceItemsManagementAllowedForProductTypeInterface $isSourceItemsManagementAllowedForProductType
     * @param GetAssignedStocksDataBySku $getAssignedStocksDataBySku
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        IsSourceItemsManagementAllowedForProductTypeInterface $isSourceItemsManagementAllowedForProductType,
        GetAssignedStocksDataBySku $getAssignedStocksDataBySku,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->isSourceItemsManagementAllowedForProductType = $isSourceItemsManagementAllowedForProductType;
        $this->getAssignedStocksDataBySku = $getAssignedStocksDataBySku;
    }

    /**
     * @inheritdoc
     */
    public function prepareDataSource(array $dataSource)
    {
        if ($dataSource['data']['totalRecords'] > 0) {
            foreach ($dataSource['data']['items'] as &$row) {
                $row['stocks'] = $this->isSourceItemsManagementAllowedForProductType->execute($row['type_id']) === true
                    ? $this->getAssignedStocksDataBySku->execute($row['sku'])
                    : [];
            }
        }
        unset($row);

        return $dataSource;
    }
}
