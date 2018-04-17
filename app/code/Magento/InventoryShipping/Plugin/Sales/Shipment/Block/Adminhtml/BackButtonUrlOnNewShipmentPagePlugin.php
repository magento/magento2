<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShipping\Plugin\Sales\Shipment\Block\Adminhtml;

use Magento\Shipping\Block\Adminhtml\Create;
use Magento\InventorySales\Model\StockByWebsiteIdResolver;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\InventoryApi\Api\GetStockSourceLinksInterface;
use Magento\InventoryApi\Api\Data\StockSourceLinkInterface;

class BackButtonUrlOnNewShipmentPagePlugin
{
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
     * @param StockByWebsiteIdResolver $stockByWebsiteIdResolver
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param GetStockSourceLinksInterface $getStockSourceLinks
     */
    public function __construct(
        StockByWebsiteIdResolver $stockByWebsiteIdResolver,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        GetStockSourceLinksInterface $getStockSourceLinks
    ) {
        $this->stockByWebsiteIdResolver = $stockByWebsiteIdResolver;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->getStockSourceLinks = $getStockSourceLinks;
    }

    /**
     * @param Create $subject
     * @return string
     */
    public function afterGetBackUrl(Create $subject, $result)
    {
        $websiteId = (int)$subject->getShipment()->getStore()->getWebsiteId();
        if ($this->isMultiSourceMode($websiteId)) {
            return $subject->getUrl(
                'inventoryshipping/SourceSelection/index',
                [
                    'order_id' => $subject->getShipment() ? $subject->getShipment()->getOrderId() : null
                ]
            );
        }

        return $result;
    }

    /**
     * Check if system has more than one enabled Source for stock
     *
     * @param $websiteId
     * @return bool
     */
    private function isMultiSourceMode($websiteId): bool
    {
        $stockId = (int)$this->stockByWebsiteIdResolver->get((int)$websiteId)->getStockId();
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(StockSourceLinkInterface::STOCK_ID, $stockId)
            ->create();
        return $this->getStockSourceLinks->execute($searchCriteria)->getTotalCount() > 1;
    }
}
