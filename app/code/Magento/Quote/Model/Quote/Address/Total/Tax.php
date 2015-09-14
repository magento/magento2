<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Quote\Address\Total;

use Magento\Framework\Pricing\PriceCurrencyInterface;

class Tax extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
    /**
     * @var array
     */
    protected $_appliedTaxes = [];

    /**
     * Tax data
     *
     * @var \Magento\Tax\Helper\Data
     */
    protected $_taxData;

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Tax\Model\Calculation
     */
    protected $_calculation;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @param \Magento\Tax\Helper\Data $taxData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Tax\Model\Calculation $calculation
     * @param PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        \Magento\Tax\Helper\Data $taxData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Tax\Model\Calculation $calculation,
        PriceCurrencyInterface $priceCurrency
    ) {
        $this->_taxData = $taxData;
        $this->_scopeConfig = $scopeConfig;
        $this->_calculation = $calculation;
        $this->priceCurrency = $priceCurrency;
        $this->setCode('tax');
        die('Broken TAX collector called.');
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface|\Magento\Quote\Model\Quote\Address $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        $store = $quote->getStore();

        $shippingAssignment->setTaxAmount(0);
        $shippingAssignment->setBaseTaxAmount(0);
        $shippingAssignment->setAppliedTaxes([]);

        $items = $shippingAssignment->getAllItems();
        if (!count($items)) {
            return $this;
        }
        $custTaxClassId = $quote->getCustomerTaxClassId();

        $taxCalculationModel = $this->_calculation;
        /* @var $taxCalculationModel \Magento\Tax\Model\Calculation */
        $request = $taxCalculationModel->getRateRequest(
            $shippingAssignment,
            $quote->getBillingAddress(),
            $custTaxClassId,
            $store
        );

        foreach ($items as $item) {
            /**
             * Child item's tax we calculate for parent
             */
            if ($item->getParentItemId()) {
                continue;
            }
            /**
             * We calculate parent tax amount as sum of children's tax amounts
             */

            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                foreach ($item->getChildren() as $child) {
                    $discountBefore = $item->getDiscountAmount();
                    $baseDiscountBefore = $item->getBaseDiscountAmount();

                    $rate = $taxCalculationModel->getRate(
                        $request->setProductClassId($child->getProduct()->getTaxClassId())
                    );

                    $child->setTaxPercent($rate);
                    $child->calcTaxAmount();

                    if ($discountBefore != $item->getDiscountAmount()) {
                        $shippingAssignment->setDiscountAmount(
                            $shippingAssignment->getDiscountAmount() + ($item->getDiscountAmount() - $discountBefore)
                        );
                        $shippingAssignment->setBaseDiscountAmount(
                            $shippingAssignment->getBaseDiscountAmount()
                            + ($item->getBaseDiscountAmount() - $baseDiscountBefore)
                        );

                        $shippingAssignment->setGrandTotal(
                            $shippingAssignment->getGrandTotal() - ($item->getDiscountAmount() - $discountBefore)
                        );
                        $shippingAssignment->setBaseGrandTotal(
                            $shippingAssignment->getBaseGrandTotal()
                            - ($item->getBaseDiscountAmount() - $baseDiscountBefore)
                        );
                    }

                    $this->_saveAppliedTaxes(
                        $shippingAssignment,
                        $taxCalculationModel->getAppliedRates($request),
                        $child->getTaxAmount(),
                        $child->getBaseTaxAmount(),
                        $rate
                    );
                }
                $itemTaxAmount = $item->getTaxAmount() + $item->getDiscountTaxCompensation();
                $shippingAssignment->setTaxAmount($shippingAssignment->getTaxAmount() + $itemTaxAmount);
                $itemBaseTaxAmount = $item->getBaseTaxAmount() + $item->getBaseDiscountTaxCompensation();
                $shippingAssignment->setBaseTaxAmount($shippingAssignment->getBaseTaxAmount() + $itemBaseTaxAmount);
            } else {
                $discountBefore = $item->getDiscountAmount();
                $baseDiscountBefore = $item->getBaseDiscountAmount();

                $rate = $taxCalculationModel->getRate(
                    $request->setProductClassId($item->getProduct()->getTaxClassId())
                );

                $item->setTaxPercent($rate);
                $item->calcTaxAmount();

                if ($discountBefore != $item->getDiscountAmount()) {
                    $shippingAssignment->setDiscountAmount(
                        $shippingAssignment->getDiscountAmount() + ($item->getDiscountAmount() - $discountBefore)
                    );
                    $shippingAssignment->setBaseDiscountAmount(
                        $shippingAssignment->getBaseDiscountAmount()
                        + ($item->getBaseDiscountAmount() - $baseDiscountBefore)
                    );

                    $shippingAssignment->setGrandTotal(
                        $shippingAssignment->getGrandTotal() - ($item->getDiscountAmount() - $discountBefore)
                    );
                    $shippingAssignment->setBaseGrandTotal(
                        $shippingAssignment->getBaseGrandTotal() - ($item->getBaseDiscountAmount() - $baseDiscountBefore)
                    );
                }

                $itemTaxAmount = $item->getTaxAmount() + $item->getDiscountTaxCompensation();
                $shippingAssignment->setTaxAmount($shippingAssignment->getTaxAmount() + $itemTaxAmount);
                $itemBaseTaxAmount = $item->getBaseTaxAmount() + $item->getBaseDiscountTaxCompensation();
                $shippingAssignment->setBaseTaxAmount($shippingAssignment->getBaseTaxAmount() + $itemBaseTaxAmount);

                $applied = $taxCalculationModel->getAppliedRates($request);
                $this->_saveAppliedTaxes(
                    $shippingAssignment,
                    $applied,
                    $item->getTaxAmount(),
                    $item->getBaseTaxAmount(),
                    $rate
                );
            }
        }

        $shippingTaxClass = $this->_scopeConfig->getValue(
            \Magento\Tax\Model\Config::CONFIG_XML_PATH_SHIPPING_TAX_CLASS,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );

        $shippingTax = 0;
        $shippingBaseTax = 0;

        if ($shippingTaxClass) {
            $rate = $taxCalculationModel->getRate($request->setProductClassId($shippingTaxClass));
            if ($rate) {
                if (!$this->_taxData->shippingPriceIncludesTax()) {
                    $shippingTax = $shippingAssignment->getShippingAmount() * $rate / 100;
                    $shippingBaseTax = $shippingAssignment->getBaseShippingAmount() * $rate / 100;
                } else {
                    $shippingTax = $shippingAssignment->getShippingTaxAmount();
                    $shippingBaseTax = $shippingAssignment->getBaseShippingTaxAmount();
                }

                $shippingTax = $this->priceCurrency->round($shippingTax);
                $shippingBaseTax = $this->priceCurrency->round($shippingBaseTax);

                $shippingAssignment->setTaxAmount($shippingAssignment->getTaxAmount() + $shippingTax);
                $shippingAssignment->setBaseTaxAmount($shippingAssignment->getBaseTaxAmount() + $shippingBaseTax);

                $this->_saveAppliedTaxes(
                    $shippingAssignment,
                    $taxCalculationModel->getAppliedRates($request),
                    $shippingTax,
                    $shippingBaseTax,
                    $rate
                );
            }
        }

        if (!$this->_taxData->shippingPriceIncludesTax()) {
            $shippingAssignment->setShippingTaxAmount($shippingTax);
            $shippingAssignment->setBaseShippingTaxAmount($shippingBaseTax);
        }

        $shippingAssignment->setGrandTotal($shippingAssignment->getGrandTotal() + $shippingAssignment->getTaxAmount());
        $shippingAssignment->setBaseGrandTotal(
            $shippingAssignment->getBaseGrandTotal() + $shippingAssignment->getBaseTaxAmount()
        );
        return $this;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address $address
     * @param array $applied
     * @param int $amount
     * @param int $baseAmount
     * @param int $rate
     * @return void
     */
    protected function _saveAppliedTaxes(
        \Magento\Quote\Model\Quote\Address $address,
        $applied,
        $amount,
        $baseAmount,
        $rate
    ) {
        $previouslyAppliedTaxes = $address->getAppliedTaxes();
        $process = count($previouslyAppliedTaxes);

        foreach ($applied as $row) {
            if (!isset($previouslyAppliedTaxes[$row['id']])) {
                $row['process'] = $process;
                $row['amount'] = 0;
                $row['base_amount'] = 0;
                $previouslyAppliedTaxes[$row['id']] = $row;
            }

            if ($row['percent'] !== null) {
                $row['percent'] = $row['percent'] ? $row['percent'] : 1;
                $rate = $rate ? $rate : 1;

                $appliedAmount = $amount / $rate * $row['percent'];
                $baseAppliedAmount = $baseAmount / $rate * $row['percent'];
            } else {
                $appliedAmount = 0;
                $baseAppliedAmount = 0;
                foreach ($row['rates'] as $rate) {
                    $appliedAmount += $rate['amount'];
                    $baseAppliedAmount += $rate['base_amount'];
                }
            }

            if ($appliedAmount || $previouslyAppliedTaxes[$row['id']]['amount']) {
                $previouslyAppliedTaxes[$row['id']]['amount'] += $appliedAmount;
                $previouslyAppliedTaxes[$row['id']]['base_amount'] += $baseAppliedAmount;
            } else {
                unset($previouslyAppliedTaxes[$row['id']]);
            }
        }
        $address->setAppliedTaxes($previouslyAppliedTaxes);
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address|\Magento\Quote\Model\Quote\Address\Total $total
     * @return $this
     */
    public function fetch(\Magento\Quote\Model\Quote\Address\Total $total)
    {
        $applied = $total->getAppliedTaxes();
        $store = $total->getQuote()->getStore();
        $amount = $total->getTaxAmount();

        if ($amount != 0 || $this->_taxData->displayZeroTax($store)) {
            $total->addTotal(
                [
                    'code' => $this->getCode(),
                    'title' => __('Tax'),
                    'full_info' => $applied ? $applied : [],
                    'value' => $amount,
                ]
            );
        }
        return $this;
    }
}
