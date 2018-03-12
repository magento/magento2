<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\InventoryCatalog\Model\GetSalableQuantityDataBySku;
use Magento\InventoryCatalog\Model\IsSingleSourceModeInterface;
use Magento\InventoryConfiguration\Model\IsSourceItemsAllowedForProductTypeInterface;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Add grid column with salable quantity data
 */
class SalableQuantity extends Column
{
    /**
     * @var IsSourceItemsAllowedForProductTypeInterface
     */
    private $isSourceItemsAllowedForProductType;

    /**
     * @var IsSingleSourceModeInterface
     */
    private $isSingleSourceMode;

    /**
     * @var GetSalableQuantityDataBySku
     */
    private $getSalableQuantityDataBySku;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param IsSourceItemsAllowedForProductTypeInterface $isSourceItemsAllowedForProductType
     * @param IsSingleSourceModeInterface $isSingleSourceMode
     * @param GetSalableQuantityDataBySku $getSalableQuantityDataBySku
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        IsSourceItemsAllowedForProductTypeInterface $isSourceItemsAllowedForProductType,
        IsSingleSourceModeInterface $isSingleSourceMode,
        GetSalableQuantityDataBySku $getSalableQuantityDataBySku,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->isSourceItemsAllowedForProductType = $isSourceItemsAllowedForProductType;
        $this->isSingleSourceMode = $isSingleSourceMode;
        $this->getSalableQuantityDataBySku = $getSalableQuantityDataBySku;
    }

    /**
     * @inheritdoc
     */
    public function prepareDataSource(array $dataSource)
    {
        if ($dataSource['data']['totalRecords'] > 0 && $this->isSingleSourceMode->execute() === false) {
            foreach ($dataSource['data']['items'] as &$row) {
                $row['salable_quantity'] =
                    $this->isSourceItemsAllowedForProductType->execute($row['type_id']) === true
                    ? $this->getSalableQuantityDataBySku->execute($row['sku'])
                    : [];
            }
        }
        unset($row);

        return $dataSource;
    }
}
