<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Guest;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class \Magento\Sales\Controller\Guest\PrintShipment
 *
 * @since 2.0.0
 */
class PrintShipment extends \Magento\Sales\Controller\AbstractController\PrintShipment
{
    /**
     * @var OrderLoader
     * @since 2.0.0
     */
    protected $orderLoader;

    /**
     * @param Context $context
     * @param OrderViewAuthorization $orderAuthorization
     * @param \Magento\Framework\Registry $registry
     * @param PageFactory $resultPageFactory
     * @param OrderLoader $orderLoader
     * @since 2.0.0
     */
    public function __construct(
        Context $context,
        OrderViewAuthorization $orderAuthorization,
        \Magento\Framework\Registry $registry,
        PageFactory $resultPageFactory,
        OrderLoader $orderLoader
    ) {
        $this->orderLoader = $orderLoader;
        parent::__construct(
            $context,
            $orderAuthorization,
            $registry,
            $resultPageFactory
        );
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function execute()
    {
        $result = $this->orderLoader->load($this->_request);
        if ($result instanceof \Magento\Framework\Controller\ResultInterface) {
            return $result;
        }

        $shipmentId = (int)$this->getRequest()->getParam('shipment_id');
        if ($shipmentId) {
            $shipment = $this->_objectManager->create(\Magento\Sales\Model\Order\Shipment::class)->load($shipmentId);
            $order = $shipment->getOrder();
        } else {
            $order = $this->_coreRegistry->registry('current_order');
        }
        if ($this->orderAuthorization->canView($order)) {
            if (isset($shipment)) {
                $this->_coreRegistry->register('current_shipment', $shipment);
            }
            return $this->resultPageFactory->create()->addHandle('print');
        } else {
            return $this->resultRedirectFactory->create()->setPath('sales/guest/form');
        }
    }
}
