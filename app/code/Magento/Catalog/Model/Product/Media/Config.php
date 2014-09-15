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
namespace Magento\Catalog\Model\Product\Media;

/**
 * Catalog product media config
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Config implements ConfigInterface
{
    /**
     * Store manager
     *
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     */
    public function __construct(\Magento\Framework\StoreManagerInterface $storeManager)
    {
        $this->storeManager = $storeManager;
    }

    /**
     * Filesystem directory path of product images
     * relatively to media folder
     *
     * @return string
     */
    public function getBaseMediaPathAddition()
    {
        return 'catalog/product';
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
     * @return string
     */
    public function getBaseMediaPath()
    {
        return 'catalog/product';
    }

    /**
     * @return string
     */
    public function getBaseMediaUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product';
    }

    /**
     * Filesystem directory path of temporary product images
     * relatively to media folder
     *
     * @return string
     */
    public function getBaseTmpMediaPath()
    {
        return 'tmp/' . $this->getBaseMediaPathAddition();
    }

    /**
     * @return string
     */
    public function getBaseTmpMediaUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl(
            \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
        ) . 'tmp/' . $this->getBaseMediaUrlAddition();
    }

    /**
     * @param string $file
     * @return string
     */
    public function getMediaUrl($file)
    {
        return $this->getBaseMediaUrl() . '/' . $this->_prepareFile($file);
    }

    /**
     * @param string $file
     * @return string
     */
    public function getMediaPath($file)
    {
        return $this->getBaseMediaPath() . '/' . $this->_prepareFile($file);
    }

    /**
     * @param string $file
     * @return string
     */
    public function getTmpMediaUrl($file)
    {
        return $this->getBaseTmpMediaUrl() . '/' . $this->_prepareFile($file);
    }

    /**
     * Part of URL of temporary product images
     * relatively to media folder
     *
     * @param string $file
     * @return string
     */
    public function getTmpMediaShortUrl($file)
    {
        return 'tmp/' . $this->getBaseMediaUrlAddition() . '/' . $this->_prepareFile($file);
    }

    /**
     * Part of URL of product images relatively to media folder
     *
     * @param string $file
     * @return string
     */
    public function getMediaShortUrl($file)
    {
        return $this->getBaseMediaUrlAddition() . '/' . $this->_prepareFile($file);
    }

    /**
     * @param string $file
     * @return string
     */
    public function getTmpMediaPath($file)
    {
        return $this->getBaseTmpMediaPath() . '/' . $this->_prepareFile($file);
    }

    /**
     * @param string $file
     * @return string
     */
    protected function _prepareFile($file)
    {
        return ltrim(str_replace('\\', '/', $file), '/');
    }
}
