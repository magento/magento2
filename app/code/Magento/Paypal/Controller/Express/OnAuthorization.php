<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Controller\Express;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Paypal\Model\Config as PayPalConfig;
use Magento\Paypal\Model\Express\Checkout as PayPalCheckout;
use Magento\Paypal\Model\Api\ProcessableException as ApiProcessableException;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Sales\Model\OrderFactory;
use Magento\Paypal\Model\Express\Checkout\Factory as CheckoutFactory;
use Magento\Framework\Session\Generic as PayPalSession;
use Magento\Framework\Url\Helper\Data as UrlHelper;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Framework\UrlInterface;
use Magento\Quote\Api\GuestCartRepositoryInterface;

/**
 * Processes data after returning from PayPal
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OnAuthorization extends AbstractExpress implements HttpPostActionInterface
{
    /**
     * @inheritdoc
     */
    protected $_configType = PayPalConfig::class;

    /**
     * @inheritdoc
     */
    protected $_configMethod = PayPalConfig::METHOD_WPP_EXPRESS;

    /**
     * @inheritdoc
     */
    protected $_checkoutType = PayPalCheckout::class;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * Url Builder
     *
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var GuestCartRepositoryInterface
     */
    private $guestCartRepository;

    /**
     * @param Context $context
     * @param CustomerSession $customerSession
     * @param CheckoutSession $checkoutSession
     * @param OrderFactory $orderFactory
     * @param CheckoutFactory $checkoutFactory
     * @param PayPalSession $paypalSession
     * @param UrlHelper $urlHelper
     * @param CustomerUrl $customerUrl
     * @param CartRepositoryInterface $cartRepository
     * @param UrlInterface $urlBuilder
     * @param GuestCartRepositoryInterface $guestCartRepository
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
        CartRepositoryInterface $cartRepository,
        UrlInterface $urlBuilder,
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
        $this->cartRepository = $cartRepository;
        $this->urlBuilder = $urlBuilder;
        $this->guestCartRepository = $guestCartRepository;
    }

    /**
     * Place order or redirect on Paypal review page
     *
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $controllerResult = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $payerId = $this->getRequest()->getParam('payerId');
        $tokenId = $this->getRequest()->getParam('paymentToken');

        try {
            $quote = $this->_getQuote();

            $responseContent = [
                'success' => true,
                'error_message' => '',
            ];

            /** Populate checkout object with new data */
            $this->_initCheckout($quote);
            /**  Populate quote  with information about billing and shipping addresses*/
            $this->_checkout->returnFromPaypal($tokenId, $payerId);
            if ($this->_checkout->canSkipOrderReviewStep()) {
                $this->_checkout->place($tokenId);
                $order = $this->_checkout->getOrder();
                /** "last successful quote" */
                $this->_getCheckoutSession()->setLastQuoteId($quote->getId())->setLastSuccessQuoteId($quote->getId());

                $this->_getCheckoutSession()->setLastOrderId($order->getId())
                    ->setLastRealOrderId($order->getIncrementId())
                    ->setLastOrderStatus($order->getStatus());

                $this->_eventManager->dispatch(
                    'paypal_express_place_order_success',
                    [
                        'order' => $order,
                        'quote' => $quote
                    ]
                );
                $responseContent['redirectUrl'] = $this->urlBuilder->getUrl('checkout/onepage/success/');
            } else {
                $responseContent['redirectUrl'] = $this->urlBuilder->getUrl('paypal/express/review');
                $this->_checkoutSession->setQuoteId($quote->getId());
                $this->_getSession()->setQuoteId($quote->getId());
            }
        } catch (ApiProcessableException $e) {
            $responseContent['success'] = false;
            $responseContent['error_message'] = $e->getUserMessage();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $responseContent['success'] = false;
            $responseContent['error_message'] = $e->getMessage();
        } catch (\Exception $e) {
            $responseContent['success'] = false;
            $responseContent['error_message'] = __('We can\'t process Express Checkout approval.');
        }

        return $controllerResult->setData($responseContent);
    }
}
