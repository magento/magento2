<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Api;

/**
 * @api
 * @since 2.1.0
 */
interface SynonymGroupRepositoryInterface
{
    /**
     * Save synonym group data
     *
     * @param \Magento\Search\Api\Data\SynonymGroupInterface $synonymGroup
     * @param bool $errorOnMergeConflict
     * @return \Magento\Search\Api\Data\SynonymGroupInterface saved attribute set
     * @since 2.1.0
     */
    public function save(\Magento\Search\Api\Data\SynonymGroupInterface $synonymGroup, $errorOnMergeConflict = false);

    /**
     * Remove given synonym group data
     *
     * @param \Magento\Search\Api\Data\SynonymGroupInterface $synonymGroup
     * @return bool
     * @since 2.1.0
     */
    public function delete(\Magento\Search\Api\Data\SynonymGroupInterface $synonymGroup);

    /**
     * Return a paritcular synonym group interface instance based on passed in synonym group id
     *
     * @param int $synonymGroupId
     * @return \Magento\Search\Api\Data\SynonymGroupInterface
     * @since 2.1.0
     */
    public function get($synonymGroupId);
}
