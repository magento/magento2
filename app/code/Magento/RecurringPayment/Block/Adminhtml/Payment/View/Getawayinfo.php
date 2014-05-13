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
 * Recurring payment getaway info block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\RecurringPayment\Block\Adminhtml\Payment\View;

class Getawayinfo extends \Magento\Backend\Block\Widget
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\RecurringPayment\Block\Fields
     */
    protected $_fields;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\RecurringPayment\Block\Fields $fields
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\RecurringPayment\Block\Fields $fields,
        array $data = array()
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
        $this->_fields = $fields;
    }

    /**
     * Return recurring payment getaway information for view
     *
     * @return array
     */
    public function getRecurringPaymentGetawayInformation()
    {
        $recurringPayment = $this->_coreRegistry->registry('current_recurring_payment');
        $information = array();
        foreach ($recurringPayment->getData() as $key => $value) {
            $information[$this->_fields->getFieldLabel($key)] = $value;
        }
        return $information;
    }
}
