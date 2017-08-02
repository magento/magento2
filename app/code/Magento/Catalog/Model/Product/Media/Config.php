<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Catalog\Model\Product\Media;

use Magento\Eav\Model\Entity\Attribute;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Catalog product media config
 *
 * @api
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Config implements ConfigInterface
{
    /**
     * Store manager
     *
     * @var StoreManagerInterface
     * @since 2.0.0
     */
    protected $storeManager;

    /**
     * @var Attribute
     * @since 2.1.0
     */
    private $attributeHelper;

    /**
     * @param StoreManagerInterface $storeManager
     * @since 2.0.0
     */
    public function __construct(StoreManagerInterface $storeManager)
    {
        $this->storeManager = $storeManager;
    }

    /**
     * Filesystem directory path of product images
     * relatively to media folder
     *
     * @return string
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getBaseMediaUrlAddition()
    {
        return 'catalog/product';
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getBaseMediaPath()
    {
        return 'catalog/product';
    }

    /**
     * @return string
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getBaseTmpMediaPath()
    {
        return 'tmp/' . $this->getBaseMediaPathAddition();
    }

    /**
     * @return string
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getMediaUrl($file)
    {
        return $this->getBaseMediaUrl() . '/' . $this->_prepareFile($file);
    }

    /**
     * @param string $file
     * @return string
     * @since 2.0.0
     */
    public function getMediaPath($file)
    {
        return $this->getBaseMediaPath() . '/' . $this->_prepareFile($file);
    }

    /**
     * @param string $file
     * @return string
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getMediaShortUrl($file)
    {
        return $this->getBaseMediaUrlAddition() . '/' . $this->_prepareFile($file);
    }

    /**
     * @param string $file
     * @return string
     * @since 2.0.0
     */
    public function getTmpMediaPath($file)
    {
        return $this->getBaseTmpMediaPath() . '/' . $this->_prepareFile($file);
    }

    /**
     * @param string $file
     * @return string
     * @since 2.0.0
     */
    protected function _prepareFile($file)
    {
        return ltrim(str_replace('\\', '/', $file), '/');
    }

    /**
     * @return array
     * @since 2.1.0
     */
    public function getMediaAttributeCodes()
    {
        return $this->getAttributeHelper()->getAttributeCodesByFrontendType('media_image');
    }

    /**
     * @return Attribute
     * @since 2.1.0
     */
    private function getAttributeHelper()
    {
        if (null === $this->attributeHelper) {
            $this->attributeHelper = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Eav\Model\Entity\Attribute::class);
        }
        return $this->attributeHelper;
    }
}
