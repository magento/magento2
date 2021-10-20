<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Media;

use Magento\Eav\Model\Entity\Attribute;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Catalog product media config.
 *
 * @api
 * @since 100.0.2
 */
class Config implements ConfigInterface
{
    /**
     * Store manager
     *
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Attribute
     */
    private $attributeHelper;

    /**
     * @var string[]
     */
    private $mediaAttributeCodes;

    /**
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(StoreManagerInterface $storeManager)
    {
        $this->storeManager = $storeManager;
    }

    /**
     * Get filesystem directory path for product images relative to the media directory.
     *
     * @return string
     */
    public function getBaseMediaPathAddition()
    {
        return 'catalog/product';
    }

    /**
     * Get web-based directory path for product images relative to the media directory.
     *
     * @return string
     */
    public function getBaseMediaUrlAddition()
    {
        return 'catalog/product';
    }

    /**
     * @inheritdoc
     */
    public function getBaseMediaPath()
    {
        return 'catalog/product';
    }

    /**
     * @inheritdoc
     */
    public function getBaseMediaUrl()
    {
        return $this->storeManager->getStore()
                ->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . $this->getBaseMediaUrlAddition();
    }

    /**
     * Filesystem directory path of temporary product images relative to the media directory.
     *
     * @return string
     */
    public function getBaseTmpMediaPath()
    {
        return 'tmp/' . $this->getBaseMediaPathAddition();
    }

    /**
     * Get temporary base media URL.
     *
     * @return string
     */
    public function getBaseTmpMediaUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl(
            UrlInterface::URL_TYPE_MEDIA
        ) . 'tmp/' . $this->getBaseMediaUrlAddition();
    }

    /**
     * @inheritdoc
     */
    public function getMediaUrl($file)
    {
        return $this->getBaseMediaUrl() . '/' . $this->_prepareFile($file);
    }

    /**
     * @inheritdoc
     */
    public function getMediaPath($file)
    {
        return $this->getBaseMediaPath() . '/' . $this->_prepareFile($file);
    }

    /**
     * Get temporary media URL.
     *
     * @param string $file
     * @return string
     */
    public function getTmpMediaUrl($file)
    {
        return $this->getBaseTmpMediaUrl() . '/' . $this->_prepareFile($file);
    }

    /**
     * Part of URL of temporary product images relative to the media directory.
     *
     * @param string $file
     * @return string
     */
    public function getTmpMediaShortUrl($file)
    {
        return 'tmp/' . $this->getBaseMediaUrlAddition() . '/' . $this->_prepareFile($file);
    }

    /**
     * Part of URL of product images relatively to media folder.
     *
     * @param string $file
     * @return string
     */
    public function getMediaShortUrl($file)
    {
        return $this->getBaseMediaUrlAddition() . '/' . $this->_prepareFile($file);
    }

    /**
     * Get path to the temporary media.
     *
     * @param string $file
     * @return string
     */
    public function getTmpMediaPath($file)
    {
        return $this->getBaseTmpMediaPath() . '/' . $this->_prepareFile($file);
    }

    /**
     * Process file path.
     *
     * @param string $file
     * @return string
     */
    protected function _prepareFile($file)
    {
        return ltrim(str_replace('\\', '/', $file), '/');
    }

    /**
     * Get codes of media attribute.
     *
     * @return array
     * @since 100.0.4
     */
    public function getMediaAttributeCodes()
    {
        if (!isset($this->mediaAttributeCodes)) {
            // the in-memory object-level caching allows to prevent unnecessary calls to the DB
            $this->mediaAttributeCodes = $this->getAttributeHelper()->getAttributeCodesByFrontendType('media_image');
        }
        return $this->mediaAttributeCodes;
    }

    /**
     * Get attribute helper.
     *
     * @return Attribute
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
