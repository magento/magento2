<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);


namespace Magento\Paypal\Controller\Express;

use Magento\Paypal\Model\Api\ProcessableException as ApiProcessableException;
use Magento\Framework\Controller\ResultFactory;

class OnAuthorization
    extends \Magento\Paypal\Controller\Express\AbstractExpress
    implements \Magento\Framework\App\Action\HttpPostActionInterface
{
    /**
     * @var \Magento\Checkout\Api\AgreementsValidatorInterface
     */
    protected $agreementsValidator;

    /**
     * @var \Magento\Sales\Api\PaymentFailuresInterface
     */
    private $paymentFailures;

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
     * @var \Magento\Checkout\Api\PaymentInformationManagementInterface
     */
    private $paymentInformationService;

    /**
     * @var \Magento\Quote\Model\Quote\PaymentFactory
     */
    protected $cartRepository;

    /**
     * @var \Magento\Quote\Api\CartManagementInterface
     */
    private $quoteManagement;

    /**
     * Url Builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;

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
        \Magento\Sales\Api\PaymentFailuresInterface $paymentFailures = null,
        \Magento\Checkout\Api\PaymentInformationManagementInterface $paymentInformationService,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepository,
        \Magento\Quote\Api\CartManagementInterface $quoteManagement,
        \Magento\Framework\UrlInterface $urlBuilder
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

        $this->agreementsValidator = $agreementValidator;
        $this->paymentFailures = $paymentFailures ? : $this->_objectManager->get(
            \Magento\Sales\Api\PaymentFailuresInterface::class
        );
        $this->paymentInformationService = $paymentInformationService;
        $this->cartRepository = $cartRepository;
        $this->quoteManagement = $quoteManagement;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        $controllerResult = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $params = $this->getRequest()->getParams();
        $quoteId = $params['quoteId'];
        $payerId = $params['payerId'];
        $tokenId = $params['paymentToken'];
        /** @var \Magento\Quote\Api\Data\CartInterface $quote */
        //@todo add logic  or separate controller to load quote for quest
        $quote = $this->cartRepository->get($quoteId);

        $responseContent = [
            'success' => true,
            'error_message' => '',
        ];

        try {
            //populate checkout object with new data
            $this->_initCheckout($quote);
            $this->_checkout->returnFromPaypal($tokenId, $payerId);
            if ($this->_checkout->canSkipOrderReviewStep()) {
                //$this->_checkout->place($tokenId);
                /**  Populate quote  with information about billing and shipping addresses*/
                $this->_checkout->returnFromPaypal($tokenId);
                /** we are using quoteManagement::submit here to prevent billing address/shipping validation if we return from PayPal  */
                $order = $this->quoteManagement->submit($quote);
                // prepare session to success or cancellation page
                $this->_getCheckoutSession()->clearHelperData();

                // "last successful quote"
                $this->_getCheckoutSession()->setLastQuoteId($quoteId)->setLastSuccessQuoteId($quoteId);

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
                $this->_checkoutSession->setQuoteId($quoteId);

            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $responseContent['success'] = false;
            $responseContent['error_message'] = $e->getMessage();

        } catch (\Exception $e) {
            $responseContent['success'] = false;
            $responseContent['error_message'] = 'We can\'t process Express Checkout approval.';
        }

        return $controllerResult->setData($responseContent);

    }
}
