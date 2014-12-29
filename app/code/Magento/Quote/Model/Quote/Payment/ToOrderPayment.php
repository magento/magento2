<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Quote\Model\Quote\Payment;

use Magento\Sales\Api\Data\OrderPaymentDataBuilder as OrderPaymentBuilder;
use Magento\Sales\Api\Data\OrderPaymentInterface;

/**
 * Class ToOrderPayment
 */
class ToOrderPayment
{
    protected $fields = [
        'method', 'additional_data', 'additional_information', 'po_number', 'cc_type', 'cc_number_enc', 'cc_last_4',
        'cc_owner', 'cc_exp_month', 'cc_exp_year', 'cc_number', 'cc_cid', 'cc_ss_issue', 'cc_ss_start_month',
        'cc_ss_start_year'
    ];

    /**
     * @var OrderPaymentBuilder|\Magento\Framework\Api\Builder
     */
    protected $orderPaymentBuilder;

    public function __construct(
        OrderPaymentBuilder $orderPaymentBuilder
    ) {
        $this->orderPaymentBuilder = $orderPaymentBuilder;
    }

    /**
     * @param array $data
     * @return OrderPaymentInterface
     */
    public function convert(\Magento\Quote\Model\Quote\Payment $object, $data = [])
    {
        return $this->orderPaymentBuilder
            ->populateWithArray(array_merge(array_intersect_key($object->getData(), array_flip($this->fields)), $data))
            ->create();
    }
}
