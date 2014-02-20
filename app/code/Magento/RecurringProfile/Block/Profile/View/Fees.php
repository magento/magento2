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

namespace Magento\RecurringProfile\Block\Profile\View;

/**
 * Recurring profile view fees
 */
class Fees extends \Magento\RecurringProfile\Block\Profile\View
{
    /**
     * @var \Magento\Core\Helper\Data
     */
    protected $_coreHelper;

    /**
     * @var \Magento\RecurringProfile\Block\Fields
     */
    protected $_fields;

    /**
     * @param \Magento\View\Element\Template\Context $context
     * @param \Magento\Core\Model\Registry $registry
     * @param \Magento\Core\Helper\Data $coreHelper
     * @param \Magento\RecurringProfile\Block\Fields $fields
     * @param array $data
     */
    public function __construct(
        \Magento\View\Element\Template\Context $context,
        \Magento\Core\Model\Registry $registry,
        \Magento\Core\Helper\Data $coreHelper,
        \Magento\RecurringProfile\Block\Fields $fields,
        array $data = array()
    ) {
        $this->_coreHelper = $coreHelper;
        parent::__construct($context, $registry, $data);
        $this->_fields = $fields;
    }

    /**
     * Prepare fees info
     *
     * @return void
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $this->_shouldRenderInfo = true;
        $this->_addInfo(array(
            'label' => $this->_fields->getFieldLabel('currency_code'),
            'value' => $this->_recurringProfile->getCurrencyCode()
        ));
        $params = array('init_amount', 'trial_billing_amount', 'billing_amount', 'tax_amount', 'shipping_amount');
        foreach ($params as $key) {
            $value = $this->_recurringProfile->getData($key);
            if ($value) {
                $this->_addInfo(array(
                    'label' => $this->_fields->getFieldLabel($key),
                    'value' => $this->_coreHelper->formatCurrency($value, false),
                    'is_amount' => true,
                ));
            }
        }
    }
}
