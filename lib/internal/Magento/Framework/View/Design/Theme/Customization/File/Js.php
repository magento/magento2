<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Design\Theme\Customization\File;

/**
 * Theme JS file service class
 * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getType()
    {
        return self::TYPE;
    }

    /**
     * Get content type
     *
     * @return string
     * @since 2.0.0
     */
    public function getContentType()
    {
        return self::CONTENT_TYPE;
    }
}
