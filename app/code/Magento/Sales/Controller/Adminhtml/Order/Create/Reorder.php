<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order\Create;

use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Model\Order\Reorder\UnavailableProductsProvider;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Helper\Reorder as ReorderHelper;

class Reorder extends \Magento\Sales\Controller\Adminhtml\Order\Create
{
    /**
     * @var UnavailableProductsProvider
     */
    private $unavailableProductsProvider;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var ReorderHelper
     */
    private $reorderHelper;

    /**
     * @param Action\Context $context
     * @param \Magento\Catalog\Helper\Product $productHelper
     * @param \Magento\Framework\Escaper $escaper
     * @param PageFactory $resultPageFactory
     * @param ForwardFactory $resultForwardFactory
     * @param UnavailableProductsProvider $unavailableProductsProvider
     * @param OrderRepositoryInterface $orderRepository
     * @param ReorderHelper $reorderHelper
     */
    public function __construct(
        Action\Context $context,
        \Magento\Catalog\Helper\Product $productHelper,
        \Magento\Framework\Escaper $escaper,
        PageFactory $resultPageFactory,
        ForwardFactory $resultForwardFactory,
        UnavailableProductsProvider $unavailableProductsProvider,
        OrderRepositoryInterface $orderRepository,
        ReorderHelper $reorderHelper
    ) {
        $this->unavailableProductsProvider = $unavailableProductsProvider;
        $this->orderRepository = $orderRepository;
        $this->reorderHelper = $reorderHelper;
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
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->orderRepository->get($orderId);
        if (!$this->reorderHelper->canReorder($order->getEntityId())) {
            return $this->resultForwardFactory->create()->forward('noroute');
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if (!$order->getId()) {
            $resultRedirect->setPath('sales/order/');
            return $resultRedirect;
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
