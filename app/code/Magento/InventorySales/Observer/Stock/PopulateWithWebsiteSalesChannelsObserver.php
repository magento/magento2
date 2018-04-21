<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Observer\Stock;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\InventoryApi\Api\Data\StockInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterfaceFactory;

/**
 * Populate stock with sales channels during saving via controller
 *
 * This needs to be handled in dedicated observer, because there is no pre-defined way of making several API calls for
 * Form submission handling
 */
class PopulateWithWebsiteSalesChannelsObserver implements ObserverInterface
{
    /**
     * @var SalesChannelInterfaceFactory
     */
    private $salesChannelFactory;

    /**
     * @param SalesChannelInterfaceFactory $salesChannelFactory
     */
    public function __construct(SalesChannelInterfaceFactory $salesChannelFactory)
    {
        $this->salesChannelFactory = $salesChannelFactory;
    }

    /**
     * Populate stock with sales channels during saving via controller
     *
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        /** @var StockInterface $stock */
        $stock = $observer->getEvent()->getStock();
        /** @var RequestInterface $request */
        $request = $observer->getEvent()->getRequest();
        $requestData = $request->getParams();

        $extensionAttributes = $stock->getExtensionAttributes();
        $assignedSalesChannels = $extensionAttributes->getSalesChannels();

        if (null !== $assignedSalesChannels) {
            foreach ($assignedSalesChannels as $key => $assignedSalesChannel) {
                if ($assignedSalesChannel->getType() === SalesChannelInterface::TYPE_WEBSITE) {
                    unset($assignedSalesChannels[$key]);
                }
            }
        }

        if (isset($requestData['sales_channels'][SalesChannelInterface::TYPE_WEBSITE])
            && is_array($requestData['sales_channels'][SalesChannelInterface::TYPE_WEBSITE])
        ) {
            foreach ($requestData['sales_channels'][SalesChannelInterface::TYPE_WEBSITE] as $websiteCode) {
                $assignedSalesChannels[] = $this->createSalesChannelByWebsiteCode($websiteCode);
            }
        }
        $extensionAttributes->setSalesChannels($assignedSalesChannels);
    }

    /**
     * Create the sales channel by given website code
     *
     * @param string $websiteCode
     * @return SalesChannelInterface
     */
    private function createSalesChannelByWebsiteCode(string $websiteCode): SalesChannelInterface
    {
        $salesChannel = $this->salesChannelFactory->create();
        $salesChannel->setCode($websiteCode);
        $salesChannel->setType(SalesChannelInterface::TYPE_WEBSITE);
        return $salesChannel;
    }
}
