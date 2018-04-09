<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShipping\Block\Adminhtml\Order\View;

use Magento\Backend\Block\Widget\Container;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Registry;
use Magento\InventorySales\Model\StockByWebsiteIdResolver;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\InventoryApi\Api\GetStockSourceLinksInterface;
use Magento\InventoryApi\Api\Data\StockSourceLinkInterface;

/**
 * Update order_ship button to redirect to Source Selection page
 *
 * @api
 */
class ShipButton extends Container
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var StockByWebsiteIdResolver
     */
    private $stockByWebsiteIdResolver;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var GetStockSourceLinksInterface
     */
    private $getStockSourceLinks;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param StockByWebsiteIdResolver $stockByWebsiteIdResolver
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param GetStockSourceLinksInterface $getStockSourceLinks
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        StockByWebsiteIdResolver $stockByWebsiteIdResolver,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        GetStockSourceLinksInterface $getStockSourceLinks,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->registry = $registry;
        $this->stockByWebsiteIdResolver = $stockByWebsiteIdResolver;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->getStockSourceLinks = $getStockSourceLinks;
    }

    /**
     * @inheritdoc
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        if (!$this->isMultiSourceMode()) {
            $this->buttonList->update(
                'order_ship',
                'onclick',
                'setLocation(\'' . $this->getSourceSelectionUrl() . '\')'
            );
        }
        return $this;
    }

    /**
     * Source Selection URL getter
     *
     * @return string
     */
    public function getSourceSelectionUrl()
    {
        return $this->getUrl(
            'inventoryshipping/SourceSelection/index',
            [
                'order_id' => $this->getRequest()->getParam('order_id')
            ]
        );
    }

    /**
     * Check if system has more than one enabled Source for stock
     *
     * @return bool
     */
    private function isMultiSourceMode(): bool
    {
        $order = $this->registry->registry('current_order');
        $websiteId = $order->getStore()->getWebsiteId();
        $stockId = (int)$this->stockByWebsiteIdResolver->get((int)$websiteId)->getStockId();
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(StockSourceLinkInterface::STOCK_ID, $stockId)
            ->create();
        return $this->getStockSourceLinks->execute($searchCriteria)->getTotalCount() > 1;
    }
}
