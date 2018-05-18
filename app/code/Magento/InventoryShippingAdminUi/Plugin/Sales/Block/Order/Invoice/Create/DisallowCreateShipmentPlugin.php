<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShippingAdminUi\Plugin\Sales\Block\Order\Invoice\Create;

use Magento\InventoryApi\Api\GetSourcesAssignedToStockOrderedByPriorityInterface;
use Magento\Sales\Block\Adminhtml\Order\Invoice\Create\Form;
use Magento\Framework\Exception\LocalizedException;
use Magento\InventorySalesApi\Model\StockByWebsiteIdResolverInterface;

/**
 * Disallow create shipment in multi source mode
 */
class DisallowCreateShipmentPlugin
{
    /**
     * @var GetSourcesAssignedToStockOrderedByPriorityInterface
     */
    private $getSourcesAssignedToStockOrderedByPriority;

    /**
     * @var StockByWebsiteIdResolverInterface
     */
    private $stockByWebsiteIdResolver;

    /**
     * DisallowCreateShipment constructor.
     * @param GetSourcesAssignedToStockOrderedByPriorityInterface $getSourcesAssignedToStockOrderedByPriority
     * @param StockByWebsiteIdResolverInterface $stockByWebsiteIdResolver
     */
    public function __construct(
        GetSourcesAssignedToStockOrderedByPriorityInterface $getSourcesAssignedToStockOrderedByPriority,
        StockByWebsiteIdResolverInterface $stockByWebsiteIdResolver
    ) {
        $this->getSourcesAssignedToStockOrderedByPriority = $getSourcesAssignedToStockOrderedByPriority;
        $this->stockByWebsiteIdResolver = $stockByWebsiteIdResolver;
    }

    /**
     * @param Form $subject
     * @param bool $result
     * @return bool
     */
    public function afterCanCreateShipment(Form $subject, bool $result)
    {
        try {
            $websiteId = $subject->getOrder()->getStore()->getWebsiteId();
            $stockId = $this->stockByWebsiteIdResolver->execute((int)$websiteId)->getStockId();
            $sources = $this->getSourcesAssignedToStockOrderedByPriority->execute((int)$stockId);
            if (count($sources) > 1) {
                return false;
            }
        } catch (LocalizedException $e) {
            return false;
        }

        return $result;
    }
}
