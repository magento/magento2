<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Model\Quote\Address\Total;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Pricing\PriceCurrencyInterface as PriceRounder;
use Magento\Quote\Api\Data\ShippingAssignmentInterface as ShippingAssignment;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;

/**
 * Collect grand totals.
 */
class Grand extends AbstractTotal
{
    /**
     * @var PriceRounder
     */
    private $priceRounder;

    /**
     * @param PriceRounder|null $priceRounder
     */
    public function __construct(?PriceRounder $priceRounder)
    {
        $this->priceRounder = $priceRounder?: ObjectManager::getInstance()->get(PriceRounder::class);
    }

    /**
     * Collect grand total address amount
     *
     * @param Quote $quote
     * @param ShippingAssignment $shippingAssignment
     * @param Total $total
     * @return Grand
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function collect(Quote $quote, ShippingAssignment $shippingAssignment, Total $total): Grand
    {
        $totals = array_sum($total->getAllTotalAmounts());
        $baseTotals = array_sum($total->getAllBaseTotalAmounts());
        $grandTotal = $this->priceRounder->roundPrice($total->getGrandTotal() + $totals, 4);
        $baseGrandTotal = $this->priceRounder->roundPrice($total->getBaseGrandTotal() + $baseTotals, 4);

        $total->setGrandTotal($grandTotal);
        $total->setBaseGrandTotal($baseGrandTotal);
        return $this;
    }

    /**
     * Add grand total information to address
     *
     * @param Quote $quote
     * @param Total $total
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function fetch(Quote $quote, Total $total): array
    {
        return [
            'code' => $this->getCode(),
            'title' => __('Grand Total'),
            'value' => $total->getGrandTotal(),
            'area' => 'footer',
        ];
    }
}
