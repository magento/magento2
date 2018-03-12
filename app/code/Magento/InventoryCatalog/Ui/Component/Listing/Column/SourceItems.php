<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Ui\Component\Listing\Column;

use Magento\Inventory\Model\SourceItem\Command\GetSourceItemsBySkuInterface;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\InventoryCatalog\Model\IsSingleSourceModeInterface;
use Magento\InventoryConfiguration\Model\IsSourceItemsAllowedForProductTypeInterface;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

/**
 * Add grid column with source items data
 */
class SourceItems extends Column
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
     * @param IsSourceItemsAllowedForProductTypeInterface $isSourceItemsAllowedForProductType
     * @param IsSingleSourceModeInterface $isSingleSourceMode
     * @param SourceRepositoryInterface $sourceRepository
     * @param GetSourceItemsBySkuInterface $getSourceItemsBySku
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        IsSourceItemsAllowedForProductTypeInterface $isSourceItemsAllowedForProductType,
        IsSingleSourceModeInterface $isSingleSourceMode,
        SourceRepositoryInterface $sourceRepository,
        GetSourceItemsBySkuInterface $getSourceItemsBySku,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->isSourceItemsAllowedForProductType = $isSourceItemsAllowedForProductType;
        $this->sourceRepository = $sourceRepository;
        $this->isSingleSourceMode = $isSingleSourceMode;
        $this->getSourceItemsBySku = $getSourceItemsBySku;
    }

    /**
     * @inheritdoc
     */
    public function prepareDataSource(array $dataSource)
    {
        if ($dataSource['data']['totalRecords'] > 0 && $this->isSingleSourceMode->execute() === false) {
            foreach ($dataSource['data']['items'] as &$row) {
                $row['qty'] = $this->isSourceItemsAllowedForProductType->execute($row['type_id']) === true
                    ? $this->getSourceItemsData($row['sku'])
                    : [];
            }
        }
        unset($row);

        return $dataSource;
    }

    /**
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
