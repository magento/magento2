<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Authorizenet\Model\Directpost;

use Magento\Authorizenet\Model\Request as AuthorizenetRequest;
use Magento\Framework\Intl\DateTimeFactory;

/**
 * Authorize.net request model for DirectPost model
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
     * @param DateTimeFactory $dateTimeFactory
     * @param array $data
     */
    public function __construct(
        DateTimeFactory $dateTimeFactory,
        array $data = []
    ) {
        $this->dateTimeFactory = $dateTimeFactory;
        parent::__construct($data);
    }

    /**
     * Return merchant transaction key.
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

        $this->setXTestRequest($paymentMethod->getConfigData('test') ? 'TRUE' : 'FALSE');

        $this->setXLogin($paymentMethod->getConfigData('login'))
            ->setXMethod(\Magento\Authorizenet\Model\Authorizenet::REQUEST_METHOD_CC)
            ->setXRelayUrl($paymentMethod->getRelayUrl());

        $this->_setTransactionKey($paymentMethod->getConfigData('trans_key'));
        $this->setSignatureKey($paymentMethod->getConfigData('signature_key'));
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

        //need to use strval() because NULL values IE6-8 decodes as "null" in JSON in JavaScript,
        //but we need "" for null values.
        $billing = $order->getBillingAddress();
        if (!empty($billing)) {
            $this->setXFirstName(strval($billing->getFirstname()))
                ->setXLastName(strval($billing->getLastname()))
                ->setXCompany(strval($billing->getCompany()))
                ->setXAddress(strval($billing->getStreetLine(1)))
                ->setXCity(strval($billing->getCity()))
                ->setXState(strval($billing->getRegion()))
                ->setXZip(strval($billing->getPostcode()))
                ->setXCountry(strval($billing->getCountryId()))
                ->setXPhone(strval($billing->getTelephone()))
                ->setXFax(strval($billing->getFax()))
                ->setXCustId(strval($billing->getCustomerId()))
                ->setXCustomerIp(strval($order->getRemoteIp()))
                ->setXCustomerTaxId(strval($billing->getTaxId()))
                ->setXEmail(strval($order->getCustomerEmail()))
                ->setXEmailCustomer(strval($paymentMethod->getConfigData('email_customer')))
                ->setXMerchantEmail(strval($paymentMethod->getConfigData('merchant_email')));
        }

        $shipping = $order->getShippingAddress();
        if (!empty($shipping)) {
            $this->setXShipToFirstName(
                strval($shipping->getFirstname())
            )->setXShipToLastName(
                strval($shipping->getLastname())
            )->setXShipToCompany(
                strval($shipping->getCompany())
            )->setXShipToAddress(
                strval($shipping->getStreetLine(1))
            )->setXShipToCity(
                strval($shipping->getCity())
            )->setXShipToState(
                strval($shipping->getRegion())
            )->setXShipToZip(
                strval($shipping->getPostcode())
            )->setXShipToCountry(
                strval($shipping->getCountryId())
            );
        }

        $this->setXPoNum(strval($payment->getPoNumber()));

        return $this;
    }

    /**
     * Set sign hash into the request object.
     * All needed fields should be placed in the object fist.
     *
     * @return $this
     */
    public function signRequestData()
    {
        $fpDate = $this->dateTimeFactory->create('now', new \DateTimeZone('UTC'));
        $fpTimestamp = $fpDate->getTimestamp();

        if (!empty($this->getSignatureKey())) {
            $hash = $this->generateSha2RequestSign(
                $this->getXLogin(),
                $this->getSignatureKey(),
                $this->getXAmount(),
                $this->getXCurrencyCode(),
                $this->getXFpSequence(),
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
     * @param string $fpTimestamp
     * @return string The fingerprint.
     */
    private function generateSha2RequestSign(
        $merchantApiLoginId,
        $merchantSignatureKey,
        $amount,
        $currencyCode,
        $fpSequence,
        $fpTimestamp
    ) {
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
    private function getSignatureKey()
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
    private function setSignatureKey($signatureKey)
    {
        $this->signatureKey = $signatureKey;
    }
}
