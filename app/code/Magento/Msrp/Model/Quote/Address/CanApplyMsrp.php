<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
     * @param \Magento\Quote\Model\Quote\Address $address
     * @return bool
     */
    public function isCanApplyMsrp($address)
    {
        $canApplyMsrp = false;
        foreach ($address->getAllItems() as $item) {
            if (!$item->getParentItemId()
                    && $this->msrpHelper->isShowBeforeOrderConfirm($item->getProductId())
                    && $this->msrpHelper->isMinimalPriceLessMsrp($item->getProductId())
            ) {
                $canApplyMsrp = true;
                break;
            }
        }
        return $canApplyMsrp;
    }
}
