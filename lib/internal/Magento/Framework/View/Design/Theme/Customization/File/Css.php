<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Design\Theme\Customization\File;

/**
 * Theme CSS file service class
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
