<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model\Rss;

use DateTime;
use Magento\Framework\Stdlib\DateTime as FrameworkDateTime;
use Magento\SalesRule\Model\ResourceModel\Rule\Collection as RuleCollection;
use Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory as RuleCollectionFactory;

/**
 * Class Discounts
 * @package Magento\SalesRule\Model\Rss
 */
class Discounts
{
    /**
     * @param FrameworkDateTime $dateTime
     * @param RuleCollectionFactory $collectionFactory
     */
    public function __construct(
        protected readonly FrameworkDateTime $dateTime,
        protected readonly RuleCollectionFactory $collectionFactory
    ) {
    }

    /**
     * @param int $websiteId
     * @param int $customerGroupId
     * @return RuleCollection
     */
    public function getDiscountCollection($websiteId, $customerGroupId)
    {
        /** @var RuleCollection $collection */
        $collection = $this->collectionFactory->create();
        $collection->addWebsiteGroupDateFilter(
            $websiteId,
            $customerGroupId,
            (new DateTime())->format(FrameworkDateTime::DATETIME_PHP_FORMAT)
        )
            ->addFieldToFilter('is_rss', 1)
            ->setOrder('from_date', 'desc');
        $collection->load();
        return $collection;
    }
}
