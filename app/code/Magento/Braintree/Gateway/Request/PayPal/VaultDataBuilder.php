<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Gateway\Request\PayPal;

use Magento\Braintree\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Vault\Model\Ui\VaultConfigProvider;

/**
 * Vault Data Builder
 */
class VaultDataBuilder implements BuilderInterface
{
    /**
     * Additional options in request to gateway
     */
    private static $optionsKey = 'options';

    /**
     * The option that determines whether the payment method associated with
     * the successful transaction should be stored in the Vault.
     */
    private static $storeInVaultOnSuccess = 'storeInVaultOnSuccess';

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * VaultDataBuilder constructor.
     * @param SubjectReader $subjectReader
     */
    public function __construct(SubjectReader $subjectReader)
    {
        $this->subjectReader = $subjectReader;
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        $result = [];
        $paymentDO = $this->subjectReader->readPayment($buildSubject);

        $payment = $paymentDO->getPayment();
        $data = $payment->getAdditionalInformation();
        if (!empty($data[VaultConfigProvider::IS_ACTIVE_CODE])) {
            $result[self::$optionsKey] = [
                self::$storeInVaultOnSuccess => true
            ];
        }

        return $result;
    }
}
