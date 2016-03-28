<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\UiComponent\Config;

/**
 * Interface FileCollectorInterface
 */
interface FileCollectorInterface
{
    /**
     * Collect files
     *
     * @param string|null $searchPattern
     * @return array
     */
    public function collectFiles($searchPattern = null);
}
