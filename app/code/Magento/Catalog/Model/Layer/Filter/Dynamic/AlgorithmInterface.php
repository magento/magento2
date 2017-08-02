<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Layer\Filter\Dynamic;

/**
 * @api
 * @since 2.0.0
 */
interface AlgorithmInterface
{
    /**
     * @param int[] $intervals
     * @param string $additionalRequestData
     * @return array
     * @since 2.0.0
     */
    public function getItemsData(array $intervals = [], $additionalRequestData = '');
}
