<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OneTouchOrdering\Controller\Button;

use Exception;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Json as JsonResult;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\OneTouchOrdering\Model\CustomerData;
use Magento\OneTouchOrdering\Model\PlaceOrder as PlaceOrderModel;
use Magento\Store\Model\StoreManagerInterface;

class PlaceOrder extends \Magento\Framework\App\Action\Action
{

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;
    /**
     * @var PlaceOrderModel
     */
    private $placeOrder;
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;
    /**
     * @var Session
     */
    private $customerSession;
    /**
     * @var CustomerData
     */
    private $customerData;

    /**
     * PlaceOrder constructor.
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param PlaceOrderModel $placeOrder
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param Session $customerSession
     * @param CustomerData $customerData
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        PlaceOrderModel $placeOrder,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        Session $customerSession,
        CustomerData $customerData
    ) {
        parent::__construct($context);
        $this->storeManager = $storeManager;
        $this->productRepository = $productRepository;
        $this->placeOrder = $placeOrder;
        $this->orderRepository = $orderRepository;
        $this->customerSession = $customerSession;
        $this->customerData = $customerData;
    }

    public function execute()
    {
        $product = $this->initProduct();
        /** @var JsonResult $result */
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        $params = $this->getRequest()->getParams();
        try {
            $customerData = $this->customerData->setCustomer($this->customerSession->getCustomer());
            $orderId = $this->placeOrder->placeOrder($product, $customerData, $params);
        } catch (NoSuchEntityException $e) {
            $errorMsg = __('Something went wrong while processing your order. Please try again later.');
            $this->messageManager->addErrorMessage($errorMsg);
            $result->setData([
                'response' => $errorMsg
            ]);
            return $result;
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $result->setData([
                'response' => $e->getMessage()
            ]);
            return $result;
        } catch (Exception $e) {
            $errorMsg = __('Something went wrong while processing your order. Please try again later.');
            $this->messageManager->addErrorMessage($errorMsg);
            $result->setData([
                'response' => $e->getMessage()
            ]);
            return $result;
        }

        $order = $this->orderRepository->get($orderId);
        $message = __('Your order number is: %1.', $order->getIncrementId());
        $this->messageManager->addSuccessMessage($message);
        $result->setData([
            'response' => $message
        ]);
        return $result;
    }

    /**
     * @return \Magento\Catalog\Api\Data\ProductInterface
     * @throws NoSuchEntityException
     */
    private function initProduct()
    {
        $productId = (int)$this->getRequest()->getParam('product');
        if ($productId) {
            $storeId = $this->storeManager->getStore()->getId();
            return $this->productRepository->getById($productId, false, $storeId);
        }
        throw new NoSuchEntityException(__('Requested product doesn\'t exist'));
    }
}
