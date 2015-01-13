<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model\Rss;

/**
 * Class Discounts
 * @package Magento\SalesRule\Model\Rss
 */
class Discounts
{
    /**
     * @var \Magento\SalesRule\Model\Resource\Rule\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Magento\SalesRule\Model\Resource\Rule\CollectionFactory $collectionFactory
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\SalesRule\Model\Resource\Rule\CollectionFactory $collectionFactory
    ) {
        $this->dateTime = $dateTime;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @param int $websiteId
     * @param int $customerGroupId
     * @return \Magento\SalesRule\Model\Resource\Rule\Collection
     */
    public function getDiscountCollection($websiteId, $customerGroupId)
    {
        /** @var $collection \Magento\SalesRule\Model\Resource\Rule\Collection */
        $collection = $this->collectionFactory->create();
        $collection->addWebsiteGroupDateFilter($websiteId, $customerGroupId, $this->dateTime->now(true))
            ->addFieldToFilter('is_rss', 1)
            ->setOrder('from_date', 'desc');
        $collection->load();
        return $collection;
    }
}
