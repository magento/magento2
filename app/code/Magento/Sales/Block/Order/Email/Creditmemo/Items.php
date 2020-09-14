<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Order\Email\Creditmemo;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * Sales Order Email creditmemo items
 *
 * @api
 * @author     Magento Core Team <core@magentocommerce.com>
 * @since 100.0.2
 */
class Items extends \Magento\Sales\Block\Items\AbstractItems
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var CreditmemoRepositoryInterface
     */
    private $creditmemoRepository;

    /**
     * @param Context $context
     * @param array $data
     * @param OrderRepositoryInterface|null $orderRepository
     * @param CreditmemoRepositoryInterface|null $creditmemoRepository
     */
    public function __construct(
        Context $context,
        array $data = [],
        ?OrderRepositoryInterface $orderRepository = null,
        ?CreditmemoRepositoryInterface $creditmemoRepository = null
    ) {
        $this->orderRepository =
            $orderRepository ?: ObjectManager::getInstance()->get(OrderRepositoryInterface::class);
        $this->creditmemoRepository =
            $creditmemoRepository ?: ObjectManager::getInstance()->get(CreditmemoRepositoryInterface::class);

        parent::__construct($context, $data);
    }

    /**
     * Prepare item before output
     *
     * @param \Magento\Framework\View\Element\AbstractBlock $renderer
     * @return void
     */
    protected function _prepareItem(\Magento\Framework\View\Element\AbstractBlock $renderer)
    {
        $renderer->getItem()->setOrder($this->getOrder());
        $renderer->getItem()->setSource($this->getCreditmemo());
    }

    /**
     * Returns order.
     *
     * Custom email templates are only allowed to use scalar values for variable data.
     * So order is loaded by order_id, that is passed to block from email template.
     * For legacy custom email templates it can pass as an object.
     *
     * @return OrderInterface|null
     * @since 102.1.0
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

    /**
     * Returns creditmemo.
     *
     * Custom email templates are only allowed to use scalar values for variable data.
     * So creditmemo is loaded by creditmemo_id, that is passed to block from email template.
     * For legacy custom email templates it can pass as an object.
     *
     * @return CreditmemoInterface|null
     * @since 102.1.0
     */
    public function getCreditmemo()
    {
        $creditmemo = $this->getData('creditmemo');
        if ($creditmemo !== null) {
            return $creditmemo;
        }

        $creditmemoId = (int)$this->getData('creditmemo_id');
        if ($creditmemoId) {
            $creditmemo = $this->creditmemoRepository->get($creditmemoId);
            $this->setData('creditmemo', $creditmemo);
        }

        return $this->getData('creditmemo');
    }
}
