<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Api;

/**
 * @api
 */
interface SynonymGroupRepositoryInterface
{
    /**
     * Save synonym group data
     *
     * @param \Magento\Search\Api\Data\SynonymGroupInterface $synonymGroup
     * @param bool $errorOnMergeConflict
     * @return \Magento\Search\Api\Data\SynonymGroupInterface saved attribute set
     */
    public function save(\Magento\Search\Api\Data\SynonymGroupInterface $synonymGroup, $errorOnMergeConflict = false);

    /**
     * Remove given synonym group data
     *
     * @param \Magento\Search\Api\Data\SynonymGroupInterface $synonymGroup
     * @return bool
     */
    public function delete(\Magento\Search\Api\Data\SynonymGroupInterface $synonymGroup);

    /**
     * Return a particular synonym group interface instance based on passed in synonym group id
     *
     * @param int $synonymGroupId
     * @return \Magento\Search\Api\Data\SynonymGroupInterface
     */
    public function get($synonymGroupId);
}
