<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Design\Theme\Customization\File;

/**
 * Theme JS file service class
 */
class Js extends \Magento\Framework\View\Design\Theme\Customization\AbstractFile
{
    /**#@+
     * File type customization
     */
    const TYPE = 'js';

    const CONTENT_TYPE = 'js';

    /**#@-*/

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return self::TYPE;
    }

    /**
     * Get content type
     *
     * @return string
     */
    public function getContentType()
    {
        return self::CONTENT_TYPE;
    }
}
