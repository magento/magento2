<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model;

/**
 * Filter custom attributes for product using the excluded list
 */
class FilterProductCustomAttribute
{
    /**
     * @var array
     */
    private $excludedList;

    /**
     * @param array $excludedList
     */
    public function __construct(array $excludedList = [])
    {
        $this->excludedList = $excludedList;
    }

    /**
     * Delete custom attribute
     *
     * @param array $attributes set objects attributes @example ['attribute_code'=>'attribute_object']
     * @return array
     */
    public function execute(array $attributes): array
    {
        return array_diff_key($attributes, array_flip($this->excludedList));
    }
}
