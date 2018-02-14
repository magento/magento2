<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Observer;

use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\ResourceModel\Order\Payment\Collection;

/**
 * Class ConvertAdditionalInfo convert additional info from multidimensional array into single one for API calls.
 */
class ConvertAdditionalInfoObserver implements ObserverInterface
{
    /**
     * Application state for identifying API calls.
     *
     * @var State
     */
    private $state;

    /**
     * ConvertAdditionalInfoObserver constructor.
     * @param State $state
     */
    public function __construct(State $state)
    {
        $this->state = $state;
    }

    /**
     * Convert additional info from multidimensional array into single one for API calls.
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var Collection $paymentCollection */
        $paymentCollection = $observer->getData('order_payment_collection');
        $areaCode = $this->state->getAreaCode();
        if ($areaCode == Area::AREA_WEBAPI_REST || $areaCode == Area::AREA_WEBAPI_SOAP) {
            foreach ($paymentCollection as $payment) {
                if (!empty($payment->getData(OrderPaymentInterface::ADDITIONAL_INFORMATION))) {
                    $additionalInfo = $this->convertAdditionalInfo(
                        $payment->getData(OrderPaymentInterface::ADDITIONAL_INFORMATION)
                    );
                    $payment->setData(OrderPaymentInterface::ADDITIONAL_INFORMATION, $additionalInfo);
                }
            }
        }
    }

    /**
     * Convert multidimensional additional information array to single.
     *
     * @param array $info
     * @return array
     */
    private function convertAdditionalInfo($info)
    {
        $result = [];
        foreach ($info as $key => $item) {
            if (is_array($item)) {
                $result += $this->convertAdditionalInfo($item);
                unset($info[$key]);
            } else {
                $result[$key] = $item;
            }
        }

        return $result;
    }
}
