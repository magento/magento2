<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Ui\Component;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Inventory\Model\IsSourceItemsManagementAllowedForProductTypeInterface;
use Magento\InventoryCatalog\Model\GetAssignedStocksDataBySku;
use Magento\Ui\Component\Container;

/**
 * Container with stocks data
 */
class Stocks extends Container
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
     * @param IsSourceItemsManagementAllowedForProductTypeInterface $isSourceItemsManagementAllowedForProductType
     * @param GetAssignedStocksDataBySku $getAssignedStocksDataBySku
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        IsSourceItemsManagementAllowedForProductTypeInterface $isSourceItemsManagementAllowedForProductType,
        GetAssignedStocksDataBySku $getAssignedStocksDataBySku,
        $components = [],
        array $data = []
    ) {
        parent::__construct($context, $components, $data);
        $this->isSourceItemsManagementAllowedForProductType = $isSourceItemsManagementAllowedForProductType;
        $this->getAssignedStocksDataBySku = $getAssignedStocksDataBySku;
    }

    /**
     * @inheritdoc
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (!isset($dataSource['data']['product']['type_id']) || '' === trim($dataSource['data']['product']['type_id'])
            || $this->isSourceItemsManagementAllowedForProductType->execute($dataSource['data']['product']['type_id'])
            === false
        ) {
            return $dataSource;
        }

        if (!isset($dataSource['data']['product']['sku']) || '' === trim($dataSource['data']['product']['sku'])) {
            return $dataSource;
        }

        $dataSource['data']['stocks'] = $this->getAssignedStocksDataBySku->execute(
            $dataSource['data']['product']['sku']
        );
        return $dataSource;
    }
}
