<?php
/**
 * Product Media Attribute
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * @api
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
     */
    public function getId();

    /**
     * Set gallery entry ID
     *
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * Get media type
     *
     * @return string
     */
    public function getMediaType();

    /**
     * Set media type
     *
     * @param string $mediaType
     * @return $this
     */
    public function setMediaType($mediaType);

    /**
     * Retrieve gallery entry alternative text
     *
     * @return string
     */
    public function getLabel();

    /**
     * Set gallery entry alternative text
     *
     * @param string $label
     * @return $this
     */
    public function setLabel($label);

    /**
     * Retrieve gallery entry position (sort order)
     *
     * @return int
     */
    public function getPosition();
    
    /**
     * Set gallery entry position (sort order)
     *
     * @param int $position
     * @return $this
     */
    public function setPosition($position);

    /**
     * Check if gallery entry is hidden from product page
     *
     * @return bool
     */
    public function isDisabled();

    /**
     * Set whether gallery entry is hidden from product page
     *
     * @param bool $disabled
     * @return $this
     */
    public function setDisabled($disabled);

    /**
     * Retrieve gallery entry image types (thumbnail, image, small_image etc)
     *
     * @return string[]
     */
    public function getTypes();

    /**
     * Set gallery entry image types (thumbnail, image, small_image etc)
     *
     * @param string[] $types
     * @return $this
     */
    public function setTypes(array $types = null);

    /**
     * Get file path
     *
     * @return string|null
     */
    public function getFile();

    /**
     * Set file path
     *
     * @param string $file
     * @return $this
     */
    public function setFile($file);
    
    /**
     * Get media gallery content
     *
     * @return \Magento\Framework\Api\Data\ImageContentInterface|null
     */
    public function getContent();
    
    /**
     * Set media gallery content
     *
     * @param $content \Magento\Framework\Api\Data\ImageContentInterface
     * @return $this 
     */
    public function setContent($content);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryExtensionInterface $extensionAttributes
    );
}
