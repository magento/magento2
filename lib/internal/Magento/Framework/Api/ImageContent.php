<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api;

use Magento\Framework\Api\Data\ImageContentInterface;

/**
 * Image Content data object
 *
 * @codeCoverageIgnore
 */
class ImageContent extends \Magento\Framework\Api\AbstractExtensibleObject implements ImageContentInterface
{
    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getBase64EncodedData()
    {
        return $this->_get(self::BASE64_ENCODED_DATA);
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getType()
    {
        return $this->_get(self::TYPE);
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getName()
    {
        return $this->_get(self::NAME);
    }

    /**
     * {@inheritdoc}
     *
     * @param string $data
     * @return $this
     */
    public function setBase64EncodedData($data)
    {
        return $this->setData(self::BASE64_ENCODED_DATA, $data);
    }

    /**
     * {@inheritdoc}
     *
     * @param string $mimeType
     * @return $this
     */
    public function setType($mimeType)
    {
        return $this->setData(self::TYPE, $mimeType);
    }

    /**
     * {@inheritdoc}
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
