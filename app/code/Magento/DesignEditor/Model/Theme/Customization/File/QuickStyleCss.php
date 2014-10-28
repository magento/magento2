<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
