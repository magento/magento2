<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ProductVideo\Model\Product\Attribute\Media;

use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Framework\Api\Data\VideoContentInterface;

/**
 * VideoEntry class
 * @since 2.0.0
 */
class VideoEntry extends AbstractExtensibleModel implements VideoContentInterface
{
    /**
     * Retrieve MIME type
     *
     * @return string
     * @since 2.0.0
     */
    public function getMediaType()
    {
        return $this->getData(self::TYPE);
    }

    /**
     * Set MIME type
     *
     * @param string $mimeType
     * @return $this
     * @since 2.0.0
     */
    public function setMediaType($mimeType)
    {
        return $this->setData(self::TYPE, $mimeType);
    }

    /**
     * Get provider YouTube|Vimeo
     *
     * @return string
     * @since 2.0.0
     */
    public function getVideoProvider()
    {
        return $this->getData(self::PROVIDER);
    }

    /**
     * Set provider
     *
     * @param string $data
     * @return $this
     * @since 2.0.0
     */
    public function setVideoProvider($data)
    {
        return $this->setData(self::PROVIDER, $data);
    }

    /**
     * Get video URL
     *
     * @return string
     * @since 2.0.0
     */
    public function getVideoUrl()
    {
        return $this->getData(self::URL);
    }

    /**
     * Set video URL
     *
     * @param string $data
     * @return $this
     * @since 2.0.0
     */
    public function setVideoUrl($data)
    {
        return $this->setData(self::URL, $data);
    }

    /**
     * Get Title
     *
     * @return string
     * @since 2.0.0
     */
    public function getVideoTitle()
    {
        return $this->getData(self::TITLE);
    }

    /**
     * Set Title
     *
     * @param string $data
     * @return $this
     * @since 2.0.0
     */
    public function setVideoTitle($data)
    {
        return $this->setData(self::TITLE, $data);
    }

    /**
     * Get video Description
     *
     * @return string
     * @since 2.0.0
     */
    public function getVideoDescription()
    {
        return $this->getData(self::DESCRIPTION);
    }

    /**
     * Set video Description
     *
     * @param string $data
     * @return $this
     * @since 2.0.0
     */
    public function setVideoDescription($data)
    {
        return $this->setData(self::DESCRIPTION, $data);
    }

    /**
     * Get Metadata
     *
     * @return string
     * @since 2.0.0
     */
    public function getVideoMetadata()
    {
        return $this->getData(self::METADATA);
    }

    /**
     * Set Metadata
     *
     * @param string $data
     * @return $this
     * @since 2.0.0
     */
    public function setVideoMetadata($data)
    {
        return $this->setData(self::METADATA, $data);
    }

    /**
     * Get extension attributes
     *
     * @return \Magento\Framework\Api\ExtensionAttributesInterface
     * @since 2.0.0
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set extension attributes
     *
     * @param \Magento\Catalog\Api\Data\ProductExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(\Magento\Catalog\Api\Data\ProductExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
