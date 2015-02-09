<?php
/**
 * Product Media Attribute
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Api\Data;

interface ProductAttributeMediaGalleryEntryInterface
{
    const ID = 'id';
    const LABEL = 'label';
    const POSITION = 'position';
    const DISABLED = 'is_disabled';
    const TYPES = 'types';
    const FILE = 'file';

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
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsDisabled();

    /**
     * Set whether gallery entry is hidden from product page
     *
     * @param bool $isDisabled
     * @return $this
     */
    public function setIsDisabled($isDisabled);

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
}
