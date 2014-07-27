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
namespace Magento\Weee\Model\Sales\Pdf;

class Weee extends \Magento\Sales\Model\Order\Pdf\Total\DefaultTotal
{
    /**
     * @var \Magento\Weee\Helper\Data
     */
    protected $_weeeData;

    /**
     * @param \Magento\Tax\Helper\Data $taxHelper
     * @param \Magento\Tax\Model\Calculation $taxCalculation
     * @param \Magento\Tax\Model\Resource\Sales\Order\Tax\CollectionFactory $ordersFactory
     * @param \Magento\Weee\Helper\Data $_weeeData
     * @param array $data
     */
    public function __construct(
        \Magento\Tax\Helper\Data $taxHelper,
        \Magento\Tax\Model\Calculation $taxCalculation,
        \Magento\Tax\Model\Resource\Sales\Order\Tax\CollectionFactory $ordersFactory,
        \Magento\Weee\Helper\Data $_weeeData,
        array $data = array()
    ) {
        $this->_weeeData = $_weeeData;
        parent::__construct($taxHelper, $taxCalculation, $ordersFactory, $data);
    }

    /**
     * Check if weee total amount should be included
     * array(
     *  $index => array(
     *      'amount'   => $amount,
     *      'label'    => $label,
     *      'font_size'=> $font_size
     *  )
     * )
     * @return array
     */
    public function getTotalsForDisplay()
    {
        /** @var $items \Magento\Sales\Model\Order\Item[] */
        $items = $this->getSource()->getAllItems();
        $store = $this->getSource()->getStore();

        $weeeTotal = $this->_weeeData->getTotalAmounts($items, $store);

        // If we have no Weee, check if we still need to display it
        if (!$weeeTotal && !filter_var($this->getDisplayZero(), FILTER_VALIDATE_BOOLEAN)) {
            return array();
        }

        // Display the Weee total amount
        $fontSize = $this->getFontSize() ? $this->getFontSize() : 7;
        $totals = array(
            array(
                'amount' => $this->getOrder()->formatPriceTxt($weeeTotal),
                'label' => __($this->getTitle()) . ':',
                'font_size' => $fontSize
            )
        );

        return $totals;
    }
}
