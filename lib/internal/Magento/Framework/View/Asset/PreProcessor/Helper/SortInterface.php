<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\View\Asset\PreProcessor\Helper;

/**
 * Interface SortInterface
 */
interface SortInterface
{
    /**
     * Sorting an array by directive
     * [
     *     'name-1' => ['after' => 'xxx', 'data' => [...]]
     *     'name-2' => ['after' => 'xxx', 'data' => [...]]
     * ]
     * @param array $array
     * @return array
     */
    public function sort(array $array);
}
