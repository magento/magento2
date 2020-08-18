<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Weee\Block\Sales\Order;

/**
 * Wee tax total column block
 *
 * @api
 * @since 100.0.2
 */
class Totals extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Weee\Helper\Data
     */
    protected $weeeData;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Weee\Helper\Data $weeeData
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Weee\Helper\Data $weeeData,
        array $data = []
    ) {
        $this->weeeData = $weeeData;
        parent::__construct($context, $data);
    }

    /**
     * Get totals source object
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getSource()
    {
        return $this->getParentBlock()->getSource();
    }

    /**
     * Create the weee ("FPT") totals summary
     *
     * @return $this
     */
    public function initTotals()
    {
        /** @var $items \Magento\Sales\Model\Order\Item[] */
        $items = $this->getSource()->getAllItems();
        $store = $this->getSource()->getStore();

        $weeeTotal = $this->weeeData->getTotalAmounts($items, $store);
        $weeeBaseTotal = $this->weeeData->getBaseTotalAmounts($items, $store);
        if ($weeeTotal) {
            $totals = $this->getParentBlock()->getTotals();

            // Add our total information to the set of other totals
            $total = new \Magento\Framework\DataObject(
                [
                    'code' => $this->getNameInLayout(),
                    'label' => __('FPT'),
                    'value' => $weeeTotal,
                    'base_value' => $weeeBaseTotal
                ]
            );
            if (isset($totals['grand_total_incl'])) {
                $this->getParentBlock()->addTotalBefore($total, 'grand_total');
            } else {
                $this->getParentBlock()->addTotalBefore($total, $this->getBeforeCondition());
            }
        }
        return $this;
    }
}
