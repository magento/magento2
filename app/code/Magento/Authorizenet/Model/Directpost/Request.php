<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Authorizenet\Model\Directpost;

use Magento\Authorizenet\Model\Request as AuthorizenetRequest;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Intl\DateTimeFactory;

/**
 * Authorize.net request model for DirectPost model
 * @deprecated 2.3.1 Authorize.net is removing all support for this payment method
 */
class Request extends AuthorizenetRequest
{
    /**
     * @var string
     */
    protected $_transKey = null;

    /**
     * Hexadecimal signature key.
     *
     * @var string
     */
    private $signatureKey = '';

    /**
     * @var DateTimeFactory
     */
    private $dateTimeFactory;

    /**
     * @param array $data
     * @param DateTimeFactory $dateTimeFactory
     */
    public function __construct(
        array $data = [],
        DateTimeFactory $dateTimeFactory = null
    ) {
        $this->dateTimeFactory = $dateTimeFactory ?? ObjectManager::getInstance()
                ->get(DateTimeFactory::class);
        parent::__construct($data);
    }

    /**
     * Return merchant transaction key.
     *
     * Needed to generate MD5 sign.
     *
     * @return string
     */
    protected function _getTransactionKey()
    {
        return $this->_transKey;
    }

    /**
     * Set merchant transaction key.
     *
     * Needed to generate MD5 sign.
     *
     * @param string $transKey
     * @return $this
     */
    protected function _setTransactionKey($transKey)
    {
        $this->_transKey = $transKey;
        return $this;
    }

    /**
     * Generates the MD5 fingerprint for request.
     *
     * @param string $merchantApiLoginId
     * @param string $merchantTransactionKey
     * @param string $amount
     * @param string $currencyCode
     * @param string $fpSequence An invoice number or random number.
     * @param string $fpTimestamp
     * @return string The fingerprint.
     */
    public function generateRequestSign(
        $merchantApiLoginId,
        $merchantTransactionKey,
        $amount,
        $currencyCode,
        $fpSequence,
        $fpTimestamp
    ) {
        return hash_hmac(
            "md5",
            $merchantApiLoginId . '^' . $fpSequence . '^' . $fpTimestamp . '^' . $amount . '^' . $currencyCode,
            $merchantTransactionKey
        );
    }

    /**
     * Set Authorizenet data to request.
     *
     * @param \Magento\Authorizenet\Model\Directpost $paymentMethod
     * @return $this
     */
    public function setConstantData(\Magento\Authorizenet\Model\Directpost $paymentMethod)
    {
        $this->setXVersion('3.1')->setXDelimData('FALSE')->setXRelayResponse('TRUE');

        $this->setSignatureKey($paymentMethod->getConfigData('signature_key'));

        $this->setXLogin($paymentMethod->getConfigData('login'))
            ->setXMethod(\Magento\Authorizenet\Model\Authorizenet::REQUEST_METHOD_CC)
            ->setXRelayUrl($paymentMethod->getRelayUrl());

        $this->_setTransactionKey($paymentMethod->getConfigData('trans_key'));
        return $this;
    }

    /**
     * Set entity data to request
     *
     * @param \Magento\Sales\Model\Order $order
     * @param \Magento\Authorizenet\Model\Directpost $paymentMethod
     * @return $this
     */
    public function setDataFromOrder(
        \Magento\Sales\Model\Order $order,
        \Magento\Authorizenet\Model\Directpost $paymentMethod
    ) {
        $payment = $order->getPayment();

        $this->setXType($payment->getAnetTransType());
        $this->setXFpSequence($order->getQuoteId());
        $this->setXInvoiceNum($order->getIncrementId());
        $this->setXAmount($payment->getBaseAmountAuthorized());
        $this->setXCurrencyCode($order->getBaseCurrencyCode());
        $this->setXTax(
            sprintf('%.2F', $order->getBaseTaxAmount())
        )->setXFreight(
            sprintf('%.2F', $order->getBaseShippingAmount())
        );

        //need to use (string) because NULL values IE6-8 decodes as "null" in JSON in JavaScript,
        //but we need "" for null values.
        $billing = $order->getBillingAddress();
        if (!empty($billing)) {
            $this->setXFirstName((string)$billing->getFirstname())
                ->setXLastName((string)$billing->getLastname())
                ->setXCompany((string)$billing->getCompany())
                ->setXAddress((string)$billing->getStreetLine(1))
                ->setXCity((string)$billing->getCity())
                ->setXState((string)$billing->getRegion())
                ->setXZip((string)$billing->getPostcode())
                ->setXCountry((string)$billing->getCountryId())
                ->setXPhone((string)$billing->getTelephone())
                ->setXFax((string)$billing->getFax())
                ->setXCustId((string)$billing->getCustomerId())
                ->setXCustomerIp((string)$order->getRemoteIp())
                ->setXCustomerTaxId((string)$billing->getTaxId())
                ->setXEmail((string)$order->getCustomerEmail())
                ->setXEmailCustomer((string)$paymentMethod->getConfigData('email_customer'))
                ->setXMerchantEmail((string)$paymentMethod->getConfigData('merchant_email'));
        }

        $shipping = $order->getShippingAddress();
        if (!empty($shipping)) {
            $this->setXShipToFirstName(
                (string)$shipping->getFirstname()
            )->setXShipToLastName(
                (string)$shipping->getLastname()
            )->setXShipToCompany(
                (string)$shipping->getCompany()
            )->setXShipToAddress(
                (string)$shipping->getStreetLine(1)
            )->setXShipToCity(
                (string)$shipping->getCity()
            )->setXShipToState(
                (string)$shipping->getRegion()
            )->setXShipToZip(
                (string)$shipping->getPostcode()
            )->setXShipToCountry(
                (string)$shipping->getCountryId()
            );
        }

        $this->setXPoNum((string)$payment->getPoNumber());

        return $this;
    }

    /**
     * Set sign hash into the request object.
     *
     * All needed fields should be placed in the object first.
     *
     * @return $this
     */
    public function signRequestData()
    {
        $fpDate = $this->dateTimeFactory->create('now', new \DateTimeZone('UTC'));
        $fpTimestamp = $fpDate->getTimestamp();

        if (!empty($this->getSignatureKey())) {
            $hash = $this->generateSha2RequestSign(
                (string)$this->getXLogin(),
                (string)$this->getSignatureKey(),
                (string)$this->getXAmount(),
                (string)$this->getXCurrencyCode(),
                (string)$this->getXFpSequence(),
                $fpTimestamp
            );
        } else {
            $hash = $this->generateRequestSign(
                $this->getXLogin(),
                $this->_getTransactionKey(),
                $this->getXAmount(),
                $this->getXCurrencyCode(),
                $this->getXFpSequence(),
                $fpTimestamp
            );
        }

        $this->setXFpTimestamp($fpTimestamp);
        $this->setXFpHash($hash);

        return $this;
    }

    /**
     * Generates the SHA2 fingerprint for request.
     *
     * @param string $merchantApiLoginId
     * @param string $merchantSignatureKey
     * @param string $amount
     * @param string $currencyCode
     * @param string $fpSequence An invoice number or random number.
     * @param int $fpTimestamp
     * @return string The fingerprint.
     */
    private function generateSha2RequestSign(
        string $merchantApiLoginId,
        string $merchantSignatureKey,
        string $amount,
        string $currencyCode,
        string $fpSequence,
        int $fpTimestamp
    ): string {
        $message = $merchantApiLoginId . '^' . $fpSequence . '^' . $fpTimestamp . '^' . $amount . '^' . $currencyCode;

        return strtoupper(hash_hmac('sha512', $message, pack('H*', $merchantSignatureKey)));
    }

    /**
     * Return merchant hexadecimal signature key.
     *
     * Needed to generate SHA2 sign.
     *
     * @return string
     */
    private function getSignatureKey(): string
    {
        return $this->signatureKey;
    }

    /**
     * Set merchant hexadecimal signature key.
     *
     * Needed to generate SHA2 sign.
     *
     * @param string $signatureKey
     * @return void
     */
    private function setSignatureKey(string $signatureKey)
    {
        $this->signatureKey = $signatureKey;
    }
}
