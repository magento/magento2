<?php
/**
 * Created by PhpStorm.
 * User: aohorodnyk
 * Date: 14.07.2015
 * Time: 9:47
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
