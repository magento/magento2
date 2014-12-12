<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
