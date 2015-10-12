<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Asset\PreProcessor\Helper;

/**
 * Interface SorterInterface
 */
interface SorterInterface
{
    /**
     * Sorting an array of preprocessors
     *
     * @param array $preprocessors
     * @return array
     */
    public function sorting(array $preprocessors);
}
