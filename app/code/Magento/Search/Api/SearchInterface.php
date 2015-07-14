<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Api;


interface SearchInterface
{
    /**
     * @param \Magento\Framework\Api\Search\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Framework\Api\Search\SearchResultInterface
     */
    public function search(\Magento\Framework\Api\Search\SearchCriteriaInterface $searchCriteria);
}
