<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Msrp\Model\Quote\Address;

/**
 * Msrp items total
 */
class Total extends \Magento\Sales\Model\Quote\Address\Total\AbstractTotal
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
    }

    /**
     * Collect information about MSRP price enabled
     *
     * @param  \Magento\Sales\Model\Quote\Address $address
     * @return $this
     */
    public function collect(\Magento\Sales\Model\Quote\Address $address)
    {
        parent::collect($address);

        $items = $this->_getAddressItems($address);
        if (!count($items)) {
            return $this;
        }

        $canApplyMsrp = false;
        foreach ($items as $item) {
            if (!$item->getParentItemId()
                    && $this->msrpData->isShowBeforeOrderConfirm($item->getProductId())
                    && $this->msrpData->isMinimalPriceLessMsrp($item->getProductId())
            ) {
                $canApplyMsrp = true;
                break;
            }
        }

        $address->setCanApplyMsrp($canApplyMsrp);

        return $this;
    }
}
