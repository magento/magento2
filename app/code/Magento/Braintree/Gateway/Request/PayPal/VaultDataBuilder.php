<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Gateway\Request\PayPal;

use Magento\Braintree\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Vault\Model\Ui\VaultConfigProvider;

/**
 * Vault Data Builder
 * @since 2.1.3
 */
class VaultDataBuilder implements BuilderInterface
{
    /**
     * Additional options in request to gateway
     * @since 2.1.3
     */
    private static $optionsKey = 'options';

    /**
     * The option that determines whether the payment method associated with
     * the successful transaction should be stored in the Vault.
     * @since 2.1.3
     */
    private static $storeInVaultOnSuccess = 'storeInVaultOnSuccess';

    /**
     * @var SubjectReader
     * @since 2.1.3
     */
    private $subjectReader;

    /**
     * VaultDataBuilder constructor.
     * @param SubjectReader $subjectReader
     * @since 2.1.3
     */
    public function __construct(SubjectReader $subjectReader)
    {
        $this->subjectReader = $subjectReader;
    }

    /**
     * @inheritdoc
     * @since 2.1.3
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
