<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Usps\Block\Adminhtml\Order\Packaging\Plugin;

use Magento\Shipping\Block\Adminhtml\Order\Packaging;
use Magento\Usps\Helper\Data as DataHelper;

/**
 * Plugin class
 * @since 2.0.0
 */
class DisplayGirth
{
    /**
     * Usps data helper
     *
     * @var \Magento\Usps\Helper\Data
     * @since 2.0.0
     */
    protected $helper;

    /**
     * Construct
     *
     * @param \Magento\Usps\Helper\Data $helper
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function aroundIsDisplayGirthValue(Packaging $subject, \Closure $proceed)
    {
        return $this->helper->displayGirthValue($subject->getShipment()->getOrder()->getShippingMethod());
    }
}
