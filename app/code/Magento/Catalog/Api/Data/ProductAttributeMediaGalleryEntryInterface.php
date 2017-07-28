<?php
/**
 * Product Media Attribute
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * @api
 * @since 2.0.0
 */
interface ProductAttributeMediaGalleryEntryInterface extends ExtensibleDataInterface
{
    const ID = 'id';
    const LABEL = 'label';
    const POSITION = 'position';
    const DISABLED = 'disabled';
    const TYPES = 'types';
    const MEDIA_TYPE = 'media_type';
    const FILE = 'file';
    const CONTENT = 'content';

    /**
     * Retrieve gallery entry ID
     *
     * @return int|null
     * @since 2.0.0
     */
    public function getId();

    /**
     * Set gallery entry ID
     *
     * @param int $id
     * @return $this
     * @since 2.0.0
     */
    public function setId($id);

    /**
     * Get media type
     *
     * @return string
     * @since 2.0.0
     */
    public function getMediaType();

    /**
     * Set media type
     *
     * @param string $mediaType
     * @return $this
     * @since 2.0.0
     */
    public function setMediaType($mediaType);

    /**
     * Retrieve gallery entry alternative text
     *
     * @return string
     * @since 2.0.0
     */
    public function getLabel();

    /**
     * Set gallery entry alternative text
     *
     * @param string $label
     * @return $this
     * @since 2.0.0
     */
    public function setLabel($label);

    /**
     * Retrieve gallery entry position (sort order)
     *
     * @return int
     * @since 2.0.0
     */
    public function getPosition();

    /**
     * Set gallery entry position (sort order)
     *
     * @param int $position
     * @return $this
     * @since 2.0.0
     */
    public function setPosition($position);

    /**
     * Check if gallery entry is hidden from product page
     *
     * @return bool
     * @since 2.0.0
     */
    public function isDisabled();

    /**
     * Set whether gallery entry is hidden from product page
     *
     * @param bool $disabled
     * @return $this
     * @since 2.0.0
     */
    public function setDisabled($disabled);

    /**
     * Retrieve gallery entry image types (thumbnail, image, small_image etc)
     *
     * @return string[]
     * @since 2.0.0
     */
    public function getTypes();

    /**
     * Set gallery entry image types (thumbnail, image, small_image etc)
     *
     * @param string[] $types
     * @return $this
     * @since 2.0.0
     */
    public function setTypes(array $types = null);

    /**
     * Get file path
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getFile();

    /**
     * Set file path
     *
     * @param string $file
     * @return $this
     * @since 2.0.0
     */
    public function setFile($file);

    /**
     * Get media gallery content
     *
     * @return \Magento\Framework\Api\Data\ImageContentInterface|null
     * @since 2.0.0
     */
    public function getContent();

    /**
     * Set media gallery content
     *
     * @param \Magento\Framework\Api\Data\ImageContentInterface $content
     * @return $this
     * @since 2.0.0
     */
    public function setContent($content);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(
        \Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryExtensionInterface $extensionAttributes
    );
}
