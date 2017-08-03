<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Asset\PreProcessor\Helper;

/**
 * Interface SortInterface
 * @since 2.0.0
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
     * @since 2.0.0
     */
    public function sort(array $array);
}
