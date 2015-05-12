<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api;

use Magento\Framework\Api\Data\ImageContentInterface;

/**
 * @codeCoverageIgnore
 */
class ImageContent extends \Magento\Framework\Api\AbstractExtensibleObject implements ImageContentInterface
{
    /**
     * Retrieve media data (base64 encoded content)
     *
     * @return string
     */
    public function getBase64EncodedData()
    {
        return $this->_get(self::BASE64_ENCODED_DATA);
    }

    /**
     * Retrieve MIME type
     *
     * @return string
     */
    public function getMimeType()
    {
        return $this->_get(self::MIME_TYPE);
    }

    /**
     * Retrieve image name
     *
     * @return string
     */
    public function getName()
    {
        return $this->_get(self::NAME);
    }

    /**
     * Set media data (base64 encoded content)
     *
     * @param string $data
     * @return $this
     */
    public function setBase64EncodedData($data)
    {
        return $this->setData(self::BASE64_ENCODED_DATA, $data);
    }

    /**
     * Set MIME type
     *
     * @param string $mimeType
     * @return $this
     */
    public function setMimeType($mimeType)
    {
        return $this->setData(self::MIME_TYPE, $mimeType);
    }

    /**
     * Set image name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * {@inheritdoc}
     *
     * @return \Magento\Framework\Api\Data\ImageContentExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     *
     * @param \Magento\Framework\Api\Data\ImageContentExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\Framework\Api\Data\ImageContentExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
