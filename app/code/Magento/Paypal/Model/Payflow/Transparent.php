<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Model\Payflow;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\State\InvalidTransitionException;
use Magento\Framework\Validator\Exception as ValidatorException;
use Magento\Payment\Gateway\Command\CommandException;
use Magento\Payment\Helper\Formatter;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Model\Method\ConfigInterfaceFactory;
use Magento\Payment\Model\Method\TransparentInterface;
use Magento\Payment\Model\MethodInterface;
use Magento\Paypal\Model\Payflow\Service\Gateway;
use Magento\Paypal\Model\Payflow\Service\Response\Handler\HandlerInterface;
use Magento\Paypal\Model\Payflow\Service\Response\Validator\ResponseValidator;
use Magento\Paypal\Model\Payflowpro;
use Magento\Sales\Api\Data\OrderPaymentExtensionInterfaceFactory;
use Magento\Sales\Model\Order\Payment;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Api\Data\PaymentTokenInterfaceFactory;

/**
 * Payflow Pro payment gateway model (transparent redirect).
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Transparent extends Payflowpro implements TransparentInterface
{
    use Formatter;

    public const CC_DETAILS = 'cc_details';

    public const CC_VAULT_CODE = 'payflowpro_cc_vault';

    /**
     * Result code of account verification transaction request.
     */
    private const RESULT_CODE = 'result_code';

    /**
     * Fraud Management Filters config setting.
     */
    private const CONFIG_FMF = 'fmf';

    /**
     * @var string
     */
    protected $_formBlockType = \Magento\Payment\Block\Transparent\Info::class;

    /**
     * @var string
     */
    protected $_infoBlockType = \Magento\Paypal\Block\Payment\Info::class;

    /**
     * Fetch transaction details availability option.
     *
     * @var bool
     */
    protected $_canFetchTransactionInfo = true;

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
     * @throws CommandException
     * @throws InvalidTransitionException
     * @throws ValidatorException
     * @throws LocalizedException
     */
    public function authorize(InfoInterface $payment, $amount)
    {
        if ($this->isFraudDetected($payment)) {
            $this->markPaymentAsFraudulent($payment);
            return $this;
        }

        $zeroAmountAuthorizationId = $this->getZeroAmountAuthorizationId($payment);
        /** @var PaymentTokenInterface $vaultPaymentToken */
        $vaultPaymentToken = $payment->getExtensionAttributes()->getVaultPaymentToken();
        /** @var Payment $payment */
        $request = $this->buildBasicRequest();

        /** @var \Magento\Sales\Model\Order $order */
        $order = $payment->getOrder();
        $this->addRequestOrderInfo($request, $order);
        $request = $this->fillCustomerContacts($order, $request);

        /** @var \Magento\Paypal\Model\Cart $payPalCart */
        $payPalCart = $this->payPalCartFactory->create(['salesModel' => $order]);
        $payPalCart->getAmounts();

        $parentTransactionId = $vaultPaymentToken ? $vaultPaymentToken->getGatewayToken() : $zeroAmountAuthorizationId;
        $request->setData('trxtype', self::TRXTYPE_AUTH_ONLY);
        $request->setData('origid', $parentTransactionId);
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
        } catch (ValidatorException $exception) {
            $payment->setParentTransactionId($response->getData(self::PNREF));
            $this->void($payment);
            throw new ValidatorException(__("The payment couldn't be processed at this time. Please try again later."));
        }

        $this->setTransStatus($payment, $response);

        if ($vaultPaymentToken) {
            $payment->setParentTransactionId($vaultPaymentToken->getGatewayToken());
        } else {
            $this->createPaymentToken($payment, $zeroAmountAuthorizationId);
        }

        $payment->unsAdditionalInformation(self::CC_DETAILS);
        $payment->unsAdditionalInformation(self::PNREF);
        $payment->unsAdditionalInformation(self::RESULT_CODE);

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
        if ($this->isFraudDetected($payment)) {
            $this->markPaymentAsFraudulent($payment);
            return $this;
        }

        /** @var Payment $payment */
        $zeroAmountAuthorizationId = $this->getZeroAmountAuthorizationId($payment);
        /** @var PaymentTokenInterface $vaultPaymentToken */
        $vaultPaymentToken = $payment->getExtensionAttributes()->getVaultPaymentToken();
        if ($vaultPaymentToken && empty($zeroAmountAuthorizationId) && empty($payment->getParentTransactionId())) {
            $payment->setAdditionalInformation(self::PNREF, $vaultPaymentToken->getGatewayToken());
            $payment->setParentTransactionId($vaultPaymentToken->getGatewayToken());
        }
        parent::capture($payment, $amount);

        if ($zeroAmountAuthorizationId && $vaultPaymentToken === null) {
            $this->createPaymentToken($payment, $zeroAmountAuthorizationId);
        }

        return $this;
    }

    /**
     * Attempt to accept a pending payment.
     *
     * Order acquires a payment review state based on results of PayPal account verification transaction (zero-amount
     * authorization). For accepting a payment should be created PayPal reference transaction with a real order amount.
     * Fraud Protection Service filters do not screen reference transactions.
     *
     * @param InfoInterface $payment
     * @return bool
     * @throws LocalizedException
     */
    public function acceptPayment(InfoInterface $payment)
    {
        $this->validatePaymentTransaction($payment);
        if ($this->getConfigPaymentAction() === MethodInterface::ACTION_AUTHORIZE_CAPTURE) {
            $invoices = iterator_to_array($payment->getOrder()->getInvoiceCollection());
            $invoice = count($invoices) ? reset($invoices) : null;
            $payment->capture($invoice);
        } else {
            $amount = $payment->getOrder()->getBaseGrandTotal();
            $payment->authorize(true, $amount);
        }

        return true;
    }

    /**
     * Deny a pending payment.
     *
     * Order acquires a payment review state based on results of PayPal account verification transaction (zero-amount
     * authorization). This transaction type cannot be voided, so we do not send any request to payment gateway.
     *
     * @param InfoInterface $payment
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function denyPayment(InfoInterface $payment)
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function fetchTransactionInfo(InfoInterface $payment, $transactionId)
    {
        $result = parent::fetchTransactionInfo($payment, $transactionId);
        $this->_canFetchTransactionInfo = false;
        if ($payment->getIsTransactionApproved()) {
            $this->acceptPayment($payment);
        }

        return $result;
    }

    /**
     * Marks payment as fraudulent.
     *
     * @param InfoInterface $payment
     * @throws \Exception
     */
    private function markPaymentAsFraudulent(InfoInterface $payment): void
    {
        $zeroAmountAuthorizationId = $this->getZeroAmountAuthorizationId($payment);
        $payment->setTransactionId($zeroAmountAuthorizationId);
        $payment->setIsTransactionClosed(0);
        $payment->setIsTransactionPending(true);
        $payment->setIsFraudDetected(true);
        $this->createPaymentToken($payment, $zeroAmountAuthorizationId);
        $fraudulentMsg = 'Order is suspended as an account verification transaction is suspected to be fraudulent.';
        $extensionAttributes = $this->getPaymentExtensionAttributes($payment);
        $extensionAttributes->setNotificationMessage($fraudulentMsg);
        $payment->unsAdditionalInformation(self::CC_DETAILS);
        $payment->unsAdditionalInformation(self::PNREF);
        $payment->unsAdditionalInformation(self::RESULT_CODE);
    }

    /**
     * Checks if fraud filters were triggered for the payment.
     *
     * For current PayPal PayflowPro transparent redirect integration
     * Fraud Protection Service filters screen only account verification
     * transaction (also known as zero dollar authorization).
     * Following reference transaction with real dollar amount will not be screened
     * by Fraud Protection Service.
     *
     * @param InfoInterface $payment
     * @return bool
     */
    private function isFraudDetected(InfoInterface $payment): bool
    {
        $resultCode = $payment->getAdditionalInformation(self::RESULT_CODE);
        $isFmfEnabled = (bool)$this->getConfig()->getValue(self::CONFIG_FMF);
        return $isFmfEnabled && $this->getZeroAmountAuthorizationId($payment) && in_array(
            $resultCode,
            [self::RESPONSE_CODE_DECLINED_BY_FILTER, self::RESPONSE_CODE_FRAUDSERVICE_FILTER]
        );
    }

    /**
     * Returns zero dollar authorization transaction id.
     *
     * PNREF (transaction id) is available in payment additional information only right after
     * PayPal account verification transaction (also known as zero dollar authorization).
     *
     * @param InfoInterface $payment
     * @return string
     */
    private function getZeroAmountAuthorizationId(InfoInterface $payment): string
    {
        return (string)$payment->getAdditionalInformation(self::PNREF);
    }

    /**
     * Validates payment transaction status on PayPal.
     *
     * @param InfoInterface $payment
     * @throws LocalizedException
     */
    private function validatePaymentTransaction(InfoInterface $payment): void
    {
        if ($payment->canFetchTransactionInfo()) {
            $transactionId = $payment->getLastTransId();
            parent::fetchTransactionInfo($payment, $transactionId);
            $this->_canFetchTransactionInfo = false;
            if ($payment->getIsTransactionDenied()) {
                throw new LocalizedException(
                    __('Payment can\'t be accepted since transaction was rejected by merchant.')
                );
            }
        }
    }
}
