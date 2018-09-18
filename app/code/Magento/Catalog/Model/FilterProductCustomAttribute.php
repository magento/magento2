<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model;

/**
 * Filter custom attributes for product using the blacklist
 */
class FilterProductCustomAttribute
{
    /**
     * @var array
     */
    private $blackList;

    /**
     * @param array $blackList
     */
    public function __construct(array $blackList = [])
    {
        $this->blackList = $blackList;
    }

    /**
     * Delete custom attribute
     * @param array $attributes
     * @return array
     */
    public function execute(array $attributes): array
    {
        return array_diff($attributes, $this->blackList);
    }
}
