<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Usps\Block\Adminhtml\Order\Packaging\Plugin;

use Closure;
use Magento\Shipping\Block\Adminhtml\Order\Packaging;
use Magento\Usps\Helper\Data as DataHelper;

/**
 * Plugin class
 */
class DisplayGirth
{
    /**
     * Construct
     *
     * @param DataHelper $helper
     */
    public function __construct(
        protected readonly DataHelper $helper
    ) {
    }

    /**
     * Is display girth value for specified shipping method
     *
     * @param Packaging $subject
     * @param Closure $proceed
     *
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundIsDisplayGirthValue(Packaging $subject, Closure $proceed)
    {
        return $this->helper->displayGirthValue($subject->getShipment()->getOrder()->getShippingMethod());
    }
}
