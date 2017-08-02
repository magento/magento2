<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Gallery;

use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface;
use Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryExtensionInterface;

/**
 * @codeCoverageIgnore
 * @since 2.0.0
 */
class Entry extends AbstractExtensibleModel implements ProductAttributeMediaGalleryEntryInterface
{
    /**
     * Retrieve gallery entry ID
     *
     * @return int
     * @since 2.0.0
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * Get media type
     *
     * @return string
     * @since 2.0.0
     */
    public function getMediaType()
    {
        return $this->getData(self::MEDIA_TYPE);
    }

    /**
     * Retrieve gallery entry alternative text
     *
     * @return string
     * @since 2.0.0
     */
    public function getLabel()
    {
        return $this->getData(self::LABEL);
    }

    /**
     * Retrieve gallery entry position (sort order)
     *
     * @return int
     * @since 2.0.0
     */
    public function getPosition()
    {
        return $this->getData(self::POSITION);
    }

    /**
     * Check if gallery entry is hidden from product page
     *
     * @return bool
     * @since 2.0.0
     */
    public function isDisabled()
    {
        return $this->getData(self::DISABLED);
    }

    /**
     * Retrieve gallery entry image types (thumbnail, image, small_image etc)
     *
     * @return string[]
     * @since 2.0.0
     */
    public function getTypes()
    {
        return $this->getData(self::TYPES);
    }

    /**
     * Get file path
     *
     * @return string
     * @since 2.0.0
     */
    public function getFile()
    {
        return $this->getData(self::FILE);
    }

    /**
     * @return \Magento\Framework\Api\Data\ImageContentInterface|null
     * @since 2.0.0
     */
    public function getContent()
    {
        return $this->getData(self::CONTENT);
    }

    /**
     * Set media type
     *
     * @param string $mediaType
     * @return $this
     * @since 2.0.0
     */
    public function setMediaType($mediaType)
    {
        return $this->setData(self::MEDIA_TYPE, $mediaType);
    }

    /**
     * Set gallery entry alternative text
     *
     * @param string $label
     * @return $this
     * @since 2.0.0
     */
    public function setLabel($label)
    {
        return $this->setData(self::LABEL, $label);
    }

    /**
     * Set gallery entry position (sort order)
     *
     * @param int $position
     * @return $this
     * @since 2.0.0
     */
    public function setPosition($position)
    {
        return $this->setData(self::POSITION, $position);
    }

    /**
     * Set whether gallery entry is hidden from product page
     *
     * @param bool $disabled
     * @return $this
     * @since 2.0.0
     */
    public function setDisabled($disabled)
    {
        return $this->setData(self::DISABLED, $disabled);
    }

    /**
     * Set gallery entry image types (thumbnail, image, small_image etc)
     *
     * @param string[] $types
     * @return $this
     * @since 2.0.0
     */
    public function setTypes(array $types = null)
    {
        return $this->setData(self::TYPES, $types);
    }

    /**
     * Set file path
     *
     * @param string $file
     * @return $this
     * @since 2.0.0
     */
    public function setFile($file)
    {
        return $this->setData(self::FILE, $file);
    }

    /**
     * Set media gallery content
     *
     * @param $content \Magento\Framework\Api\Data\ImageContentInterface
     * @return $this
     * @since 2.0.0
     */
    public function setContent($content)
    {
        return $this->setData(self::CONTENT, $content);
    }

    /**
     * {@inheritdoc}
     *
     * @return ProductAttributeMediaGalleryEntryExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     *
     * @param ProductAttributeMediaGalleryEntryExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(ProductAttributeMediaGalleryEntryExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
