<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Quote\Address\Total;

use Magento\Framework\Pricing\PriceCurrencyInterface;

class Tax extends \Magento\Sales\Model\Quote\Address\Total\AbstractTotal
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
    }

    /**
     * @param \Magento\Sales\Model\Quote\Address $address
     * @return $this
     */
    public function collect(\Magento\Sales\Model\Quote\Address $address)
    {
        $store = $address->getQuote()->getStore();

        $address->setTaxAmount(0);
        $address->setBaseTaxAmount(0);
        $address->setAppliedTaxes([]);

        $items = $address->getAllItems();
        if (!count($items)) {
            return $this;
        }
        $custTaxClassId = $address->getQuote()->getCustomerTaxClassId();

        $taxCalculationModel = $this->_calculation;
        /* @var $taxCalculationModel \Magento\Tax\Model\Calculation */
        $request = $taxCalculationModel->getRateRequest(
            $address,
            $address->getQuote()->getBillingAddress(),
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
                        $address->setDiscountAmount(
                            $address->getDiscountAmount() + ($item->getDiscountAmount() - $discountBefore)
                        );
                        $address->setBaseDiscountAmount(
                            $address->getBaseDiscountAmount() + ($item->getBaseDiscountAmount() - $baseDiscountBefore)
                        );

                        $address->setGrandTotal(
                            $address->getGrandTotal() - ($item->getDiscountAmount() - $discountBefore)
                        );
                        $address->setBaseGrandTotal(
                            $address->getBaseGrandTotal() - ($item->getBaseDiscountAmount() - $baseDiscountBefore)
                        );
                    }

                    $this->_saveAppliedTaxes(
                        $address,
                        $taxCalculationModel->getAppliedRates($request),
                        $child->getTaxAmount(),
                        $child->getBaseTaxAmount(),
                        $rate
                    );
                }
                $itemTaxAmount = $item->getTaxAmount() + $item->getDiscountTaxCompensation();
                $address->setTaxAmount($address->getTaxAmount() + $itemTaxAmount);
                $itemBaseTaxAmount = $item->getBaseTaxAmount() + $item->getBaseDiscountTaxCompensation();
                $address->setBaseTaxAmount($address->getBaseTaxAmount() + $itemBaseTaxAmount);
            } else {
                $discountBefore = $item->getDiscountAmount();
                $baseDiscountBefore = $item->getBaseDiscountAmount();

                $rate = $taxCalculationModel->getRate(
                    $request->setProductClassId($item->getProduct()->getTaxClassId())
                );

                $item->setTaxPercent($rate);
                $item->calcTaxAmount();

                if ($discountBefore != $item->getDiscountAmount()) {
                    $address->setDiscountAmount(
                        $address->getDiscountAmount() + ($item->getDiscountAmount() - $discountBefore)
                    );
                    $address->setBaseDiscountAmount(
                        $address->getBaseDiscountAmount() + ($item->getBaseDiscountAmount() - $baseDiscountBefore)
                    );

                    $address->setGrandTotal(
                        $address->getGrandTotal() - ($item->getDiscountAmount() - $discountBefore)
                    );
                    $address->setBaseGrandTotal(
                        $address->getBaseGrandTotal() - ($item->getBaseDiscountAmount() - $baseDiscountBefore)
                    );
                }

                $itemTaxAmount = $item->getTaxAmount() + $item->getDiscountTaxCompensation();
                $address->setTaxAmount($address->getTaxAmount() + $itemTaxAmount);
                $itemBaseTaxAmount = $item->getBaseTaxAmount() + $item->getBaseDiscountTaxCompensation();
                $address->setBaseTaxAmount($address->getBaseTaxAmount() + $itemBaseTaxAmount);

                $applied = $taxCalculationModel->getAppliedRates($request);
                $this->_saveAppliedTaxes($address, $applied, $item->getTaxAmount(), $item->getBaseTaxAmount(), $rate);
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
                    $shippingTax = $address->getShippingAmount() * $rate / 100;
                    $shippingBaseTax = $address->getBaseShippingAmount() * $rate / 100;
                } else {
                    $shippingTax = $address->getShippingTaxAmount();
                    $shippingBaseTax = $address->getBaseShippingTaxAmount();
                }

                $shippingTax = $this->priceCurrency->round($shippingTax);
                $shippingBaseTax = $this->priceCurrency->round($shippingBaseTax);

                $address->setTaxAmount($address->getTaxAmount() + $shippingTax);
                $address->setBaseTaxAmount($address->getBaseTaxAmount() + $shippingBaseTax);

                $this->_saveAppliedTaxes(
                    $address,
                    $taxCalculationModel->getAppliedRates($request),
                    $shippingTax,
                    $shippingBaseTax,
                    $rate
                );
            }
        }

        if (!$this->_taxData->shippingPriceIncludesTax()) {
            $address->setShippingTaxAmount($shippingTax);
            $address->setBaseShippingTaxAmount($shippingBaseTax);
        }

        $address->setGrandTotal($address->getGrandTotal() + $address->getTaxAmount());
        $address->setBaseGrandTotal($address->getBaseGrandTotal() + $address->getBaseTaxAmount());
        return $this;
    }

    /**
     * @param \Magento\Sales\Model\Quote\Address $address
     * @param array $applied
     * @param int $amount
     * @param int $baseAmount
     * @param int $rate
     * @return void
     */
    protected function _saveAppliedTaxes(
        \Magento\Sales\Model\Quote\Address $address,
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

            if (!is_null($row['percent'])) {
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
     * @param \Magento\Sales\Model\Quote\Address $address
     * @return $this
     */
    public function fetch(\Magento\Sales\Model\Quote\Address $address)
    {
        $applied = $address->getAppliedTaxes();
        $store = $address->getQuote()->getStore();
        $amount = $address->getTaxAmount();

        if ($amount != 0 || $this->_taxData->displayZeroTax($store)) {
            $address->addTotal(
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
