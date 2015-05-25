<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Cart;

use Magento\Quote\Api\Data\CalculatedTotalsInterface;
/**
 * Cart totals data objects converter.
 */
class TotalsConverter
{
    /**
     * @var \Magento\Quote\Api\Data\CalculatedTotalsInterfaceFactory
     */
    protected $factory;

    /**
     * @param \Magento\Quote\Api\Data\CalculatedTotalsInterfaceFactory $factory
     */
    public function __construct(
        \Magento\Quote\Api\Data\CalculatedTotalsInterfaceFactory $factory
    ) {
        $this->factory = $factory;
    }


    /**
     * @param \Magento\Quote\Model\Quote\Address\Total[] $addressTotals
     * @return \Magento\Quote\Api\Data\CalculatedTotalsInterface[]
     */
    public function process($addressTotals)
    {
        $data = [];
        /** @var \Magento\Quote\Model\Quote\Address\Total $addressTotal */
        foreach ($addressTotals as $addressTotal) {
            $pureData = [
                CalculatedTotalsInterface::CODE => $addressTotal->getCode(),
                CalculatedTotalsInterface::TITLE => $addressTotal->getTitle()->getText(),
                CalculatedTotalsInterface::VALUE => $addressTotal->getValue(),
                CalculatedTotalsInterface::AREA => $addressTotal->getArea(),
            ];
            /** @var \Magento\Quote\Model\Cart\CalculatedTotals $total */
            $total = $this->factory->create();
            $total->setData($pureData);
            $data[] = $total;
        }
        return $data;
    }
}
