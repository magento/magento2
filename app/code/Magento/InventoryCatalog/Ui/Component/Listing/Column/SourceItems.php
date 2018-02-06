<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Ui\Component\Listing\Column;

use Magento\Inventory\Model\IsSourceItemsManagementAllowedForProductTypeInterface;
use Magento\Inventory\Model\SourceItem\Command\GetSourceItemsBySkuInterface;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

/**
 * Add grid column with source items data
 */
class SourceItems extends Column
{
    /**
     * @var IsSourceItemsManagementAllowedForProductTypeInterface
     */
    private $isSourceItemsManagementAllowedForProductType;

    /**
     * @var SourceRepositoryInterface
     */
    private $sourceRepository;

    /**
     * @var GetSourceItemsBySkuInterface
     */
    private $getSourceItemsBySku;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param IsSourceItemsManagementAllowedForProductTypeInterface $isSourceItemsManagementAllowedForProductType
     * @param SourceRepositoryInterface $sourceRepository
     * @param GetSourceItemsBySkuInterface $getSourceItemsBySku
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        IsSourceItemsManagementAllowedForProductTypeInterface $isSourceItemsManagementAllowedForProductType,
        SourceRepositoryInterface $sourceRepository,
        GetSourceItemsBySkuInterface $getSourceItemsBySku,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->isSourceItemsManagementAllowedForProductType = $isSourceItemsManagementAllowedForProductType;
        $this->sourceRepository = $sourceRepository;
        $this->getSourceItemsBySku = $getSourceItemsBySku;
    }

    /**
     * Prepare data source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if ($dataSource['data']['totalRecords'] > 0) {
            foreach ($dataSource['data']['items'] as &$row) {
                $row['qty'] = $this->isSourceItemsManagementAllowedForProductType->execute($row['type_id']) === true
                    ? $this->getSourceItemsData($row['sku'])
                    : [];
            }
        }
        unset($row);

        return $dataSource;
    }

    /**
     * Prepare source items
     *
     * @param string $sku
     * @return array
     */
    private function getSourceItemsData(string $sku): array
    {
        $sourceItems = $this->getSourceItemsBySku->execute($sku)->getItems();

        $sourceItemsData = [];
        foreach ($sourceItems as $sourceItem) {
            $source = $this->sourceRepository->get($sourceItem->getSourceCode());
            $qty = (float)$sourceItem->getQuantity();

            $sourceItemsData[] = [
                'source_name' => $source->getName(),
                'qty' => $qty,
            ];
        }
        return $sourceItemsData;
    }
}
