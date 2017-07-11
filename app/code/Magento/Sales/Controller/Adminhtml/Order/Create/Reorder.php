<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order\Create;

use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Framework\View\Result\PageFactory;
use \Magento\Sales\Model\Order\Reorder\UnavailableProductsProvider;

class Reorder extends \Magento\Sales\Controller\Adminhtml\Order\Create
{
    /**
     * @var UnavailableProductsProvider
     */
    private $unavailableProductsProvider;

    /**
     * @param UnavailableProductsProvider $unavailableProductsProvider
     * @param Action\Context $context
     * @param \Magento\Catalog\Helper\Product $productHelper
     * @param \Magento\Framework\Escaper $escaper
     * @param PageFactory $resultPageFactory
     * @param ForwardFactory $resultForwardFactory
     */
    public function __construct(
        UnavailableProductsProvider $unavailableProductsProvider,
        Action\Context $context,
        \Magento\Catalog\Helper\Product $productHelper,
        \Magento\Framework\Escaper $escaper,
        PageFactory $resultPageFactory,
        ForwardFactory $resultForwardFactory
    ) {
        $this->unavailableProductsProvider = $unavailableProductsProvider;
        parent::__construct(
            $context,
            $productHelper,
            $escaper,
            $resultPageFactory,
            $resultForwardFactory
        );
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Forward|\Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $this->_getSession()->clearStorage();
        $orderId = $this->getRequest()->getParam('order_id');
        $order = $this->_objectManager->create(\Magento\Sales\Model\Order::class)->load($orderId);
        if (!$this->_objectManager->get(\Magento\Sales\Helper\Reorder::class)->canReorder($order->getEntityId())) {
            return $this->resultForwardFactory->create()->forward('noroute');
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if (!$order->getId()) {
            $resultRedirect->setPath('sales/order/');
        }

        $unavailableProducts = $this->unavailableProductsProvider->getForOrder($order);
        if (count($unavailableProducts) > 0) {
            foreach ($unavailableProducts as $sku) {
                $this->messageManager->addNoticeMessage(
                    sprintf('Product "%s" not found. This product is no longer available.', $sku)
                );
            }
            $resultRedirect->setPath('sales/order/view', ['order_id' => $orderId]);
        } else {
            $order->setReordered(true);
            $this->_getSession()->setUseOldShippingMethod(true);
            $this->_getOrderCreateModel()->initFromOrder($order);
            $resultRedirect->setPath('sales/*');
        }

        return $resultRedirect;
    }
}
