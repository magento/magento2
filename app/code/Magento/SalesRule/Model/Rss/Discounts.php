<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
