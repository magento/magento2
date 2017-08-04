<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Creditmemo\Item\Validation;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\Sales\Model\Order\Item;
use Magento\Sales\Model\ValidatorInterface;

/**
 * Class CreationQuantityValidator
 * @since 2.1.3
 */
class CreationQuantityValidator implements ValidatorInterface
{
    /**
     * @var OrderItemRepositoryInterface
     * @since 2.1.3
     */
    private $orderItemRepository;

    /**
     * @var OrderInterfaceFactory
     * @since 2.1.3
     */
    private $context;

    /**
     * ItemCreationQuantityValidator constructor.
     * @param OrderItemRepositoryInterface $orderItemRepository
     * @param mixed $context
     * @since 2.1.3
     */
    public function __construct(OrderItemRepositoryInterface $orderItemRepository, $context = null)
    {
        $this->orderItemRepository = $orderItemRepository;
        $this->context = $context;
    }

    /**
     * @inheritdoc
     * @since 2.1.3
     */
    public function validate($entity)
    {
        try {
            $orderItem = $this->orderItemRepository->get($entity->getOrderItemId());
            if (!$this->isItemPartOfContextOrder($orderItem)) {
                return [__('The creditmemo contains product item that is not part of the original order.')];
            }
        } catch (NoSuchEntityException $e) {
            return [__('The creditmemo contains product item that is not part of the original order.')];
        }

        if (!$this->isQtyAvailable($orderItem, $entity->getQty())) {
            return [__('The quantity to refund must not be greater than the unrefunded quantity.')];
        }

        return [];
    }

    /**
     * @param Item $orderItem
     * @param int $qty
     * @return bool
     * @since 2.1.3
     */
    private function isQtyAvailable(Item $orderItem, $qty)
    {
        return $qty <= $orderItem->getQtyToRefund() || $orderItem->isDummy();
    }

    /**
     * @param OrderItemInterface $orderItem
     * @return bool
     * @since 2.1.3
     */
    private function isItemPartOfContextOrder(OrderItemInterface $orderItem)
    {
        return $this->context instanceof OrderInterface && $this->context->getEntityId() === $orderItem->getOrderId();
    }
}
