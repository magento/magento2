<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Api\Data;

/**
 * Bookmark interface
 *
 * @api
 * @since 2.0.0
 */
interface BookmarkInterface extends BookmarkExtensionInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const BOOKMARK_ID      = 'bookmark_id';
    const USER_ID          = 'user_id';
    const BOOKMARKSPACE    = 'namespace';
    const IDENTIFIER       = 'identifier';
    const TITLE            = 'title';
    const CONFIG           = 'config';
    const CREATED_AT       = 'created_at';
    const UPDATED_AT       = 'updated_at';
    const CURRENT          = 'current';
    /**#@-*/

    /**
     * Get ID
     *
     * @return int
     * @since 2.0.0
     */
    public function getId();

    /**
     * Get user id
     *
     * @return int
     * @since 2.0.0
     */
    public function getUserId();

    /**
     * Get identifier
     *
     * @return string
     * @since 2.0.0
     */
    public function getNamespace();

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
     * @return string
     * @since 2.0.0
     */
    public function getTitle();

    /**
     * Get config content
     *
     * @return array
     * @since 2.0.0
     */
    public function getConfig();

    /**
     * Get creation time
     *
     * @return string
     * @since 2.0.0
     */
    public function getCreatedAt();

    /**
     * Get update time
     *
     * @return string
     * @since 2.0.0
     */
    public function getUpdatedAt();

    /**
     * Get user bookmark is current
     *
     * @return bool
     * @since 2.0.0
     */
    public function isCurrent();

    /**
     * Set ID
     *
     * @param int $id
     * @return \Magento\Ui\Api\Data\BookmarkInterface
     * @since 2.0.0
     */
    public function setId($id);

    /**
     * Set user id
     *
     * @param int $userId
     * @return \Magento\Ui\Api\Data\BookmarkInterface
     * @since 2.0.0
     */
    public function setUserId($userId);

    /**
     * Set namespace
     *
     * @param string $namespace
     * @return \Magento\Ui\Api\Data\BookmarkInterface
     * @since 2.0.0
     */
    public function setNamespace($namespace);

    /**
     * Set identifier
     *
     * @param string $identifier
     * @return \Magento\Ui\Api\Data\BookmarkInterface
     * @since 2.0.0
     */
    public function setIdentifier($identifier);

    /**
     * Set title
     *
     * @param string $title
     * @return \Magento\Ui\Api\Data\BookmarkInterface
     * @since 2.0.0
     */
    public function setTitle($title);

    /**
     * Set config content
     *
     * @param string $config
     * @return \Magento\Ui\Api\Data\BookmarkInterface
     * @since 2.0.0
     */
    public function setConfig($config);

    /**
     * Set creation time
     *
     * @param string $createdAt
     * @return \Magento\Ui\Api\Data\BookmarkInterface
     * @since 2.0.0
     */
    public function setCreatedAt($createdAt);

    /**
     * Set update time
     *
     * @param string $updatedAt
     * @return \Magento\Ui\Api\Data\BookmarkInterface
     * @since 2.0.0
     */
    public function setUpdatedAt($updatedAt);

    /**
     * Set bookmark to current
     *
     * @param bool $isCurrent
     * @return \Magento\Ui\Api\Data\BookmarkInterface
     * @since 2.0.0
     */
    public function setCurrent($isCurrent);

    /**
     * Retrieve existing extension attributes object or create a new one
     *
     * @return \Magento\Ui\Api\Data\BookmarkExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object
     *
     * @param \Magento\Ui\Api\Data\BookmarkExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(
        \Magento\Ui\Api\Data\BookmarkExtensionInterface $extensionAttributes
    );
}
