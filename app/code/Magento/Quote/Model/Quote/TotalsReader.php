<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Quote;

use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Address\Total\ReaderInterface;

class TotalsReader
{
    /**
     * @var \Magento\Quote\Model\Quote\Address\TotalFactory
     */
    protected $totalFactory;

    /**
     * @var \Magento\Quote\Model\Quote\TotalsCollectorList
     */
    protected $collectorList;

    /**
     * @param Address\TotalFactory $totalFactory
     * @param TotalsCollectorList $collectorList
     */
    public function __construct(
        \Magento\Quote\Model\Quote\Address\TotalFactory $totalFactory,
        \Magento\Quote\Model\Quote\TotalsCollectorList $collectorList
    ) {
        $this->totalFactory = $totalFactory;
        $this->collectorList = $collectorList;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param array $total
     * @return Total[]
     */
    public function fetch(\Magento\Quote\Model\Quote $quote, array $total)
    {
        $output = [];
        $total = $this->totalFactory->create(\Magento\Quote\Model\Quote\Address\Total::class)->setData($total);
        /** @var ReaderInterface $reader */
        foreach ($this->collectorList->getCollectors($quote->getStoreId()) as $reader) {
            $data = $reader->fetch($quote, $total);
            if ($data === null || empty($data)) {
                continue;
            }

            $totalInstance = $this->convert($data);
            if (is_array($totalInstance)) {
                foreach ($totalInstance as $item) {
                    $output = $this->merge($item, $output);
                }
            } else {
                $output = $this->merge($totalInstance, $output);
            }
        }
        return $output;
    }

    /**
     * @param array $total
     * @return Total|Total[]
     */
    protected function convert($total)
    {
        if ($total instanceof Total) {
            return $total;
        }

        if (count(array_column($total, 'code')) > 0) {
            $totals = [];
            foreach ($total as $item) {
                $totals[] = $this->totalFactory->create(
                    \Magento\Quote\Model\Quote\Address\Total::class
                )->setData($item);
            }
            return $totals;
        }

        return $this->totalFactory->create(\Magento\Quote\Model\Quote\Address\Total::class)->setData($total);
    }

    /**
     * @param Total $totalInstance
     * @param Total[] $output
     * @return Total[]
     */
    protected function merge(Total $totalInstance, $output)
    {
        if (array_key_exists($totalInstance->getCode(), $output)) {
            $output[$totalInstance->getCode()] = $output[$totalInstance->getCode()]->addData(
                $totalInstance->getData()
            );
        } else {
            $output[$totalInstance->getCode()] = $totalInstance;
        }
        return $output;
    }
}
