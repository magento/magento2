<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Msrp\Model\Quote\Address;

/**
 * Class \Magento\Msrp\Model\Quote\Address\CanApplyMsrp
 *
 * @since 2.0.0
 */
class CanApplyMsrp
{
    /**
     * @var \Magento\Msrp\Helper\Data
     * @since 2.0.0
     */
    protected $msrpHelper;

    /**
     * @param \Magento\Msrp\Helper\Data $msrpHelper
     * @since 2.0.0
     */
    public function __construct(\Magento\Msrp\Helper\Data $msrpHelper)
    {
        $this->msrpHelper = $msrpHelper;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address $address
     * @return bool
     * @since 2.0.0
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
