<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Observer\Website;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\InventorySales\Model\DeleteSalesChannelToStockLinkInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\Website;

/**
 * Delete link between Stock and Website
 */
class DeleteWebsiteToStockLink implements ObserverInterface
{
    /**
     * @var DeleteSalesChannelToStockLinkInterface
     */
    private $deleteSalesChannelToStockLink;

    /**
     * @param DeleteSalesChannelToStockLinkInterface $deleteSalesChannelToStockLink
     */
    public function __construct(
        DeleteSalesChannelToStockLinkInterface $deleteSalesChannelToStockLink
    ) {
        $this->deleteSalesChannelToStockLink = $deleteSalesChannelToStockLink;
    }

    /**
     * @inheritdoc
     */
    public function execute(Observer $observer)
    {
        /** @var Website $website */
        $website = $observer->getData('website');
        $websiteCode = $website->getCode();

        if ($websiteCode === WebsiteInterface::ADMIN_CODE) {
            return;
        }
        $this->deleteSalesChannelToStockLink->execute(SalesChannelInterface::TYPE_WEBSITE, $websiteCode);
    }
}
