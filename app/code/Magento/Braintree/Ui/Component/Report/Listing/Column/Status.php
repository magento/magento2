<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Ui\Component\Report\Listing\Column;

use Braintree\Transaction;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Status
 */
class Status implements OptionSourceInterface
{
    /**
     * @var array
     */
    protected $options;

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->options !== null) {
            return $this->options;
        }

        $statuses = $this->getAvailableStatuses();
        foreach ($statuses as $statusCode => $statusName) {
            $this->options[$statusCode]['label'] = $statusName;
            $this->options[$statusCode]['value'] = $statusCode;
        }

        return $this->options;
    }

    /**
     * @return array
     */
    private function getAvailableStatuses()
    {
        return [
            Transaction::AUTHORIZATION_EXPIRED => __('Authorization expired'),
            Transaction::AUTHORIZING => __('Authorizing'),
            Transaction::AUTHORIZED => __('Authorized'),
            Transaction::GATEWAY_REJECTED => __('Gateway rejected'),
            Transaction::FAILED => __('Failed'),
            Transaction::PROCESSOR_DECLINED => __('Processor declined'),
            Transaction::SETTLED => __('Settled'),
            Transaction::SETTLING => __('Settling'),
            Transaction::SUBMITTED_FOR_SETTLEMENT => __('Submitted for settlement'),
            Transaction::VOIDED => __('Voided'),
            Transaction::UNRECOGNIZED => __('Unrecognized'),
            Transaction::SETTLEMENT_DECLINED => __('Settlement declined'),
            Transaction::SETTLEMENT_PENDING => __('Settlement pending'),
            Transaction::SETTLEMENT_CONFIRMED => __('Settlement confirmed')
        ];
    }
}
