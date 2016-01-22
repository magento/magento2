<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Observer;

use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Paypal\Model\Payflow\Transparent;

class PayflowProAddCcData extends AbstractDataAssignObserver
{
    /**
     * @var array
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
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $dataObject = $this->readDataArgument($observer);

        $ccData = array_intersect_key($dataObject->getData(), array_flip($this->ccKeys));
        if (count($ccData) !== count($this->ccKeys)) {
            return;
        }
        $paymentModel = $this->readPaymentModelArgument($observer);

        $paymentModel->setAdditionalInformation(
            Transparent::CC_DETAILS,
            $this->sortCcData($ccData)
        );
    }

    /**
     * @param array $ccData
     * @return array
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
