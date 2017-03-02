<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
     * @var \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory $collectionFactory
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory $collectionFactory
    ) {
        $this->dateTime = $dateTime;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @param int $websiteId
     * @param int $customerGroupId
     * @return \Magento\SalesRule\Model\ResourceModel\Rule\Collection
     */
    public function getDiscountCollection($websiteId, $customerGroupId)
    {
        /** @var $collection \Magento\SalesRule\Model\ResourceModel\Rule\Collection */
        $collection = $this->collectionFactory->create();
        $collection->addWebsiteGroupDateFilter(
            $websiteId,
            $customerGroupId,
            (new \DateTime())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT)
        )
            ->addFieldToFilter('is_rss', 1)
            ->setOrder('from_date', 'desc');
        $collection->load();
        return $collection;
    }
}
