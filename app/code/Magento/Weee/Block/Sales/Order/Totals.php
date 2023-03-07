<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Weee\Block\Sales\Order;

use Magento\Framework\DataObject;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Item as OrderItem;
use Magento\Weee\Helper\Data as WeeeHelper;

/**
 * Wee tax total column block
 *
 * @api
 * @since 100.0.2
 */
class Totals extends Template
{
    /**
     * @param Context $context
     * @param WeeeHelper $weeeData
     * @param array $data
     */
    public function __construct(
        Context $context,
        protected WeeeHelper $weeeData,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Get totals source object
     *
     * @return Order
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
        /** @var $items OrderItem[] */
        $items = $this->getSource()->getAllItems();
        $store = $this->getSource()->getStore();

        $weeeTotal = $this->weeeData->getTotalAmounts($items, $store);
        $weeeBaseTotal = $this->weeeData->getBaseTotalAmounts($items, $store);
        if ($weeeTotal) {
            $totals = $this->getParentBlock()->getTotals();

            // Add our total information to the set of other totals
            $total = new DataObject(
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
