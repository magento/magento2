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

/**
 * Recurring payment info/options product view block
 */
namespace Magento\RecurringPayment\Block\Catalog\Product\View;

use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class Payment extends \Magento\Framework\View\Element\Template
{
    /**
     * Recurring payment instance
     *
     * @var \Magento\RecurringPayment\Model\RecurringPayment
     */
    protected $_payment = false;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_registry = null;

    /**
     * Recurring payment factory
     *
     * @var \Magento\RecurringPayment\Model\RecurringPaymentFactory
     */
    protected $_paymentFactory;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\RecurringPayment\Model\RecurringPaymentFactory $paymentFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\RecurringPayment\Model\RecurringPaymentFactory $paymentFactory,
        array $data = array()
    ) {
        parent::__construct($context, $data);
        $this->_registry = $registry;
        $this->_paymentFactory = $paymentFactory;
    }

    /**
     * Getter for schedule info
     * array(
     *     <title> => array('blah-blah', 'bla-bla-blah', ...)
     *     <title2> => ...
     * )
     * @return array
     */
    public function getScheduleInfo()
    {
        $scheduleInfo = array();
        foreach ($this->_payment->exportScheduleInfo() as $info) {
            $scheduleInfo[$info->getTitle()] = $info->getSchedule();
        }
        return $scheduleInfo;
    }

    /**
     * Render date input element
     *
     * @return string
     */
    public function getDateHtml()
    {
        if ($this->_payment->getStartDateIsEditable()) {
            $this->setDateHtmlId('recurring_start_date');
            $calendar = $this->getLayout()->createBlock(
                'Magento\Framework\View\Element\Html\Date'
            )->setId(
                'recurring_start_date'
            )->setName(
                \Magento\RecurringPayment\Model\RecurringPayment::BUY_REQUEST_START_DATETIME
            )->setClass(
                'datetime-picker input-text'
            )->setImage(
                $this->getViewFileUrl('Magento_Core::calendar.gif')
            )->setDateFormat(
                $this->_localeDate->getDateFormat(TimezoneInterface::FORMAT_TYPE_SHORT)
            )->setTimeFormat(
                $this->_localeDate->getTimeFormat(TimezoneInterface::FORMAT_TYPE_SHORT)
            );
            return $calendar->getHtml();
        }
        return '';
    }

    /**
     * Determine current product and initialize its recurring payment model
     *
     * @return \Magento\RecurringPayment\Block\Catalog\Product\View\Payment
     */
    protected function _prepareLayout()
    {
        $product = $this->_registry->registry('current_product');
        if ($product) {
            $this->_payment = $this->_paymentFactory->create()->importProduct($product);
        }
        return parent::_prepareLayout();
    }

    /**
     * If there is no payment information, the template will be unset, blocking the output
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->_payment) {
            $this->_template = null;
        }
        return parent::_toHtml();
    }
}
