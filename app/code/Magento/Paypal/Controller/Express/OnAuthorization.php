<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Controller\Express;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\HttpPostActionInterface;

class OnAuthorization
    extends AbstractExpress
    implements HttpPostActionInterface
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
     * @var \Magento\Quote\Model\Quote\PaymentFactory
     */
    protected $cartRepository;

    /**
     * Url Builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;

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
     * @param \Magento\Checkout\Api\AgreementsValidatorInterface $agreementValidator
     * @param \Magento\Sales\Api\PaymentFailuresInterface|null $paymentFailures
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
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
        \Magento\Checkout\Api\AgreementsValidatorInterface $agreementValidator,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepository,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Quote\Api\GuestCartRepositoryInterface $guestCartRepository
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
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $controllerResult = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $params = $this->getRequest()->getParams();
        $quoteId = $params['quoteId'];
        $payerId = $params['payerId'];
        $tokenId = $params['paymentToken'];
        $customerId = $params['customerId'];
        try {
            $quote = $customerId ? $this->cartRepository->get($quoteId) : $this->guestCartRepository->get($quoteId);

            $responseContent = [
                'success' => true,
                'error_message' => '',
            ];

            //populate checkout object with new data
            $this->_initCheckout($quote);
            /**  Populate quote  with information about billing and shipping addresses*/
            $this->_checkout->returnFromPaypal($tokenId, $payerId);
            if ($this->_checkout->canSkipOrderReviewStep()) {
                $this->_checkout->place($tokenId);
                $order = $this->_checkout->getOrder();
                // "last successful quote"
                $this->_getCheckoutSession()->setLastQuoteId($quote->getId())->setLastSuccessQuoteId($quote->getId());

                if ($order) {
                    $this->_getCheckoutSession()->setLastOrderId($order->getId())
                        ->setLastRealOrderId($order->getIncrementId())
                        ->setLastOrderStatus($order->getStatus());
                }

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

            }
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
