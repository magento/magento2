<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Adminhtml\Order;

/**
 * Adminhtml order totals block
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 100.0.2
 */
class Totals extends \Magento\Sales\Block\Adminhtml\Totals//\Magento\Sales\Block\Adminhtml\Order\AbstractOrder
{
    /**
     * Initialize order totals array
     *
     * @return $this
     */
    protected function _initTotals()
    {
        parent::_initTotals();
        $this->_totals['paid'] = new \Magento\Framework\DataObject(
            [
                'code' => 'paid',
                'strong' => true,
                'value' => $this->getSource()->getTotalPaid(),
                'base_value' => $this->getSource()->getBaseTotalPaid(),
                'label' => __('Total Paid'),
                'area' => 'footer',
            ]
        );
        $this->_totals['refunded'] = new \Magento\Framework\DataObject(
            [
                'code' => 'refunded',
                'strong' => true,
                'value' => $this->getSource()->getTotalRefunded(),
                'base_value' => $this->getSource()->getBaseTotalRefunded(),
                'label' => __('Total Refunded'),
                'area' => 'footer',
            ]
        );
        $code = 'due';
        $label = 'Total Due';
        $value = $this->getSource()->getTotalDue();
        $baseValue = $this->getSource()->getBaseTotalDue();
        if ($this->getSource()->getTotalCanceled() > 0 && $this->getSource()->getBaseTotalCanceled() > 0) {
            $code = 'canceled';
            $label = 'Total Canceled';
            $value = $this->getSource()->getTotalCanceled();
            $baseValue = $this->getSource()->getBaseTotalCanceled();
        }
        $this->_totals[$code] = new \Magento\Framework\DataObject(
            [
                'code' => 'due',
                'strong' => true,
                'value' => $value,
                'base_value' => $baseValue,
                'label' => __($label),
                'area' => 'footer',
            ]
        );
        return $this;
    }
}
