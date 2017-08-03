<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Adminhtml\Order;

/**
 * Adminhtml creditmemo bar
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Totalbar extends \Magento\Sales\Block\Adminhtml\Order\AbstractOrder
{
    /**
     * Totals
     *
     * @var array
     * @since 2.0.0
     */
    protected $_totals = [];

    /**
     * Retrieve required options from parent
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
     */
    protected function _beforeToHtml()
    {
        if (!$this->getParentBlock()) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Please correct the parent block for this block.')
            );
        }
        $this->setOrder($this->getParentBlock()->getOrder());
        $this->setSource($this->getParentBlock()->getSource());
        $this->setCurrency($this->getParentBlock()->getOrder()->getOrderCurrency());

        foreach ($this->getParentBlock()->getOrderTotalbarData() as $v) {
            $this->addTotal($v[0], $v[1], $v[2]);
        }

        parent::_beforeToHtml();
    }

    /**
     * Get totals
     *
     * @return array
     * @since 2.0.0
     */
    protected function getTotals()
    {
        return $this->_totals;
    }

    /**
     * Add total
     *
     * @param string $label
     * @param float $value
     * @param bool $grand
     * @return $this
     * @since 2.0.0
     */
    public function addTotal($label, $value, $grand = false)
    {
        $this->_totals[] = ['label' => $label, 'value' => $value, 'grand' => $grand];
        return $this;
    }
}
