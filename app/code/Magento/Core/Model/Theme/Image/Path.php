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
 * @category    Magento
 * @package     Magento_Core
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Theme Image Path
 */
namespace Magento\Core\Model\Theme\Image;

class Path
{
    /**
     * Image preview path
     */
    const PREVIEW_DIRECTORY_PATH = 'theme/preview';

    /**
     * Default theme preview image
     */
    const DEFAULT_PREVIEW_IMAGE = 'Magento_Core::theme/default_preview.jpg';

    /**
     * @var \Magento\App\Dir
     */
    protected $_dir;

    /**
     * @var \Magento\Core\Model\View\Url
     */
    protected $_viewUrl;

    /**
     * @var \Magento\Core\Model\StoreManager
     */
    protected $_storeManager;

    /**
     * Initialize dependencies
     *
     * @param \Magento\App\Dir $dir
     * @param \Magento\Core\Model\View\Url $viewUrl
     * @param \Magento\Core\Model\StoreManager $storeManager
     */
    public function __construct(
        \Magento\App\Dir $dir,
        \Magento\Core\Model\View\Url $viewUrl,
        \Magento\Core\Model\StoreManager $storeManager
    ) {
        $this->_dir = $dir;
        $this->_viewUrl = $viewUrl;
        $this->_storeManager = $storeManager;
    }

    /**
     * Get preview image directory url
     *
     * @return string
     */
    public function getPreviewImageDirectoryUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Core\Model\Store::URL_TYPE_MEDIA)
            . self::PREVIEW_DIRECTORY_PATH . '/';
    }

    /**
     * Return default themes preview image url
     *
     * @return string
     */
    public function getPreviewImageDefaultUrl()
    {
        return $this->_viewUrl->getViewFileUrl(self::DEFAULT_PREVIEW_IMAGE);
    }

    /**
     * Get directory path for preview image
     *
     * @return string
     */
    public function getImagePreviewDirectory()
    {
        return $this->_dir->getDir(\Magento\App\Dir::MEDIA) . DIRECTORY_SEPARATOR
            . str_replace('/', DIRECTORY_SEPARATOR, self::PREVIEW_DIRECTORY_PATH);
    }

    /**
     * Temporary directory path to store images
     *
     * @return string
     */
    public function getTemporaryDirectory()
    {
        return implode(DIRECTORY_SEPARATOR, array(
            $this->_dir->getDir(\Magento\App\Dir::MEDIA), 'theme', 'origin'
        ));
    }
}
