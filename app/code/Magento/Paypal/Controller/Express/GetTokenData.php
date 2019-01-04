<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Controller\Express;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Webapi\Exception;
use Magento\Paypal\Model\Express\Checkout;
use Magento\Framework\App\ObjectManager;

class GetTokenData extends GetToken
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;
    private $quoteRepository;
    private $customerRepository;
    private $quoteIdMaskFactory;
    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Paypal\Model\Express\Checkout\Factory $checkoutFactory
     * @param \Magento\Framework\Session\Generic $paypalSession
     * @param \Magento\Framework\Url\Helper\Data $urlHelper
     * @param \Magento\Customer\Model\Url $customerUrl
     * @param \Psr\Log\LoggerInterface|null $logger
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Paypal\Model\Express\Checkout\Factory $checkoutFactory,
        \Magento\Framework\Session\Generic $paypalSession,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        \Magento\Customer\Model\Url $customerUrl,
        \Psr\Log\LoggerInterface $logger = null,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository = null,
        \Magento\Customer\Model\ResourceModel\CustomerRepository $customerRepository = null,
        \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory = null
    ) {
        $this->logger = $logger ?: ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class);
        $this->quoteRepository = $quoteRepository ?: ObjectManager::getInstance()->get(\Magento\Quote\Api\CartRepositoryInterface::class);
        $this->customerRepository = $customerRepository ?: ObjectManager::getInstance()->get(\Magento\Customer\Model\ResourceModel\CustomerRepository::class);
        $this->quoteIdMaskFactory = $quoteIdMaskFactory ?: ObjectManager::getInstance()->get(\Magento\Quote\Model\QuoteIdMaskFactory::class);
        parent::__construct(
            $context,
            $customerSession,
            $checkoutSession,
            $orderFactory,
            $checkoutFactory,
            $paypalSession,
            $urlHelper,
            $customerUrl
        );
    }
    /**
     * @return \Magento\Framework\App\ResponseInterface|ResultInterface
     */
    public function execute()
    {
        $controllerResult = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        try {
            $token = $this->getToken();
            if ($token === null) {
                $token = false;
            }
            $this->_initToken($token);
            $controllerResult->setData(['token' => $token]);
        } catch (LocalizedException $exception) {
            $this->logger->critical($exception);
            $controllerResult->setData([
                'message' => [
                    'text' => $exception->getMessage(),
                    'type' => 'error'
                ]
            ]);
        } catch (\Exception $exception) {
            $this->messageManager->addExceptionMessage(
                $exception,
                __('We can\'t start Express Checkout.')
            );
            return $this->getErrorResponse($controllerResult);
        }
        return $controllerResult;
    }
    /**
     * @return string|null
     * @throws LocalizedException
     */
//    protected function getToken()
//    {
//        $quoteId = $this->getRequest()->getParam('quote_id');
//        $customerId = $this->getRequest()->getParam('customer_id');
//        $hasButton = (bool)$this->getRequest()->getParam(Checkout::PAYMENT_INFO_BUTTON) == 1;
//
//        if(!$customerId) {
//            $quoteIdMask = $this->quoteIdMaskFactory->create()->load($quoteId, 'masked_id');
//            $quoteId = $quoteIdMask->getQuoteId();
//        }
//        $quote = $this->quoteRepository->get((int)$quoteId);
//        $this->initCheckout($quote);
//
//        if ($quote->getIsMultiShipping()) {
//            $quote->setIsMultiShipping(false);
//            $quote->removeAllAddresses();
//        }
//
//        if ($customerId) {
//            $customerData = $this->customerRepository->getById((int)$customerId);
//
//            $this->_checkout->setCustomerWithAddressChange(
//                $customerData,
//                $quote->getBillingAddress(),
//                $quote->getShippingAddress()
//            );
//
//            // billing agreement
//            $isBaRequested = (bool)$this->getRequest()
//                ->getParam(Checkout::PAYMENT_INFO_TRANSPORT_BILLING_AGREEMENT);
//            $this->_checkout->setIsBillingAgreementRequested($isBaRequested);
//        }
//
//        // Bill Me Later
//        $this->_checkout->setIsBml((bool)$this->getRequest()->getParam('bml'));
//
//        // giropay
//        $this->_checkout->prepareGiropayUrls(
//            $this->_url->getUrl('checkout/onepage/success'),
//            $this->_url->getUrl('paypal/express/cancel'),
//            $this->_url->getUrl('checkout/onepage/success')
//        );
//        return $this->_checkout->start(
//            $this->_url->getUrl('*/*/return'),
//            $this->_url->getUrl('*/*/cancel'),
//            $hasButton
//        );
//    }
    /**
     * Instantiate quote and checkout
     *
     * @param $quote
     * @throws LocalizedException
     */
//    private function initCheckout($quote)
//    {
//        if (!$quote->hasItems() || $quote->getHasError()) {
//            $this->getResponse()->setStatusHeader(403, '1.1', 'Forbidden');
//            throw new \Magento\Framework\Exception\LocalizedException(__('We can\'t initialize Express Checkout.'));
//        }
//        if (!(float)$quote->getGrandTotal()) {
//            throw new \Magento\Framework\Exception\LocalizedException(
//                __(
//                    'PayPal can\'t process orders with a zero balance due. '
//                    . 'To finish your purchase, please go through the standard checkout process.'
//                )
//            );
//        }
//        if (!isset($this->_checkoutTypes[$this->_checkoutType])) {
//            $parameters = [
//                'params' => [
//                    'quote' => $quote,
//                    'config' => $this->_config,
//                ],
//            ];
//            $this->_checkoutTypes[$this->_checkoutType] = $this->_checkoutFactory
//                ->create($this->_checkoutType, $parameters);
//        }
//        $this->_checkout = $this->_checkoutTypes[$this->_checkoutType];
//    }
    /**
     * @param ResultInterface $controllerResult
     * @return ResultInterface
     */
    private function getErrorResponse(ResultInterface $controllerResult)
    {
        $controllerResult->setHttpResponseCode(Exception::HTTP_BAD_REQUEST);
        $controllerResult->setData(['message' => __('Sorry, but something went wrong')]);
        return $controllerResult;
    }
}