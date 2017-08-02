<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Design\Theme\Customization;

/**
 * Theme asset file interface
 * @since 2.0.0
 */
interface FileAssetInterface
{
    /**
     * Get content type of file
     *
     * @return string
     * @since 2.0.0
     */
    public function getContentType();
}
