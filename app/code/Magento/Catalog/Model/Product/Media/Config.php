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
 * @package     Magento_Catalog
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Catalog product media config
 *
 * @category    Magento
 * @package     Magento_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Model\Product\Media;

class Config implements \Magento\Media\Model\Image\Config\ConfigInterface
{
    /**
     * Dir
     *
     * @var \Magento\App\Dir
     */
    protected $_dir;

    /**
     * Store manager
     *
     * @var \Magento\Core\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Construct
     *
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\App\Dir $dir
     */
    public function __construct(
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\App\Dir $dir
    ) {
        $this->_storeManager = $storeManager;
        $this->_dir = $dir;
    }

    /**
     * Filesystem directory path of product images
     * relatively to media folder
     *
     * @return string
     */
    public function getBaseMediaPathAddition()
    {
        return 'catalog' . DIRECTORY_SEPARATOR . 'product';
    }

    /**
     * Web-based directory path of product images
     * relatively to media folder
     *
     * @return string
     */
    public function getBaseMediaUrlAddition()
    {
        return 'catalog/product';
    }

    /**
     * Filesystem directory path of temporary product images
     * relatively to media folder
     *
     * @return string
     */
    public function getBaseTmpMediaPathAddition()
    {
        return 'tmp' . DIRECTORY_SEPARATOR . $this->getBaseMediaPathAddition();
    }

    /**
     * Web-based directory path of temporary product images
     * relatively to media folder
     *
     * @return string
     */
    public function getBaseTmpMediaUrlAddition()
    {
        return 'tmp/' . $this->getBaseMediaUrlAddition();
    }

    public function getBaseMediaPath()
    {
        return $this->_dir->getDir(\Magento\App\Dir::MEDIA) . DIRECTORY_SEPARATOR
            . 'catalog' . DIRECTORY_SEPARATOR . 'product';
    }

    public function getBaseMediaUrl()
    {
        return $this->_storeManager->getStore()
            ->getBaseUrl(\Magento\Core\Model\Store::URL_TYPE_MEDIA) . 'catalog/product';
    }

    public function getBaseTmpMediaPath()
    {
        return $this->_dir->getDir(\Magento\App\Dir::MEDIA) . DIRECTORY_SEPARATOR
            . $this->getBaseTmpMediaPathAddition();
    }

    public function getBaseTmpMediaUrl()
    {
        return $this->_storeManager->getStore()
            ->getBaseUrl(\Magento\Core\Model\Store::URL_TYPE_MEDIA) . $this->getBaseTmpMediaUrlAddition();
    }

    public function getMediaUrl($file)
    {
        $file = $this->_prepareFileForUrl($file);

        if(substr($file, 0, 1) == '/') {
            return $this->getBaseMediaUrl() . $file;
        }

        return $this->getBaseMediaUrl() . '/' . $file;
    }

    public function getMediaPath($file)
    {
        $file = $this->_prepareFileForPath($file);

        if(substr($file, 0, 1) == DIRECTORY_SEPARATOR) {
            return $this->getBaseMediaPath() . DIRECTORY_SEPARATOR . substr($file, 1);
        }

        return $this->getBaseMediaPath() . DIRECTORY_SEPARATOR . $file;
    }

    public function getTmpMediaUrl($file)
    {
        $file = $this->_prepareFileForUrl($file);

        if(substr($file, 0, 1) == '/') {
            $file = substr($file, 1);
        }

        return $this->getBaseTmpMediaUrl() . '/' . $file;
    }

    /**
     * Part of URL of temporary product images
     * relatively to media folder
     *
     * @return string
     */
    public function getTmpMediaShortUrl($file)
    {
        $file = $this->_prepareFileForUrl($file);

        if(substr($file, 0, 1) == '/') {
            $file = substr($file, 1);
        }

        return $this->getBaseTmpMediaUrlAddition() . '/' . $file;
    }

    /**
     * Part of URL of product images relatively to media folder
     *
     * @return string
     */
    public function getMediaShortUrl($file)
    {
        $file = $this->_prepareFileForUrl($file);

        if(substr($file, 0, 1) == '/') {
            $file = substr($file, 1);
        }

        return $this->getBaseMediaUrlAddition() . '/' . $file;
    }

    public function getTmpMediaPath($file)
    {
        $file = $this->_prepareFileForPath($file);

        if(substr($file, 0, 1) == DIRECTORY_SEPARATOR) {
            return $this->getBaseTmpMediaPath() . DIRECTORY_SEPARATOR . substr($file, 1);
        }

        return $this->getBaseTmpMediaPath() . DIRECTORY_SEPARATOR . $file;
    }

    protected function _prepareFileForUrl($file)
    {
        return str_replace(DIRECTORY_SEPARATOR, '/', $file);
    }

    protected function _prepareFileForPath($file)
    {
        return str_replace('/', DIRECTORY_SEPARATOR, $file);
    }
}
