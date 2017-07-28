<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Design\Theme\Customization\File;

/**
 * Theme CSS file service class
 * @since 2.0.0
 */
class Css extends \Magento\Framework\View\Design\Theme\Customization\AbstractFile
{
    /**#@+
     * CSS file customization types
     */
    const TYPE = 'css';

    const CONTENT_TYPE = 'css';

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
