<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\App\View\Asset\Bundle;

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
