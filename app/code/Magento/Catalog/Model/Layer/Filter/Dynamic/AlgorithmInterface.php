<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Layer\Filter\Dynamic;

interface AlgorithmInterface
{
    /**
     * @param int[] $intervals
     * @param string $additionalRequestData
     * @return array
     */
    public function getItemsData(array $intervals = [], $additionalRequestData = '');
}
