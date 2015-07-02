<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\Quote;

use Magento\Quote\Model\Cart\CartTotalRepository;
use Magento\Quote\Api\Data\TotalsExtensionFactory;

class GrandTotalDetailsPlugin
{
    /**
     * @var \Magento\Tax\Api\Data\GrandTotalDetailsInterfaceFactory
     */
    protected $detailsFactory;

    /**
     * @var \Magento\Tax\Api\Data\GrandTotalRatesInterfaceFactory
     */
    protected $ratesFactory;

    /**
     * @var TotalsExtensionFactory
     */
    protected $extensionFactory;

    /**
     * @var \Magento\Quote\Model\QuoteRepository
     */
    protected $quoteRepository;

    /**
     * @var \Magento\Tax\Model\Config
     */
    protected $taxConfig;

    /**
     * @var \Magento\Quote\Model\Quote\Address\Total\Tax
     */
    protected $taxTotal;

    /**
     * @param \Magento\Tax\Api\Data\GrandTotalDetailsInterfaceFactory $detailsFactory
     * @param \Magento\Tax\Api\Data\GrandTotalRatesInterfaceFactory $ratesFactory
     * @param TotalsExtensionFactory $extensionFactory
     * @param \Magento\Tax\Model\Config $taxConfig
     * @param \Magento\Quote\Model\Quote\Address\Total\Tax $taxTotal
     * @param \Magento\Quote\Model\QuoteRepository $quoteRepository
     */
    public function __construct(
        \Magento\Tax\Api\Data\GrandTotalDetailsInterfaceFactory $detailsFactory,
        \Magento\Tax\Api\Data\GrandTotalRatesInterfaceFactory $ratesFactory,
        TotalsExtensionFactory $extensionFactory,
        \Magento\Tax\Model\Config $taxConfig,
        \Magento\Quote\Model\Quote\Address\Total\Tax $taxTotal,
        \Magento\Quote\Model\QuoteRepository $quoteRepository
    ) {
        $this->detailsFactory = $detailsFactory;
        $this->ratesFactory = $ratesFactory;
        $this->extensionFactory = $extensionFactory;
        $this->taxConfig = $taxConfig;
        $this->taxTotal = $taxTotal;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * @param array $rates
     * @return array
     */
    protected function getRatesData($rates)
    {
        $taxRates = [];
        foreach ($rates as $rate) {
            $taxRate = $this->ratesFactory->create([]);
            $taxRate->setPercent($rate['percent']);
            $taxRate->setTitle($rate['title']);
            $taxRates[] = $taxRate;
        }
        return $taxRates;
    }

    /**
     * @param CartTotalRepository $subject
     * @param \Closure $proceed
     * @param int $cartId
     * @return \Magento\Quote\Model\Cart\Totals
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGet(CartTotalRepository $subject, \Closure $proceed, $cartId)
    {
        $result = $proceed($cartId);
        $quote = $this->quoteRepository->getActive($cartId);
        $totals = $quote->getTotals();

        if (!array_key_exists('tax', $totals)) {
            return $result;
        }

        $taxes = $totals['tax']->getData();
        if (!array_key_exists('full_info', $taxes)) {
            return $result;
        }

        $detailsId = 1;
        $finalData = [];
        foreach ($taxes['full_info'] as $info) {
            if ((array_key_exists('hidden', $info) && $info['hidden'])
                || ($info['amount'] == 0 && $this->taxConfig->displayCartZeroTax())
            ) {
                continue;
            }

            $taxDetails = $this->detailsFactory->create([]);
            $taxDetails->setAmount($info['amount']);
            $taxRates = $this->getRatesData($info['rates']);
            $taxDetails->setRates($taxRates);
            $taxDetails->setGroupId($detailsId);
            $finalData[] = $taxDetails;
            $detailsId++;
        }
        $attributes = $result->getExtensionAttributes();
        if ($attributes === null) {
            $attributes = $this->extensionFactory->create();
        }
        $attributes->setTaxGrandtotalDetails($finalData);
        /** @var $result \Magento\Quote\Model\Cart\Totals */
        $result->setExtensionAttributes($attributes);
        return $result;
    }
}
