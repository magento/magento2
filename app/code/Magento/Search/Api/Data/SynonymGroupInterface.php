<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Api\Data;

/**
 * @api
 */
interface SynonymGroupInterface
{
    /**
     * Gets group id
     *
     * @return int
     */
    public function getGroupId();

    /**
     * Sets group id
     *
     * @param int $groupId
     * @return $this
     */
    public function setGroupId($groupId);

    /**
     * Gets synonym group
     *
     * @return string
     */
    public function getSynonymGroup();

    /**
     * Sets synonym group
     *
     * @param string $synonymGroup
     * @return $this
     */
    public function setSynonymGroup($synonymGroup);

    /**
     * Gets store id
     *
     * @return int
     */
    public function getStoreId();

    /**
     * Sets store id
     *
     * @param int $id
     * @return $this
     */
    public function setStoreId($id);

    /**
     * Gets website id
     *
     * @return int
     */
    public function getWebsiteId();

    /**
     * Sets website id
     *
     * @param int $id
     * @return $this
     */
    public function setWebsiteId($id);
}
