<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Cart;

use Magento\Quote\Api\Data\TotalSegmentInterface;
use Magento\Quote\Api\Data\TotalSegmentInterfaceFactory;

/**
 * Cart totals data objects converter.
 * @since 2.0.0
 */
class TotalsConverter
{
    /**
     * @var TotalSegmentInterfaceFactory
     * @since 2.0.0
     */
    protected $factory;

    /**
     * @param TotalSegmentInterfaceFactory $factory
     * @since 2.0.0
     */
    public function __construct(
        TotalSegmentInterfaceFactory $factory
    ) {
        $this->factory = $factory;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address\Total[] $addressTotals
     * @return \Magento\Quote\Api\Data\TotalSegmentInterface[]
     * @since 2.0.0
     */
    public function process($addressTotals)
    {
        $data = [];
        /** @var \Magento\Quote\Model\Quote\Address\Total $addressTotal */
        foreach ($addressTotals as $addressTotal) {
            $pureData = [
                TotalSegmentInterface::CODE => $addressTotal->getCode(),
                TotalSegmentInterface::TITLE => '',
                TotalSegmentInterface::VALUE => $addressTotal->getValue(),
                TotalSegmentInterface::AREA => $addressTotal->getArea(),
            ];
            if (is_object($addressTotal->getTitle())) {
                $pureData[TotalSegmentInterface::TITLE] = $addressTotal->getTitle()->render();
            }
            /** @var \Magento\Quote\Model\Cart\TotalSegment $total */
            $total = $this->factory->create();
            $total->setData($pureData);
            $data[$addressTotal->getCode()] = $total;
        }
        return $data;
    }
}
