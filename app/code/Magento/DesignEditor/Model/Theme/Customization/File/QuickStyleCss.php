<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Theme customization service class for quick styles
 */
namespace Magento\DesignEditor\Model\Theme\Customization\File;

class QuickStyleCss extends \Magento\Framework\View\Design\Theme\Customization\AbstractFile
{
    /**#@+
     * QuickStyles CSS file type customization
     */
    const TYPE = 'quick_style_css';

    const CONTENT_TYPE = 'css';

    /**#@-*/

    /**
     * Default filename
     */
    const FILE_NAME = 'quick_style.css';

    /**
     * Default order position
     */
    const SORT_ORDER = 20;

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
        $file->setFileName(self::FILE_NAME);
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareSortOrder(\Magento\Framework\View\Design\Theme\FileInterface $file)
    {
        $file->setData('sort_order', self::SORT_ORDER);
    }
}
