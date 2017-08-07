<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\Quote;

use Magento\Quote\Api\Data\TotalSegmentExtensionFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\App\ObjectManager;

/**
 * Class \Magento\Tax\Model\Quote\GrandTotalDetailsPlugin
 *
 */
class GrandTotalDetailsPlugin
{
    /**
     * @var \Magento\Tax\Api\Data\GrandTotalDetailsInterfaceFactory
     */
    private $detailsFactory;

    /**
     * @var \Magento\Tax\Api\Data\GrandTotalRatesInterfaceFactory
     */
    private $ratesFactory;

    /**
     * @var TotalSegmentExtensionFactory
     */
    private $totalSegmentExtensionFactory;

    /**
     * @var \Magento\Tax\Model\Config
     */
    private $taxConfig;

    /**
     * @var string
     */
    private $code;

    /**
     * @var Json
     * @since 2.2.0
     */
    private $serializer;

    /**
     * Constructor
     *
     * @param \Magento\Tax\Api\Data\GrandTotalDetailsInterfaceFactory $detailsFactory
     * @param \Magento\Tax\Api\Data\GrandTotalRatesInterfaceFactory $ratesFactory
     * @param TotalSegmentExtensionFactory $totalSegmentExtensionFactory
     * @param \Magento\Tax\Model\Config $taxConfig
     * @param Json $serializer
     */
    public function __construct(
        \Magento\Tax\Api\Data\GrandTotalDetailsInterfaceFactory $detailsFactory,
        \Magento\Tax\Api\Data\GrandTotalRatesInterfaceFactory $ratesFactory,
        TotalSegmentExtensionFactory $totalSegmentExtensionFactory,
        \Magento\Tax\Model\Config $taxConfig,
        Json $serializer
    ) {
        $this->detailsFactory = $detailsFactory;
        $this->ratesFactory = $ratesFactory;
        $this->totalSegmentExtensionFactory = $totalSegmentExtensionFactory;
        $this->taxConfig = $taxConfig;
        $this->serializer = $serializer;
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
     * @param \Magento\Quote\Model\Cart\TotalsConverter $subject
     * @param \Magento\Quote\Api\Data\TotalSegmentInterface[] $totalSegments
     * @param \Magento\Quote\Model\Quote\Address\Total[] $addressTotals
     * @return \Magento\Quote\Api\Data\TotalSegmentInterface[]
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @since 2.2.0
     */
    public function afterProcess(
        \Magento\Quote\Model\Cart\TotalsConverter $subject,
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
