<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Braintree\Gateway\Request;

use Magento\Braintree\Gateway\Config\Config;
use Magento\Braintree\Gateway\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Adds Merchant Account ID to the request if it was specified in the configuration.
 */
class MerchantAccountDataBuilder implements BuilderInterface
{
    /**
     * The merchant account ID used to create a transaction.
     * Currency is also determined by merchant account ID.
     * If no merchant account ID is specified, Braintree will use your default merchant account.
     */
    private static $merchantAccountId = 'merchantAccountId';

    /**
     * @var Config
     */
    private $config;

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * Constructor
     *
     * @param Config $config
     * @param SubjectReader $subjectReader
     */
    public function __construct(Config $config, SubjectReader $subjectReader)
    {
        $this->config = $config;
        $this->subjectReader = $subjectReader;
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject): array
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $order = $paymentDO->getOrder();

        $result = [];
        $merchantAccountId = $this->config->getMerchantAccountId($order->getStoreId());
        if (!empty($merchantAccountId)) {
            $result[self::$merchantAccountId] = $merchantAccountId;
        }

        return $result;
    }
}
