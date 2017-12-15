<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Block\Adminhtml\Order\View\Tab;

/**
 * Tab for source items display on the order editing page
 *
 * @api
 */
class Sources extends \Magento\Backend\Block\Template implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * Template
     *
     * @var string
     */
    protected $_template = 'order/view/tab/sources.phtml';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var SourceItemRepositoryInterface
     */
    private $sourceItemRepository;

    /**
     * @var SourceRepositoryInterface
     */
    private $sourceRepository;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\InventoryApi\Api\SourceItemRepositoryInterface $sourceItemRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\InventoryApi\Api\SourceItemRepositoryInterface $sourceItemRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\InventoryApi\Api\SourceRepositoryInterface $sourceRepository,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sourceItemRepository = $sourceItemRepository;
        $this->sourceRepository = $sourceRepository;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->_coreRegistry->registry('current_order');
    }

    /**
     * Getting source items for order
     *
     * @return array
     */
    public function getSourceItems()
    {
        $order = $this->getOrder();

        $items = [];
        foreach ($order->getAllVisibleItems() as $orderItem) {
            $itemSku = $orderItem->getSku();
            $orderItemId = $orderItem->getItemId();
            $sources = $this->getSourcesBySku($itemSku);
            foreach ($sources as $source) {
                $sourceName = $this->sourceRepository->get((int)$source->getSourceId())->getName();
                $items[$sourceName][$orderItemId] = [
                    'sku' => $itemSku,
                    'qty' => $source->getQuantity()
                ];
            }
        }

        return $items;
    }

    /**
     * Getting sources by sku
     *
     * @param string $sku
     * @return array
     */
    private function getSourcesBySku(string $sku): array
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('sku', $sku)
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
