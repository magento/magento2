<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Api\Data;

/**
 * CMS block interface.
 * @api
 * @since 2.0.0
 */
interface BlockInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const BLOCK_ID      = 'block_id';
    const IDENTIFIER    = 'identifier';
    const TITLE         = 'title';
    const CONTENT       = 'content';
    const CREATION_TIME = 'creation_time';
    const UPDATE_TIME   = 'update_time';
    const IS_ACTIVE     = 'is_active';
    /**#@-*/

    /**
     * Get ID
     *
     * @return int|null
     * @since 2.0.0
     */
    public function getId();

    /**
     * Get identifier
     *
     * @return string
     * @since 2.0.0
     */
    public function getIdentifier();

    /**
     * Get title
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getTitle();

    /**
     * Get content
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getContent();

    /**
     * Get creation time
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getCreationTime();

    /**
     * Get update time
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getUpdateTime();

    /**
     * Is active
     *
     * @return bool|null
     * @since 2.0.0
     */
    public function isActive();

    /**
     * Set ID
     *
     * @param int $id
     * @return BlockInterface
     * @since 2.0.0
     */
    public function setId($id);

    /**
     * Set identifier
     *
     * @param string $identifier
     * @return BlockInterface
     * @since 2.0.0
     */
    public function setIdentifier($identifier);

    /**
     * Set title
     *
     * @param string $title
     * @return BlockInterface
     * @since 2.0.0
     */
    public function setTitle($title);

    /**
     * Set content
     *
     * @param string $content
     * @return BlockInterface
     * @since 2.0.0
     */
    public function setContent($content);

    /**
     * Set creation time
     *
     * @param string $creationTime
     * @return BlockInterface
     * @since 2.0.0
     */
    public function setCreationTime($creationTime);

    /**
     * Set update time
     *
     * @param string $updateTime
     * @return BlockInterface
     * @since 2.0.0
     */
    public function setUpdateTime($updateTime);

    /**
     * Set is active
     *
     * @param bool|int $isActive
     * @return BlockInterface
     * @since 2.0.0
     */
    public function setIsActive($isActive);
}
