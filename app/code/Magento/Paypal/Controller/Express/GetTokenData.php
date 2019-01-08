<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Controller\Express;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Paypal\Model\Express\Checkout;

class GetTokenData extends AbstractExpress implements HttpGetActionInterface
{
    /**
     * Config mode type
     *
     * @var string
     */
    protected $_configType = \Magento\Paypal\Model\Config::class;

    /**
     * Config method type
     *
     * @var string
     */
    protected $_configMethod = \Magento\Paypal\Model\Config::METHOD_WPP_EXPRESS;

    /**
     * Checkout mode type
     *
     * @var string
     */
    protected $_checkoutType = \Magento\Paypal\Model\Express\Checkout::class;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var \Magento\Customer\Model\ResourceModel\CustomerRepository
     */
    private $customerRepository;

    /**
     * @var \Magento\Quote\Model\QuoteIdMaskFactory
     */
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
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\Customer\Model\ResourceModel\CustomerRepository $customerRepository
     * @param \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory
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
        \Psr\Log\LoggerInterface $logger,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Customer\Model\ResourceModel\CustomerRepository $customerRepository,
        \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory
    )
    {
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

        $this->logger = $logger;
        $this->quoteRepository = $quoteRepository;
        $this->customerRepository = $customerRepository;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|ResultInterface
     */
    public function execute()
    {
        $controllerResult = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $responseContent = [
            'success' => true,
            'error_message' => '',
        ];

        try {
            $token = $this->getToken();
            if ($token === null) {
                $token = false;
            }
            $this->_initToken($token);

            $responseContent['token'] = $token;
        } catch (LocalizedException $exception) {
            $this->logger->critical($exception);

            $responseContent['success'] = false;
            $responseContent['error_message'] = $exception->getMessage();
        } catch (\Exception $exception) {
            $this->logger->critical($exception);

            $responseContent['success'] = false;
            $responseContent['error_message'] = __('Sorry, but something went wrong');
        }

        return $controllerResult->setData($responseContent);
    }

    /**
     * Get paypal token
     *
     * @return string|null
     * @throws LocalizedException
     */
    protected function getToken()
    {
        $quoteId = $this->getRequest()->getParam('quote_id');
        $customerId = $this->getRequest()->getParam('customer_id');
        $hasButton = (bool)$this->getRequest()->getParam(Checkout::PAYMENT_INFO_BUTTON);
        $isBaRequested = (bool)$this->getRequest()->getParam(Checkout::PAYMENT_INFO_TRANSPORT_BILLING_AGREEMENT);

        if (!$customerId) {
            $quoteIdMask = $this->quoteIdMaskFactory->create()->load($quoteId, 'masked_id');
            $quoteId = $quoteIdMask->getQuoteId();
        }
        $quote = $this->quoteRepository->get((int)$quoteId);
        $this->_initCheckout($quote);

        if ($quote->getIsMultiShipping()) {
            $quote->setIsMultiShipping(false);
            $quote->removeAllAddresses();
        }

        if ($customerId) {
            $customerData = $this->customerRepository->getById((int)$customerId);

            $this->_checkout->setCustomerWithAddressChange(
                $customerData,
                $quote->getBillingAddress(),
                $quote->getShippingAddress()
            );

            $this->_checkout->setIsBillingAgreementRequested($isBaRequested);
        }

        // giropay urls
        $this->_checkout->prepareGiropayUrls(
            $this->_url->getUrl('checkout/onepage/success'),
            $this->_url->getUrl('paypal/express/cancel'),
            $this->_url->getUrl('checkout/onepage/success')
        );

        return $this->_checkout->start(
            $this->_url->getUrl('*/*/return'),
            $this->_url->getUrl('*/*/cancel'),
            $hasButton
        );
    }
}