<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Design\Theme\Customization;

/**
 * Theme asset file interface
 */
interface FileAssetInterface
{
    /**
     * Get content type of file
     *
     * @return string
     */
    public function getContentType();
}
