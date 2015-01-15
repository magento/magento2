<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 *  Theme customization service class for custom css
 */
namespace Magento\Theme\Model\Theme\Customization\File;

class CustomCss extends \Magento\Framework\View\Design\Theme\Customization\AbstractFile
{
    /**#@+
     * Custom CSS file type customization
     */
    const TYPE = 'custom_css';

    const CONTENT_TYPE = 'css';

    /**#@-*/

    /**
     * Default filename
     */
    const FILE_NAME = 'custom.css';

    /**
     * Default order position
     */
    const SORT_ORDER = 10;

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return self::TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function getContentType()
    {
        return self::CONTENT_TYPE;
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareFileName(\Magento\Framework\View\Design\Theme\FileInterface $file)
    {
        if (!$file->getFileName()) {
            $file->setFileName(self::FILE_NAME);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareSortOrder(\Magento\Framework\View\Design\Theme\FileInterface $file)
    {
        $file->setData('sort_order', self::SORT_ORDER);
    }
}
