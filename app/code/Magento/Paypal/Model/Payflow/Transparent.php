<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Model\Payflow;

use Magento\Payment\Helper\Formatter;
use Magento\Payment\Model\InfoInterface;
use Magento\Paypal\Model\Payflowpro;
use Magento\Sales\Api\Data\OrderPaymentExtensionInterfaceFactory;
use Magento\Sales\Model\Order\Payment;
use Magento\Paypal\Model\Payflow\Service\Gateway;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Model\Method\TransparentInterface;
use Magento\Payment\Model\Method\ConfigInterfaceFactory;
use Magento\Framework\Exception\State\InvalidTransitionException;
use Magento\Paypal\Model\Payflow\Service\Response\Handler\HandlerInterface;
use Magento\Paypal\Model\Payflow\Service\Response\Validator\ResponseValidator;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Api\Data\PaymentTokenInterfaceFactory;

/**
 * Payflow Pro payment gateway model
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Transparent extends Payflowpro implements TransparentInterface
{
    use Formatter;

    const CC_DETAILS = 'cc_details';

    const CC_VAULT_CODE = 'payflowpro_cc_vault';

    /**
     * @var string
     */
    protected $_formBlockType = \Magento\Payment\Block\Transparent\Info::class;

    /**
     * @var string
     */
    protected $_infoBlockType = \Magento\Paypal\Block\Payment\Info::class;

    /**
     * @var ResponseValidator
     */
    private $responseValidator;

    /**
     * @var PaymentTokenInterfaceFactory
     */
    private $paymentTokenFactory;

    /**
     * @var OrderPaymentExtensionInterfaceFactory
     */
    private $paymentExtensionFactory;

    /**
     * @var \Magento\Paypal\Model\CartFactory
     */
    private $payPalCartFactory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Payment\Model\Method\Logger $logger
     * @param \Magento\Framework\Module\ModuleListInterface $moduleList
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param ConfigInterfaceFactory $configFactory
     * @param Gateway $gateway
     * @param HandlerInterface $errorHandler
     * @param ResponseValidator $responseValidator
     * @param PaymentTokenInterfaceFactory $paymentTokenFactory
     * @param OrderPaymentExtensionInterfaceFactory $paymentExtensionFactory
     * @param \Magento\Paypal\Model\CartFactory $payPalCartFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        ConfigInterfaceFactory $configFactory,
        Gateway $gateway,
        HandlerInterface $errorHandler,
        ResponseValidator $responseValidator,
        PaymentTokenInterfaceFactory $paymentTokenFactory,
        OrderPaymentExtensionInterfaceFactory $paymentExtensionFactory,
        \Magento\Paypal\Model\CartFactory $payPalCartFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $moduleList,
            $localeDate,
            $storeManager,
            $configFactory,
            $gateway,
            $errorHandler,
            $resource,
            $resourceCollection,
            $data
        );
        $this->responseValidator = $responseValidator;
        $this->paymentTokenFactory = $paymentTokenFactory;
        $this->paymentExtensionFactory = $paymentExtensionFactory;
        $this->payPalCartFactory = $payPalCartFactory;
    }

    /**
     * Gets response validator instance.
     *
     * @return ResponseValidator
     */
    public function getResponceValidator()
    {
        return $this->responseValidator;
    }

    /**
     * Do not validate payment form using server methods
     *
     * @return bool
     */
    public function validate()
    {
        return true;
    }

    /**
     * Performs authorize transaction
     *
     * @param InfoInterface|Object $payment
     * @param float $amount
     * @return $this
     * @throws InvalidTransitionException
     * @throws LocalizedException
     */
    public function authorize(InfoInterface $payment, $amount)
    {
        /** @var Payment $payment */
        $request = $this->buildBasicRequest();

        /** @var \Magento\Sales\Model\Order $order */
        $order = $payment->getOrder();
        $this->addRequestOrderInfo($request, $order);
        $request = $this->fillCustomerContacts($order, $request);

        /** @var \Magento\Paypal\Model\Cart $payPalCart */
        $payPalCart = $this->payPalCartFactory->create(['salesModel' => $order]);
        $payPalCart->getAmounts();

        $token = $payment->getAdditionalInformation(self::PNREF);
        $request->setData('trxtype', self::TRXTYPE_AUTH_ONLY);
        $request->setData('origid', $token);
        $request->setData('amt', $this->formatPrice($amount));
        $request->setData('currency', $order->getBaseCurrencyCode());
        $request->setData('itemamt', $this->formatPrice($payPalCart->getSubtotal()));
        $request->setData('taxamt', $this->formatPrice($payPalCart->getTax()));
        $request->setData('freightamt', $this->formatPrice($payPalCart->getShipping()));
        $request->setData('discount', $this->formatPrice($payPalCart->getDiscount()));

        $response = $this->postRequest($request, $this->getConfig());
        $this->processErrors($response);

        try {
            $this->responseValidator->validate($response, $this);
        } catch (LocalizedException $exception) {
            $payment->setParentTransactionId($response->getData(self::PNREF));
            $this->void($payment);
            throw new LocalizedException(__("The payment couldn't be processed at this time. Please try again later."));
        }

        $this->setTransStatus($payment, $response);

        $this->createPaymentToken($payment, $token);

        $payment->unsAdditionalInformation(self::CC_DETAILS);
        $payment->unsAdditionalInformation(self::PNREF);

        return $this;
    }

    /**
     * {inheritdoc}
     */
    public function getConfigInterface()
    {
        return parent::getConfig();
    }

    /**
     * Creates vault payment token.
     *
     * @param Payment $payment
     * @param string $token
     * @return void
     * @throws \Exception
     */
    protected function createPaymentToken(Payment $payment, $token)
    {
        /** @var PaymentTokenInterface $paymentToken */
        $paymentToken = $this->paymentTokenFactory->create();

        $paymentToken->setGatewayToken($token);
        $paymentToken->setTokenDetails(
            json_encode($payment->getAdditionalInformation(Transparent::CC_DETAILS))
        );
        $paymentToken->setExpiresAt(
            $this->getExpirationDate($payment)
        );

        $this->getPaymentExtensionAttributes($payment)->setVaultPaymentToken($paymentToken);
    }

    /**
     * Generates CC expiration date by year and month provided in payment.
     *
     * @param Payment $payment
     * @return string
     * @throws \Exception
     */
    private function getExpirationDate(Payment $payment)
    {
        $expDate = new \DateTime(
            $payment->getCcExpYear()
            . '-'
            . $payment->getCcExpMonth()
            . '-'
            . '01'
            . ' '
            . '00:00:00',
            new \DateTimeZone('UTC')
        );
        $expDate->add(new \DateInterval('P1M'));
        return $expDate->format('Y-m-d 00:00:00');
    }

    /**
     * Returns payment extension attributes instance.
     *
     * @param Payment $payment
     * @return \Magento\Sales\Api\Data\OrderPaymentExtensionInterface
     */
    private function getPaymentExtensionAttributes(Payment $payment)
    {
        $extensionAttributes = $payment->getExtensionAttributes();
        if ($extensionAttributes === null) {
            $extensionAttributes = $this->paymentExtensionFactory->create();
            $payment->setExtensionAttributes($extensionAttributes);
        }

        return $extensionAttributes;
    }

    /**
     * Capture payment
     *
     * @param InfoInterface|Payment $payment
     * @param float $amount
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\State\InvalidTransitionException
     */
    public function capture(InfoInterface $payment, $amount)
    {
        /** @var Payment $payment */
        $token = $payment->getAdditionalInformation(self::PNREF);
        parent::capture($payment, $amount);

        if ($token && !$payment->getAuthorizationTransaction()) {
            $this->createPaymentToken($payment, $token);
        }

        return $this;
    }
}
