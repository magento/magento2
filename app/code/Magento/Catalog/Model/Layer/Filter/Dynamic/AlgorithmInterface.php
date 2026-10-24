<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Model\Layer\Filter\Dynamic;

/**
 * @api
 * @since 100.0.2
 */
interface AlgorithmInterface
{
    /**
     * @param int[] $intervals
     * @param string $additionalRequestData
     * @return array
     */
    public function getItemsData(array $intervals = [], $additionalRequestData = '');
}
