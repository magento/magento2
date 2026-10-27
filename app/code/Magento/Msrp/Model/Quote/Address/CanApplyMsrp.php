<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Msrp\Model\Quote\Address;

class CanApplyMsrp
{
    /**
     * @var \Magento\Msrp\Helper\Data
     */
    protected $msrpHelper;

    /**
     * @param \Magento\Msrp\Helper\Data $msrpHelper
     */
    public function __construct(\Magento\Msrp\Helper\Data $msrpHelper)
    {
        $this->msrpHelper = $msrpHelper;
    }

    /**
     * Checks whether MSRP can be applied to the address
     *
     * @param \Magento\Quote\Model\Quote\Address $address
     * @return bool
     */
    public function isCanApplyMsrp($address)
    {
        $canApplyMsrp = false;
        foreach ($address->getAllItems() as $item) {
            if (!$item->getParentItemId()
                    && $this->msrpHelper->isShowBeforeOrderConfirm($item->getProduct())
                    && $this->msrpHelper->isMinimalPriceLessMsrp($item->getProduct())
            ) {
                $canApplyMsrp = true;
                break;
            }
        }
        return $canApplyMsrp;
    }
}
