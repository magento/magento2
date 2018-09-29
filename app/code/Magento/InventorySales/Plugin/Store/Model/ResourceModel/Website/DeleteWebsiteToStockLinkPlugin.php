<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Plugin\Store\Model\ResourceModel\Website;

use Magento\Framework\Model\AbstractModel;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Model\DeleteSalesChannelToStockLinkInterface;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\ResourceModel\Website as WebsiteResourceModel;
use Magento\Store\Model\Website;

/**
 * Delete link between Stock and Website
 */
class DeleteWebsiteToStockLinkPlugin
{
    /**
     * @var DeleteSalesChannelToStockLinkInterface
     */
    private $deleteSalesChannel;

    /**
     * @param DeleteSalesChannelToStockLinkInterface $deleteSalesChannel
     */
    public function __construct(
        DeleteSalesChannelToStockLinkInterface $deleteSalesChannel
    ) {
        $this->deleteSalesChannel = $deleteSalesChannel;
    }

    /**
     * @param WebsiteResourceModel $subject
     * @param WebsiteResourceModel $result
     * @param Website|AbstractModel $website
     * @return WebsiteResourceModel
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterDelete(
        WebsiteResourceModel $subject,
        WebsiteResourceModel $result,
        AbstractModel $website
    ) {
        $websiteCode = $website->getCode();

        if ($websiteCode !== WebsiteInterface::ADMIN_CODE) {
            $this->deleteSalesChannel->execute(SalesChannelInterface::TYPE_WEBSITE, $websiteCode);
        }
        return $result;
    }
}
