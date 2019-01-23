<?php
/**
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
use Magento\Paypal\Model\Config;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Sales\Model\OrderFactory;
use Magento\Paypal\Model\Express\Checkout\Factory as CheckoutFactory;
use Magento\Framework\Session\Generic as PayPalSession;
use Magento\Framework\Url\Helper\Data as UrlHelper;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\GuestCartRepositoryInterface;
use Psr\Log\LoggerInterface;

/**
 * Retrieve paypal token
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GetTokenData extends AbstractExpress implements HttpGetActionInterface
{
    /**
     * Config mode type
     *
     * @var string
     */
    protected $_configType = Config::class;

    /**
     * Config method type
     *
     * @var string
     */
    protected $_configMethod = Config::METHOD_WPP_EXPRESS;

    /**
     * Checkout mode type
     *
     * @var string
     */
    protected $_checkoutType = Checkout::class;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\Customer\Model\ResourceModel\CustomerRepository
     */
    private $customerRepository;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var \Magento\Quote\Api\GuestCartRepositoryInterface
     */
    private $guestCartRepository;

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
     * @param \Magento\Customer\Model\ResourceModel\CustomerRepository $customerRepository
     * @param \Magento\Quote\Api\CartRepositoryInterface $cartRepository
     * @param \Magento\Quote\Api\GuestCartRepositoryInterface $guestCartRepository
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        CheckoutSession $checkoutSession,
        OrderFactory $orderFactory,
        CheckoutFactory $checkoutFactory,
        PayPalSession $paypalSession,
        UrlHelper $urlHelper,
        CustomerUrl $customerUrl,
        LoggerInterface $logger,
        CustomerRepository $customerRepository,
        CartRepositoryInterface $cartRepository,
        GuestCartRepositoryInterface $guestCartRepository
    ) {
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
        $this->customerRepository = $customerRepository;
        $this->cartRepository = $cartRepository;
        $this->guestCartRepository = $guestCartRepository;
    }

    /**
     * Get token data
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute(): ResultInterface
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
    private function getToken(): ?string
    {
        $quoteId = $this->getRequest()->getParam('quote_id');
        $customerId = $this->getRequest()->getParam('customer_id');
        $hasButton = (bool)$this->getRequest()->getParam(Checkout::PAYMENT_INFO_BUTTON);

        $quote = $customerId ? $this->cartRepository->get($quoteId) : $this->guestCartRepository->get($quoteId);
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
