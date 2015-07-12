<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

/**
 * Tax Event Observer
 */
namespace Magento\Tax\Model;

class Observer
{
    /**
     * Tax data
     *
     * @var \Magento\Tax\Helper\Data
     */
    protected $_taxData;

    /**
     * @var \Magento\Tax\Model\Calculation
     */
    protected $_calculation;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

    /**
     * @var \Magento\Tax\Model\Resource\Report\TaxFactory
     */
    protected $_reportTaxFactory;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_localeResolver;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * @param \Magento\Tax\Helper\Data $taxData
     * @param \Magento\Tax\Model\Calculation $calculation
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Tax\Model\Resource\Report\TaxFactory $reportTaxFactory
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\Tax\Helper\Data $taxData,
        \Magento\Tax\Model\Calculation $calculation,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Tax\Model\Resource\Report\TaxFactory $reportTaxFactory,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\Registry $registry
    ) {
        $this->_taxData = $taxData;
        $this->_calculation = $calculation;
        $this->_localeDate = $localeDate;
        $this->_reportTaxFactory = $reportTaxFactory;
        $this->_localeResolver = $localeResolver;
        $this->_registry = $registry;
    }

    /**
     * Put quote address tax information into order
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function salesEventConvertQuoteAddressToOrder(\Magento\Framework\Event\Observer $observer)
    {
        $address = $observer->getEvent()->getAddress();
        $order = $observer->getEvent()->getOrder();

        $taxes = $address->getAppliedTaxes();
        if (is_array($taxes)) {
            if (is_array($order->getAppliedTaxes())) {
                $taxes = array_merge($order->getAppliedTaxes(), $taxes);
            }
            $order->setAppliedTaxes($taxes);
            $order->setConvertingFromQuote(true);
        }

        $itemAppliedTaxes = $address->getItemsAppliedTaxes();
        if (is_array($itemAppliedTaxes)) {
            if (is_array($order->getItemAppliedTaxes())) {
                $itemAppliedTaxes = array_merge($order->getItemAppliedTaxes(), $itemAppliedTaxes);
            }
            $order->setItemAppliedTaxes($itemAppliedTaxes);
        }
    }

    /**
     * Refresh sales tax report statistics for last day
     *
     * @param \Magento\Cron\Model\Schedule $schedule
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aggregateSalesReportTaxData($schedule)
    {
        $this->_localeResolver->emulate(0);
        $currentDate = $this->_localeDate->date();
        $date = $currentDate->modify('-25 hours');
        /** @var $reportTax \Magento\Tax\Model\Resource\Report\Tax */
        $reportTax = $this->_reportTaxFactory->create();
        $reportTax->aggregate($date);
        $this->_localeResolver->revert();
        return $this;
    }

    /**
     * Reset extra tax amounts on quote addresses before recollecting totals
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function quoteCollectTotalsBefore(\Magento\Framework\Event\Observer $observer)
    {
        /* @var $quote \Magento\Quote\Model\Quote */
        $quote = $observer->getEvent()->getQuote();
        foreach ($quote->getAllAddresses() as $address) {
            $address->setExtraTaxAmount(0);
            $address->setBaseExtraTaxAmount(0);
        }
        return $this;
    }

    /**
     * Change default JavaScript templates for options rendering
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function updateProductOptions(\Magento\Framework\Event\Observer $observer)
    {
        $response = $observer->getEvent()->getResponseObject();
        $options = $response->getAdditionalOptions();

        $_product = $this->_registry->registry('current_product');
        if (!$_product) {
            return $this;
        }

        $algorithm = $this->_taxData->getCalculationAlgorithm();
        $options['calculationAlgorithm'] = $algorithm;
        // prepare correct template for options render
        if ($this->_taxData->displayBothPrices()) {
            $options['optionTemplate'] = sprintf(
                '<%%= data.label %%>'
                . '<%% if (data.finalPrice.value) { %%>'
                . ' +<%%= data.finalPrice.formatted %%> (%1$s <%%= data.basePrice.formatted %%>)'
                . '<%% } %%>',
                __('Excl. tax:')
            );
        } elseif ($this->_taxData->priceIncludesTax() && $this->_taxData->displayPriceExcludingTax()) {
            $options['optionTemplate'] = sprintf(
                '<%%= data.label %%>'
                . '<%% if (data.basePrice.value) { %%>'
                . ' +<%%= data.basePrice.formatted %%>'
                . '<%% } %%>'
            );
        }

        $response->setAdditionalOptions($options);
        return $this;
    }
}
