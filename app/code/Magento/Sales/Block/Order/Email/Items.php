<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Sales Order Email order items
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Sales\Block\Order\Email;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * Sales Order Email items.
 *
 * @api
 * @since 100.0.2
 */
class Items extends \Magento\Sales\Block\Items\AbstractItems
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @param Context $context
     * @param array $data
     * @param OrderRepositoryInterface|null $orderRepository
     */
    public function __construct(
        Context $context,
        array $data = [],
        ?OrderRepositoryInterface $orderRepository = null
    ) {
        $this->orderRepository = $orderRepository ?: ObjectManager::getInstance()->get(OrderRepositoryInterface::class);

        parent::__construct($context, $data);
    }

    /**
     * Returns order.
     *
     * Custom email templates are only allowed to use scalar values for variable data.
     * So order is loaded by order_id, that is passed to block from email template.
     * For legacy custom email templates it can pass as an object.
     *
     * @return OrderInterface|null
     */
    public function getOrder()
    {
        $order = $this->getData('order');

        if ($order !== null) {
            return $order;
        }
        $orderId = (int)$this->getData('order_id');
        if ($orderId) {
            $order = $this->orderRepository->get($orderId);
            $this->setData('order', $order);
        }

        return $this->getData('order');
    }
}
