<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
