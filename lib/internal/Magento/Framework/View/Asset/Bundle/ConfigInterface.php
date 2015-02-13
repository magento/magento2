<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Asset\Bundle;

interface ConfigInterface
{
    /**
     * Get excluded file list
     *
     * @param string $area
     * @return array
     */
    public function getExcludedFiles($area);

    /**
     * Get excluded directory list
     *
     * @param string $area
     * @return array
     */
    public function getExcludedDir($area);
}
