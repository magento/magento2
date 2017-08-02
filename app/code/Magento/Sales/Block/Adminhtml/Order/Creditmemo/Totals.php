<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Adminhtml\Order\Creditmemo;

use Magento\Sales\Model\Order\Creditmemo;

/**
 * Adminhtml order creditmemo totals block
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Totals extends \Magento\Sales\Block\Adminhtml\Totals
{
    /**
     * Creditmemo
     *
     * @var Creditmemo|null
     * @since 2.0.0
     */
    protected $_creditmemo;

    /**
     * Retrieve creditmemo model instance
     *
     * @return Creditmemo
     * @since 2.0.0
     */
    public function getCreditmemo()
    {
        if ($this->_creditmemo === null) {
            if ($this->hasData('creditmemo')) {
                $this->_creditmemo = $this->_getData('creditmemo');
            } elseif ($this->_coreRegistry->registry('current_creditmemo')) {
                $this->_creditmemo = $this->_coreRegistry->registry('current_creditmemo');
            } elseif ($this->getParentBlock() && $this->getParentBlock()->getCreditmemo()) {
                $this->_creditmemo = $this->getParentBlock()->getCreditmemo();
            }
        }
        return $this->_creditmemo;
    }

    /**
     * Get source
     *
     * @return Creditmemo|null
     * @since 2.0.0
     */
    public function getSource()
    {
        return $this->getCreditmemo();
    }

    /**
     * Initialize creditmemo totals array
     *
     * @return $this
     * @since 2.0.0
     */
    protected function _initTotals()
    {
        parent::_initTotals();
        $this->addTotal(
            new \Magento\Framework\DataObject(
                [
                    'code' => 'adjustment_positive',
                    'value' => $this->getSource()->getAdjustmentPositive(),
                    'base_value' => $this->getSource()->getBaseAdjustmentPositive(),
                    'label' => __('Adjustment Refund'),
                ]
            )
        );
        $this->addTotal(
            new \Magento\Framework\DataObject(
                [
                    'code' => 'adjustment_negative',
                    'value' => $this->getSource()->getAdjustmentNegative(),
                    'base_value' => $this->getSource()->getBaseAdjustmentNegative(),
                    'label' => __('Adjustment Fee'),
                ]
            )
        );
        return $this;
    }
}
