<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Gateway\Request;

use Magento\Braintree\Gateway\Config\Config;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Braintree\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Helper\Formatter;

/**
 * Class ThreeDSecureDataBuilder
 * @since 2.1.0
 */
class ThreeDSecureDataBuilder implements BuilderInterface
{
    use Formatter;

    /**
     * @var Config
     * @since 2.1.0
     */
    private $config;

    /**
     * @var SubjectReader
     * @since 2.1.0
     */
    private $subjectReader;

    /**
     * Constructor
     *
     * @param Config $config
     * @param SubjectReader $subjectReader
     * @since 2.1.0
     */
    public function __construct(Config $config, SubjectReader $subjectReader)
    {
        $this->config = $config;
        $this->subjectReader = $subjectReader;
    }

    /**
     * @inheritdoc
     * @since 2.1.0
     */
    public function build(array $buildSubject)
    {
        $result = [];

        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $amount = $this->formatPrice($this->subjectReader->readAmount($buildSubject));

        if ($this->is3DSecureEnabled($paymentDO->getOrder(), $amount)) {
            $result['options'][Config::CODE_3DSECURE] = ['required' => true];
        }
        return $result;
    }

    /**
     * Check if 3d secure is enabled
     * @param OrderAdapterInterface $order
     * @param float $amount
     * @return bool
     * @since 2.1.0
     */
    private function is3DSecureEnabled(OrderAdapterInterface $order, $amount)
    {
        if (!$this->config->isVerify3DSecure() || $amount < $this->config->getThresholdAmount()) {
            return false;
        }

        $billingAddress = $order->getBillingAddress();
        $specificCounties = $this->config->get3DSecureSpecificCountries();
        if (!empty($specificCounties) && !in_array($billingAddress->getCountryId(), $specificCounties)) {
            return false;
        }

        return true;
    }
}
