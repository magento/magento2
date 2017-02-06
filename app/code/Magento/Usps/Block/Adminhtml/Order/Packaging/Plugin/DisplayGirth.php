<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Usps\Block\Adminhtml\Order\Packaging\Plugin;

use Magento\Shipping\Block\Adminhtml\Order\Packaging;
use Magento\Usps\Helper\Data as DataHelper;

/**
 * Plugin class
 */
class DisplayGirth
{
    /**
     * Usps data helper
     *
     * @var \Magento\Usps\Helper\Data
     */
    protected $helper;

    /**
     * Construct
     *
     * @param \Magento\Usps\Helper\Data $helper
     */
    public function __construct(DataHelper $helper)
    {
        $this->helper = $helper;
    }

    /**
     * Is display girth value for specified shipping method
     *
     * @param \Magento\Shipping\Block\Adminhtml\Order\Packaging $subject
     * @param \Closure $proceed
     *
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundIsDisplayGirthValue(Packaging $subject, \Closure $proceed)
    {
        return $this->helper->displayGirthValue($subject->getShipment()->getOrder()->getShippingMethod());
    }
}
