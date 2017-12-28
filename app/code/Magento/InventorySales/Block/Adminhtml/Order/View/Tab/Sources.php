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

/**
 * Tab for source items display on the order editing page
 *
 * @api
 */
class Sources extends Template implements TabInterface
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
     * @param Context $context
     * @param Registry $registry
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SourceItemRepositoryInterface $sourceItemRepository
     * @param SourceRepositoryInterface $sourceRepository
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SourceItemRepositoryInterface $sourceItemRepository,
        SourceRepositoryInterface $sourceRepository,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->registry = $registry;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sourceItemRepository = $sourceItemRepository;
        $this->sourceRepository = $sourceRepository;
    }

    /**
     * Getting source items for order
     *
     * @return array
     */
    public function getSourceItemsData()
    {
        $order = $this->registry->registry('current_order');

        $sourceItemsData = [];
        foreach ($order->getAllVisibleItems() as $orderItem) {
            $sourceItems = $this->getSourceItemsBySku($orderItem->getSku());

            foreach ($sourceItems as $sourceItem) {
                $sourceName = $this->sourceRepository->get($sourceItem->getSourceCode())->getName();
                $sourceItemsData[$sourceName][] = [
                    'sku' => $sourceItem->getSku(),
                    'qty' => $sourceItem->getQuantity(),
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
        return __('Source Delivery');
    }

    /**
     * @inheritdoc
     */
    public function getTabTitle()
    {
        return __('Source Delivery');
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
