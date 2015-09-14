<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */


namespace Magento\Quote\Model\Quote\Address;

class TotalsList
{
    /**
     * @var \Magento\Quote\Model\Quote\Address\Total[]
     */
    protected $list;

    /**
     * @var \Magento\Quote\Model\Quote\Address\TotalFactory
     */
    protected $totalFactory;

    /**
     * @var string[]
     */
    protected $fetchedData;

    /**
     * TotalsList constructor.
     * @param TotalFactory $totalFactory
     */
    public function __construct(TotalFactory $totalFactory)
    {
        $this->totalFactory = $totalFactory;
    }

    /**
     * @return Total[]
     */
    public function getList()
    {
        return $this->list;
    }
    /**
     * Add total data or model
     *
     * @param \Magento\Quote\Model\Quote\Address\Total|array $total
     * @param array $data
     * @return $this
     */
    public function add($total, $data)
    {
        if ($total instanceof \Magento\Quote\Model\Quote\Address\Total) {
            $totalInstance = $total;
        } else {
            $totalInstance = $this->totalFactory->create('Magento\Quote\Model\Quote\Address\Total')->setData($total);
        }
        $data = $this->totalFactory->create('Magento\Quote\Model\Quote\Address\Total')->setData($data);
        /** @var  $totalInstance \Magento\Quote\Model\Quote\Address\Total */
        $this->list[$data->getCode()] = $totalInstance;
        $this->fetchedData[$data->getCode()] = $data;
        return $this;
    }

    /**
     * @return \Magento\Quote\Model\Quote\Address\Total[]
     */
    public function fetch()
    {
        return $this->fetchedData;
    }

    /**
     * @return Total
     */
    public function merge()
    {
        /** @var \Magento\Quote\Model\Quote\Address\Total $total */
        $total = $this->totalFactory->create('Magento\Quote\Model\Quote\Address\Total');

        foreach ($this->getList() as $totalItem) {
            $total->merge($totalItem);
        }
        return $total;
    }
}
