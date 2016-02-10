<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Observer;

use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Vault\Model\Ui\VaultConfigProvider;

class VaultEnableAssigner extends AbstractDataAssignObserver
{
    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $data = $this->readDataArgument($observer);

        if (isset($data[VaultConfigProvider::IS_ACTIVE_CODE])) {
            $payment = $this->readPaymentModelArgument($observer);
            $payment->setAdditionalInformation(
                VaultConfigProvider::IS_ACTIVE_CODE,
                filter_var($data[VaultConfigProvider::IS_ACTIVE_CODE], FILTER_VALIDATE_BOOLEAN)
            );
        }
    }
}
