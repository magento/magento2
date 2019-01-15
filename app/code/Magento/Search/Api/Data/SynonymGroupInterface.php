<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Api\Data;

/**
 * @api
 * @since 100.1.0
 */
interface SynonymGroupInterface
{
    /**
     * Gets group id
     *
     * @return int
     * @since 100.1.0
     */
    public function getGroupId();

    /**
     * Sets group id
     *
     * @param int $groupId
     * @return $this
     * @since 100.1.0
     */
    public function setGroupId($groupId);

    /**
     * Gets synonym group
     *
     * @return string
     * @since 100.1.0
     */
    public function getSynonymGroup();

    /**
     * Sets synonym group
     *
     * @param string $synonymGroup
     * @return $this
     * @since 100.1.0
     */
    public function setSynonymGroup($synonymGroup);

    /**
     * Gets store id
     *
     * @return int
     * @since 100.1.0
     */
    public function getStoreId();

    /**
     * Sets store id
     *
     * @param int $id
     * @return $this
     * @since 100.1.0
     */
    public function setStoreId($id);

    /**
     * Gets website id
     *
     * @return int
     * @since 100.1.0
     */
    public function getWebsiteId();

    /**
     * Sets website id
     *
     * @param int $id
     * @return $this
     * @since 100.1.0
     */
    public function setWebsiteId($id);
}
