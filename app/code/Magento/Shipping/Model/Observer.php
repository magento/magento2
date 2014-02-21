<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Shipping\Model;

class Observer
{
    /**
     * @var \Magento\Core\Model\LocaleInterface
     */
    protected $_coreLocale;

    /**
     * @var \Magento\Sales\Model\Resource\Report\ShippingFactory
     */
    protected $_shippingFactory;

    /**
     * @param \Magento\Core\Model\LocaleInterface $coreLocale
     * @param \Magento\Sales\Model\Resource\Report\ShippingFactory $shippingFactory
     */
    public function __construct(
        \Magento\Core\Model\LocaleInterface $coreLocale,
        \Magento\Sales\Model\Resource\Report\ShippingFactory $shippingFactory
    ) {
        $this->_coreLocale = $coreLocale;
        $this->_shippingFactory = $shippingFactory;
    }

    /**
     * Refresh sales shipment report statistics for last day
     *
     * @return $this
     */
    public function aggregateSalesReportShipmentData()
    {
        $this->_coreLocale->emulate(0);
        $currentDate = $this->_coreLocale->date();
        $date = $currentDate->subHour(25);
        $this->_shippingFactory->create()->aggregate($date);
        $this->_coreLocale->revert();
        return $this;
    }
}
