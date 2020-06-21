<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\PaypalGraphQl\Observer;

use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Api\Data\PaymentInterface;

/**
 * Class PayflowProSetCcData set CcData to quote payment
 */
class PayflowProSetCcData extends AbstractDataAssignObserver
{
    /**
     * Set CcData
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $dataObject = $this->readDataArgument($observer);
        $additionalData = $dataObject->getData(PaymentInterface::KEY_ADDITIONAL_DATA);

        if (!isset($additionalData['cc_details'])) {
            return;
        }

        $paymentModel = $this->readPaymentModelArgument($observer);
        foreach ($additionalData['cc_details'] as $ccKey => $ccValue) {
            $paymentModel->setData($ccKey, $ccValue);
        }
    }
}
