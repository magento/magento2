<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestModuleJoinDirectives\Api;

/**
 * Interface TestRepositoryInterface
 */
interface TestRepositoryInterface
{
    /**
     * Get list of quotes
     *
     * @param \Magento\Framework\Api\SearchCriteria $searchCriteria
     * @return \Magento\Quote\Api\Data\CartSearchResultsInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteria $searchCriteria);
}
