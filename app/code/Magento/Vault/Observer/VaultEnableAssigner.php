<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Observer;

use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Vault\Model\Ui\VaultConfigProvider;

/**
 * Class \Magento\Vault\Observer\VaultEnableAssigner
 *
 */
class VaultEnableAssigner extends AbstractDataAssignObserver
{
    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $data = $this->readDataArgument($observer);

        $additionalData = $data->getData(PaymentInterface::KEY_ADDITIONAL_DATA);

        if (!is_array($additionalData)) {
            return;
        }

        if (isset($additionalData[VaultConfigProvider::IS_ACTIVE_CODE])) {
            $payment = $this->readPaymentModelArgument($observer);
            $payment->setAdditionalInformation(
                VaultConfigProvider::IS_ACTIVE_CODE,
                filter_var($additionalData[VaultConfigProvider::IS_ACTIVE_CODE], FILTER_VALIDATE_BOOLEAN)
            );
        }
    }
}
