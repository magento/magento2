<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Block\Adminhtml\Order\View\Tab;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Registry;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\SourceItemRepositoryInterface;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\InventorySales\Api\ShippingAlgorithmInterface;
use Magento\InventorySales\Api\SourceSelectionInterface;

/**
 * Tab for source items display on the order editing page
 *
 * @api
 */
class SourceSelection extends Template implements TabInterface
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var SourceItemRepositoryInterface
     */
    private $sourceItemRepository;

    /**
     * @var SourceRepositoryInterface
     */
    private $sourceRepository;
    /**
     * @var ShippingAlgorithmInterface
     */
    private $shippingAlgorithm;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SourceItemRepositoryInterface $sourceItemRepository
     * @param SourceRepositoryInterface $sourceRepository
     * @param ShippingAlgorithmInterface $shippingAlgorithm
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SourceItemRepositoryInterface $sourceItemRepository,
        SourceRepositoryInterface $sourceRepository,
        ShippingAlgorithmInterface $shippingAlgorithm,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->registry = $registry;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sourceItemRepository = $sourceItemRepository;
        $this->sourceRepository = $sourceRepository;
        $this->shippingAlgorithm = $shippingAlgorithm;
    }

    /**
     * Getting source items for order
     *
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getSourceItemsData(): array
    {
        $order = $this->registry->registry('current_order');

        $sourceSelectionMap = $this->shippingAlgorithm->get($order)
                                                    ->getSourceSelections();

        $sourceItemsData = [];
        foreach ($order->getAllVisibleItems() as $orderItem) {
            $itemSku = $orderItem->getSku();
            $sourceItems = $this->getSourceItemsBySku($orderItem->getSku());

            $itemSourceSelections = [];
            /** @var SourceSelectionInterface[] $sourceSelections */
            $sourceSelections = $sourceSelectionMap[$itemSku];
            foreach ($sourceSelections as $sourceSelection) {
                $itemSourceSelections[$sourceSelection->getSourceCode()] = $sourceSelection->getQty();
            }

            foreach ($sourceItems as $sourceItem) {
                $sourceCode = $sourceItem->getSourceCode();
                $sourceName = $this->sourceRepository->get($sourceCode)->getName();
                $deductedQty = $itemSourceSelections[$sourceCode] ?? 0;
                $sourceItemsData[$sourceName][] = [
                    'sku' => $itemSku,
                    'qty' => $sourceItem->getQuantity(),
                    'qty_deducted' => $deductedQty
                ];
            }
        }
        
        return $sourceItemsData;
    }

    /**
     * @param string $sku
     * @return SourceItemInterface[]
     */
    private function getSourceItemsBySku(string $sku): array
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(SourceItemInterface::SKU, $sku)
            ->create();

        $sourceItemSearchResult = $this->sourceItemRepository->getList($searchCriteria);
        return $sourceItemSearchResult->getItems();
    }

    /**
     * @inheritdoc
     */
    public function getTabLabel()
    {
        return __('Source Selection');
    }

    /**
     * @inheritdoc
     */
    public function getTabTitle()
    {
        return __('Source Selection');
    }

    /**
     * @inheritdoc
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function isHidden()
    {
        return false;
    }
}
