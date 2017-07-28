<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Observer;

use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Paypal\Model\Payflow\Transparent;
use Magento\Quote\Api\Data\PaymentInterface;

/**
 * Class \Magento\Paypal\Observer\PayflowProAddCcData
 *
 * @since 2.1.0
 */
class PayflowProAddCcData extends AbstractDataAssignObserver
{
    /**
     * @var array
     * @since 2.1.0
     */
    private $ccKeys = [
        'cc_type',
        'cc_exp_year',
        'cc_exp_month',
        'cc_last_4'
    ];

    /**
     * @param Observer $observer
     * @return void
     * @since 2.1.0
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $dataObject = $this->readDataArgument($observer);

        $additionalData = $dataObject->getData(PaymentInterface::KEY_ADDITIONAL_DATA);

        if (!is_array($additionalData)) {
            return;
        }

        $ccData = array_intersect_key($additionalData, array_flip($this->ccKeys));
        if (count($ccData) !== count($this->ccKeys)) {
            return;
        }
        $paymentModel = $this->readPaymentModelArgument($observer);

        $paymentModel->setAdditionalInformation(
            Transparent::CC_DETAILS,
            $this->sortCcData($ccData)
        );

        // CC data should be stored explicitly
        foreach ($ccData as $ccKey => $ccValue) {
            $paymentModel->setData($ccKey, $ccValue);
        }
    }

    /**
     * @param array $ccData
     * @return array
     * @since 2.1.0
     */
    private function sortCcData(array $ccData)
    {
        $r = [];
        foreach ($this->ccKeys as $key) {
            $r[$key] = isset($ccData[$key]) ? $ccData[$key] : null;
        }

        return $r;
    }
}
