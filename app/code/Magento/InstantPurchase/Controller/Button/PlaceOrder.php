<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InstantPurchase\Controller\Button;

use Exception;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Json as JsonResult;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\InstantPurchase\Model\CustomerDataGetter;
use Magento\InstantPurchase\Model\CustomerDataGetterFactory;
use Magento\InstantPurchase\Model\PlaceOrder as PlaceOrderModel;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class PlaceOrder
 * @api
 */
class PlaceOrder extends \Magento\Framework\App\Action\Action
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;
    /**
     * @var PlaceOrderModel
     */
    private $placeOrder;
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;
    /**
     * @var Session
     */
    private $customerSession;
    /**
     * @var CustomerDataGetter
     */
    private $customerData;
    /**
     * @var Validator
     */
    private $formKeyValidator;

    /**
     * PlaceOrder constructor.
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param ProductRepositoryInterface $productRepository
     * @param PlaceOrderModel $placeOrder
     * @param OrderRepositoryInterface $orderRepository
     * @param Session $customerSession
     * @param CustomerDataGetterFactory $customerData
     * @param Validator $formKeyValidator
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        ProductRepositoryInterface $productRepository,
        PlaceOrderModel $placeOrder,
        OrderRepositoryInterface $orderRepository,
        Session $customerSession,
        CustomerDataGetterFactory $customerData,
        Validator $formKeyValidator
    ) {
        parent::__construct($context);
        $this->storeManager = $storeManager;
        $this->productRepository = $productRepository;
        $this->placeOrder = $placeOrder;
        $this->orderRepository = $orderRepository;
        $this->customerSession = $customerSession;
        $this->customerData = $customerData;
        $this->formKeyValidator = $formKeyValidator;
    }

    public function execute()
    {
        $errorMsg = __('Something went wrong while processing your order. Please try again later.');

        if (!$this->formKeyValidator->validate($this->getRequest())) {
            return $this->createResponse($errorMsg, false);
        }
        $product = $this->initProduct();
        $params = $this->getRequest()->getParams();
        try {
            $customerData = $this->customerData->create($this->customerSession->getCustomer());
            $orderId = $this->placeOrder->placeOrder($product, $customerData, $params);
        } catch (NoSuchEntityException $e) {
            return $this->createResponse($errorMsg, false);
        } catch (Exception $e) {
            return $this->createResponse($e->getMessage(), false);
        }

        $order = $this->orderRepository->get($orderId);
        $message = __('Your order number is: %1.', $order->getIncrementId());

        return $this->createResponse($message, true);
    }

    /**
     * @return ProductInterface
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

    /**
     * @param string $message
     * @param bool $successMessage
     * @return JsonResult
     */
    private function createResponse(string $message, bool $successMessage)
    {
        /** @var JsonResult $result */
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $result->setData([
            'response' => $message
        ]);
        if ($successMessage) {
            $this->messageManager->addSuccessMessage($message);
        } else {
            $this->messageManager->addErrorMessage($message);
        }

        return $result;
    }
}
