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
namespace Magento\Weee\Block\Sales\Order;

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
        array $data = array()
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
        if ($weeeTotal) {
            // Add our total information to the set of other totals
            $total = new \Magento\Framework\Object(
                array(
                    'code' => $this->getNameInLayout(),
                    'label' => __('FPT'),
                    'value' => $weeeTotal
                )
            );
            if ($this->getBeforeCondition()) {
                $this->getParentBlock()->addTotalBefore($total, $this->getBeforeCondition());
            } else {
                $this->getParentBlock()->addTotal($total, $this->getAfterCondition());
            }
        }
        return $this;
    }
}
