<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesInventory\Model\Order;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\CreditmemoItemInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;

/**
 * Class ReturnProcessor
 */
class ReturnValidator
{
    /**
     * ReturnValidator constructor.
     * @param OrderItemRepositoryInterface $orderItemRepository
     */
    public function __construct(
        private readonly OrderItemRepositoryInterface $orderItemRepository
    ) {
    }

    /**
     * @param int[] $returnToStockItems
     * @param CreditmemoInterface $creditmemo
     * @return Phrase|null
     */
    public function validate($returnToStockItems, CreditmemoInterface $creditmemo)
    {
        $creditmemoItems = $creditmemo->getItems();

        /** @var int $item */
        foreach ($returnToStockItems as $item) {
            try {
                $orderItem = $this->orderItemRepository->get($item);
                if (!$this->isOrderItemPartOfCreditmemo($creditmemoItems, $orderItem)) {
                    return __('The "%1" product is not part of the current creditmemo.', $orderItem->getSku());
                }
            } catch (NoSuchEntityException $e) {
                return __('The return to stock argument contains product item that is not part of the original order.');
            }
        }
        return null;
    }

    /**
     * @param CreditmemoItemInterface[] $creditmemoItems
     * @param OrderItemInterface $orderItem
     * @return bool
     */
    private function isOrderItemPartOfCreditmemo(array $creditmemoItems, OrderItemInterface $orderItem)
    {
        foreach ($creditmemoItems as $creditmemoItem) {
            if ($creditmemoItem->getOrderItemId() == $orderItem->getItemId()) {
                return true;
            }
        }
        return false;
    }
}
