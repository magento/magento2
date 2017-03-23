<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Api\Data;

/**
 * Bookmark interface.
 * @api
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
     */
    public function getId();

    /**
     * Get user id
     *
     * @return int
     */
    public function getUserId();

    /**
     * Get identifier
     *
     * @return string
     */
    public function getNamespace();

    /**
     * Get identifier
     *
     * @return string
     */
    public function getIdentifier();

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle();

    /**
     * Get config content
     *
     * @return array
     */
    public function getConfig();

    /**
     * Get creation time
     *
     * @return string
     */
    public function getCreatedAt();

    /**
     * Get update time
     *
     * @return string
     */
    public function getUpdatedAt();

    /**
     * Get user bookmark is current
     *
     * @return bool
     */
    public function isCurrent();

    /**
     * Set ID
     *
     * @param int $id
     * @return \Magento\Ui\Api\Data\BookmarkInterface
     */
    public function setId($id);

    /**
     * Set user id
     *
     * @param int $userId
     * @return \Magento\Ui\Api\Data\BookmarkInterface
     */
    public function setUserId($userId);

    /**
     * Set namespace
     *
     * @param string $namespace
     * @return \Magento\Ui\Api\Data\BookmarkInterface
     */
    public function setNamespace($namespace);

    /**
     * Set identifier
     *
     * @param string $identifier
     * @return \Magento\Ui\Api\Data\BookmarkInterface
     */
    public function setIdentifier($identifier);

    /**
     * Set title
     *
     * @param string $title
     * @return \Magento\Ui\Api\Data\BookmarkInterface
     */
    public function setTitle($title);

    /**
     * Set config content
     *
     * @param string $config
     * @return \Magento\Ui\Api\Data\BookmarkInterface
     */
    public function setConfig($config);

    /**
     * Set creation time
     *
     * @param string $createdAt
     * @return \Magento\Ui\Api\Data\BookmarkInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * Set update time
     *
     * @param string $updatedAt
     * @return \Magento\Ui\Api\Data\BookmarkInterface
     */
    public function setUpdatedAt($updatedAt);

    /**
     * Set bookmark to current
     *
     * @param bool $isCurrent
     * @return \Magento\Ui\Api\Data\BookmarkInterface
     */
    public function setCurrent($isCurrent);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Ui\Api\Data\BookmarkExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Ui\Api\Data\BookmarkExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\Ui\Api\Data\BookmarkExtensionInterface $extensionAttributes
    );
}
