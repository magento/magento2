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

use Magento\Framework\View\Element\Template;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * @api
 * @since 100.0.2
 */
class Items extends \Magento\Sales\Block\Items\AbstractItems
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    public function __construct(
        Template\Context $context,
        OrderRepositoryInterface $orderRepository,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->orderRepository = $orderRepository;
    }

    public function getOrder(): ?OrderInterface
    {
        if ($order = $this->getData('order')) {
            return $order;
        }

        return $this->orderRepository->get($this->getOrderId());
    }
}
