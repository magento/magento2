<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Msrp\Model\Quote\Address;

/**
 * Msrp items total
 */
class Total extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
    /**
     * @var \Magento\Msrp\Helper\Data
     */
    protected $msrpData = null;

    /**
     * @param \Magento\Msrp\Helper\Data $msrpData
     */
    public function __construct(\Magento\Msrp\Helper\Data $msrpData)
    {
        $this->msrpData = $msrpData;
        $this->setCode('msrp');
    }

    /**
     * Collect information about MSRP price enabled
     *
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return $this
     * @api
     */
    public function collect(
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        parent::collect($shippingAssignment, $total);

        if (!count($shippingAssignment->getItems())) {
            return $this;
        }

        $canApplyMsrp = false;
        foreach ($shippingAssignment->getItems() as $item) {
            if (!$item->getParentItemId()
                    && $this->msrpData->isShowBeforeOrderConfirm($item->getProductId())
                    && $this->msrpData->isMinimalPriceLessMsrp($item->getProductId())
            ) {
                $canApplyMsrp = true;
                break;
            }
        }

        $total->setCanApplyMsrp($canApplyMsrp);
        return $this;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return array
     */
    public function fetch(\Magento\Quote\Model\Quote\Address\Total $total)
    {
        return [
            'code' => $this->getCode(),
            'title' => __('MSRP'),
            'can_apply_msrp' => (bool)$total->getCanApplyMsrp()
        ];
    }
}
