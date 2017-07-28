<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\UiComponent\Config;

/**
 * Interface FileCollectorInterface
 * @since 2.0.0
 */
interface FileCollectorInterface
{
    /**
     * Collect files
     *
     * @param string|null $searchPattern
     * @return array
     * @since 2.0.0
     */
    public function collectFiles($searchPattern = null);
}
