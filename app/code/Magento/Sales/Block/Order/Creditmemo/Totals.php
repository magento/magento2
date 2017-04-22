<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Sales\Block\Order\Creditmemo;

use Magento\Sales\Model\Order\Creditmemo;

/**
 * @api
 */
class Totals extends \Magento\Sales\Block\Order\Totals
{
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        parent::__construct($context, $registry, $data);
        $this->_isScopePrivate = true;
    }

    /**
     * @var Creditmemo|null
     */
    protected $_creditmemo = null;

    /**
     * @return Creditmemo|null
     */
    public function getCreditmemo()
    {
        if ($this->_creditmemo === null) {
            if ($this->hasData('creditmemo')) {
                $this->_creditmemo = $this->_getData('creditmemo');
            } elseif ($this->_coreRegistry->registry('current_creditmemo')) {
                $this->_creditmemo = $this->_coreRegistry->registry('current_creditmemo');
            } elseif ($this->getParentBlock()->getCreditmemo()) {
                $this->_creditmemo = $this->getParentBlock()->getCreditmemo();
            }
        }
        return $this->_creditmemo;
    }

    /**
     * @param Creditmemo $creditmemo
     * @return $this
     */
    public function setCreditmemo($creditmemo)
    {
        $this->_creditmemo = $creditmemo;
        return $this;
    }

    /**
     * Get totals source object
     *
     * @return Creditmemo
     */
    public function getSource()
    {
        return $this->getCreditmemo();
    }

    /**
     * Initialize order totals array
     *
     * @return $this
     */
    protected function _initTotals()
    {
        parent::_initTotals();
        $this->removeTotal('base_grandtotal');
        if ((double)$this->getSource()->getAdjustmentPositive()) {
            $total = new \Magento\Framework\DataObject(
                [
                    'code' => 'adjustment_positive',
                    'value' => $this->getSource()->getAdjustmentPositive(),
                    'label' => __('Adjustment Refund'),
                ]
            );
            $this->addTotal($total);
        }
        if ((double)$this->getSource()->getAdjustmentNegative()) {
            $total = new \Magento\Framework\DataObject(
                [
                    'code' => 'adjustment_negative',
                    'value' => $this->getSource()->getAdjustmentNegative(),
                    'label' => __('Adjustment Fee'),
                ]
            );
            $this->addTotal($total);
        }
        /**
                <?php if ($this->getCanDisplayTotalPaid()): ?>
                <tr>
           <td colspan="6" class="a-right"><strong><?php echo __('Total Paid') ?></strong></td>
           <td class="last a-right"><strong><?php echo $_order->formatPrice($_creditmemo->getTotalPaid()) ?></strong></td>
                </tr>
                <?php endif; ?>
                <?php if ($this->getCanDisplayTotalRefunded()): ?>
                <tr>
           <td colspan="6" class="a-right"><strong><?php echo __('Total Refunded') ?></strong></td>
           <td class="last a-right"><strong><?php echo $_order->formatPrice($_creditmemo->getTotalRefunded()) ?></strong></td>
                </tr>
                <?php endif; ?>
                <?php if ($this->getCanDisplayTotalDue()): ?>
                <tr>
           <td colspan="6" class="a-right"><strong><?php echo __('Total Due') ?></strong></td>
           <td class="last a-right"><strong><?php echo $_order->formatPrice($_creditmemo->getTotalDue()) ?></strong></td>
                </tr>
                <?php endif; ?>
        */
        return $this;
    }
}
