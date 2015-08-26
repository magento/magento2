<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ProductVideo\Model\Product\Media;

use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Framework\Api\Data\VideoContentInterface;

/**
 * @codeCoverageIgnore
 */
class VideoEntry extends AbstractExtensibleModel implements VideoContentInterface
{
    /**
     * Retrieve MIME type
     *
     * @return string
     */
    public function getType()
    {
        return $this->getData(self::TYPE);
    }

    /**
     * Set MIME type
     *
     * @param string $mimeType
     * @return $this
     */
    public function setType($mimeType)
    {
        return $this->setData(self::TYPE, $mimeType);
    }

    /**
     * Get provider YouTube|Vimeo
     *
     * @return string
     */
    public function getProvider()
    {
        return $this->getData(self::PROVIDER);
    }

    /**
     * Set provider
     *
     * @param string $data
     * @return $this
     */
    public function setProvider($data)
    {
        return $this->setData(self::PROVIDER, $data);
    }

    /**
     * Get video URL
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->getData(self::URL);
    }

    /**
     * Set video URL
     *
     * @param string $data
     * @return $this
     */
    public function setUrl($data)
    {
        return $this->setData(self::URL, $data);
    }

    /**
     * Get Title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->getData(self::TITLE);
    }

    /**
     * Set Title
     *
     * @param string $data
     * @return $this
     */
    public function setTitle($data)
    {
        return $this->setData(self::TITLE, $data);
    }

    /**
     * Get video Description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->getData(self::DESCRIPTION);
    }

    /**
     * Set video Description
     *
     * @param string $data
     * @return $this
     */
    public function setDescription($data)
    {
        return $this->setData(self::DESCRIPTION);
    }

    /**
     * Get Metadata
     *
     * @return string
     */
    public function getMetadata()
    {
        return $this->getData(self::METADATA);
    }

    /**
     * Set Metadata
     *
     * @param string $data
     * @return $this
     */
    public function setMetadata($data)
    {
        return $this->setData(self::METADATA, $data);
    }
}
