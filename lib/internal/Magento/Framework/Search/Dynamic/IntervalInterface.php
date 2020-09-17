<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Dynamic;

/**
 * @api
 * @since 100.0.2
 */
interface IntervalInterface
{
    /**
     * @param int $limit
     * @param null|int $offset
     * @param null|int $lower
     * @param null|int $upper
     * @return array
     */
    public function load($limit, $offset = null, $lower = null, $upper = null);

    /**
     * @param float $data
     * @param int $index
     * @param null|int $lower
     * @return array
     */
    public function loadPrevious($data, $index, $lower = null);

    /**
     * @param float $data
     * @param int $rightIndex
     * @param null|int $upper
     * @return array
     */
    public function loadNext($data, $rightIndex, $upper = null);
}
