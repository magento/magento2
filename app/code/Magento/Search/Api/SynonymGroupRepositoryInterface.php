<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
     * @return \Magento\Search\Api\Data\SynonymGroupInterface saved attribute set
     */
    public function save(\Magento\Search\Api\Data\SynonymGroupInterface $synonymGroup);

    /**
     * Remove given synonym group data
     *
     * @param \Magento\Search\Api\Data\SynonymGroupInterface $synonymGroup
     * @return bool
     */
    public function delete(\Magento\Search\Api\Data\SynonymGroupInterface $synonymGroup);
}
