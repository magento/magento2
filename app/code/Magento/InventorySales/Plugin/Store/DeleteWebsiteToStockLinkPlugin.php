<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Plugin\Store;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Model\DeleteSalesChannelToStockLinkInterface;

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
     * @param AbstractDb $subject
     * @param callable $proceed
     * @param AbstractModel $object
     * @return AbstractDb
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundDelete(
        AbstractDb $subject,
        callable $proceed,
        AbstractModel $object
    ) {
        $websiteCode = $object->getCode();

        $res = $proceed($object);
        $this->deleteSalesChannel->execute(SalesChannelInterface::TYPE_WEBSITE, $websiteCode);

        return $res;
    }
}
