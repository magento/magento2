<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\Quote;

use Magento\Quote\Api\Data\TotalSegmentExtensionFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\App\ObjectManager;
use Magento\Quote\Api\Data\TotalSegmentInterface;
use Magento\Quote\Model\Cart\TotalsConverter;
use Magento\Quote\Model\Quote\Address\Total as QuoteAddressTotal;
use Magento\Tax\Api\Data\GrandTotalDetailsInterfaceFactory;
use Magento\Tax\Api\Data\GrandTotalRatesInterfaceFactory;
use Magento\Tax\Model\Config as TaxConfig;

class GrandTotalDetailsPlugin
{
    /**
     * @var string
     */
    private $code;

    /**
     * Constructor
     *
     * @param GrandTotalDetailsInterfaceFactory $detailsFactory
     * @param GrandTotalRatesInterfaceFactory $ratesFactory
     * @param TotalSegmentExtensionFactory $totalSegmentExtensionFactory
     * @param TaxConfig $taxConfig
     * @param Json $serializer
     */
    public function __construct(
        private readonly GrandTotalDetailsInterfaceFactory $detailsFactory,
        private readonly GrandTotalRatesInterfaceFactory $ratesFactory,
        private readonly TotalSegmentExtensionFactory $totalSegmentExtensionFactory,
        private readonly TaxConfig $taxConfig,
        private readonly Json $serializer
    ) {
        $this->code = 'tax';
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
     * @param TotalsConverter $subject
     * @param TotalSegmentInterface[] $totalSegments
     * @param QuoteAddressTotal[] $addressTotals
     * @return TotalSegmentInterface[]
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function afterProcess(
        TotalsConverter $subject,
        array $totalSegments,
        array $addressTotals = []
    ) {

        if (!array_key_exists($this->code, $addressTotals)) {
            return $totalSegments;
        }

        $taxes = $addressTotals['tax']->getData();
        if (!array_key_exists('full_info', $taxes)) {
            return $totalSegments;
        }

        $detailsId = 1;
        $finalData = [];
        $fullInfo = $taxes['full_info'];
        if (is_string($fullInfo)) {
            $fullInfo = $this->serializer->unserialize($fullInfo);
        }
        foreach ($fullInfo as $info) {
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
        $attributes = $totalSegments[$this->code]->getExtensionAttributes();
        if ($attributes === null) {
            $attributes = $this->totalSegmentExtensionFactory->create();
        }
        $attributes->setTaxGrandtotalDetails($finalData);
        $totalSegments[$this->code]->setExtensionAttributes($attributes);
        return $totalSegments;
    }
}
