<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Vault\Observer;

use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Vault\Model\Ui\VaultConfigProvider;

/**
 * Sets visibility for Vault payment
 */
class VaultEnableAssigner extends AbstractDataAssignObserver
{
    /**
     * @inheritdoc
     */
    public function execute(Observer $observer)
    {
        $data = $this->readDataArgument($observer);

        $additionalData = $data->getData(PaymentInterface::KEY_ADDITIONAL_DATA);

        if (!is_array($additionalData)) {
            return;
        }

        $payment = $this->readPaymentModelArgument($observer);
        $isVisible = false;
        if (isset($additionalData[VaultConfigProvider::IS_ACTIVE_CODE])) {
            $isVisible = filter_var(
                $additionalData[VaultConfigProvider::IS_ACTIVE_CODE],
                FILTER_VALIDATE_BOOLEAN
            );
        }
        $payment->setAdditionalInformation(VaultConfigProvider::IS_ACTIVE_CODE, $isVisible);
    }
}
