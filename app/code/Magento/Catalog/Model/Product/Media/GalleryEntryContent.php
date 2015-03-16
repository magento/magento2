<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Media;

/**
 * @codeCoverageIgnore
 */
class GalleryEntryContent extends \Magento\Framework\Model\AbstractExtensibleModel implements
    \Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryContentInterface
{
    /**
     * {@inheritdoc}
     */
    public function getEntryData()
    {
        return $this->getData(self::DATA);
    }

    /**
     * {@inheritdoc}
     */
    public function getMimeType()
    {
        return $this->getData(self::MIME_TYPE);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getData(self::NAME);
    }

    /**
     * Set media data (base64 encoded content)
     *
     * @param string $entryData
     * @return $this
     */
    public function setEntryData($entryData)
    {
        return $this->setData(self::DATA, $entryData);
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
     * @return \Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryContentExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     *
     * @param \Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryContentExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryContentExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
