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
namespace Magento\RecurringPayment\Block\Payment\View;

/**
 * Recurring payment view schedule
 */
class Schedule extends \Magento\RecurringPayment\Block\Payment\View
{
    /**
     * @var \Magento\RecurringPayment\Block\Fields
     */
    protected $_fields;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\RecurringPayment\Block\Fields $fields
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\RecurringPayment\Block\Fields $fields,
        array $data = array()
    ) {
        parent::__construct($context, $registry, $data);
        $this->_fields = $fields;
    }

    /**
     * Prepare schedule info
     *
     * @return void
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $this->_shouldRenderInfo = true;
        foreach (array('start_datetime', 'suspension_threshold') as $key) {
            $this->_addInfo(
                array(
                    'label' => $this->_fields->getFieldLabel($key),
                    'value' => $this->_recurringPayment->renderData($key)
                )
            );
        }

        foreach ($this->_recurringPayment->exportScheduleInfo() as $info) {
            $this->_addInfo(array('label' => $info->getTitle(), 'value' => $info->getSchedule()));
        }
    }
}
