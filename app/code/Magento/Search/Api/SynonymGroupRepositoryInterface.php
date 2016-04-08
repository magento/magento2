<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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
     * @param $errorOnMergeConflict
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
     * Return a paritcular synonym group model based on passed in synonym group id
     *
     * @param $synGroupId
     * @return \Magento\Search\Model\SynonymGroup
     */
    public function get($synGroupId);
}
