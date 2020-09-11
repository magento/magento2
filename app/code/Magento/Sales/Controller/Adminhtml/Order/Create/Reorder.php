<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Controller\Adminhtml\Order\Create;

use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Forward;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Catalog\Helper\Product;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Escaper;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Controller\Adminhtml\Order\Create;
use Magento\Sales\Helper\Reorder as ReorderHelper;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Reorder\UnavailableProductsProvider;
use Psr\Log\LoggerInterface;

/**
 * Controller create order.
 */
class Reorder extends Create implements HttpGetActionInterface
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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Action\Context $context
     * @param Product $productHelper
     * @param Escaper $escaper
     * @param PageFactory $resultPageFactory
     * @param ForwardFactory $resultForwardFactory
     * @param UnavailableProductsProvider $unavailableProductsProvider
     * @param OrderRepositoryInterface $orderRepository
     * @param ReorderHelper $reorderHelper
     * @param LoggerInterface $logger
     */
    public function __construct(
        Action\Context $context,
        Product $productHelper,
        Escaper $escaper,
        PageFactory $resultPageFactory,
        ForwardFactory $resultForwardFactory,
        UnavailableProductsProvider $unavailableProductsProvider,
        OrderRepositoryInterface $orderRepository,
        ReorderHelper $reorderHelper,
        LoggerInterface $logger
    ) {
        $this->unavailableProductsProvider = $unavailableProductsProvider;
        $this->orderRepository = $orderRepository;
        $this->reorderHelper = $reorderHelper;
        $this->logger = $logger;
        parent::__construct(
            $context,
            $productHelper,
            $escaper,
            $resultPageFactory,
            $resultForwardFactory
        );
    }

    /**
     * Adminhtml controller create order.
     *
     * @return Forward|Redirect
     */
    public function execute()
    {
        $this->_getSession()->clearStorage();
        $orderId = $this->getRequest()->getParam('order_id');
        /** @var Order $order */
        $order = $this->orderRepository->get($orderId);
        if (!$this->reorderHelper->canReorder($order->getEntityId())) {
            return $this->resultForwardFactory->create()->forward('noroute');
        }

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if (!$order->getId()) {
            $resultRedirect->setPath('sales/order/');
            return $resultRedirect;
        }

        $unavailableProducts = $this->unavailableProductsProvider->getForOrder($order);
        if (count($unavailableProducts) > 0) {
            foreach ($unavailableProducts as $sku) {
                $this->messageManager->addErrorMessage(
                    sprintf('Product "%s" not found. This product is no longer available.', $sku)
                );
            }
            $resultRedirect->setPath('sales/order/view', ['order_id' => $orderId]);
        } else {
            try {
                $order->setReordered(true);
                $this->_getSession()->setUseOldShippingMethod(true);
                $this->_getOrderCreateModel()->initFromOrder($order);
                $resultRedirect->setPath('sales/*');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->logger->critical($e);
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('sales/*');
            } catch (\Exception $e) {
                $this->logger->critical($e);
                $this->messageManager->addException($e, __('Error while processing order.'));
                return $resultRedirect->setPath('sales/*');
            }
        }

        return $resultRedirect;
    }
}
