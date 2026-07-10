<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\SalesRule\Model\Plugin\ResourceModel;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\SalesRule\Model\ResourceModel\Rule as RuleResource;

class Rule
{
    /**
     * Cache key for active salesrule attributes
     */
    public const CACHE_KEY = 'salesrule_active_product_attributes';

    /**
     * Cache tag for salesrule attributes
     */
    public const CACHE_TAG = 'salesrule';

    /**
     * Cache Lifetime for salesrule attributes
     */
    public const CACHE_TTL = 86400;

    /**
     * Temp variable to save attributes
     * @var array
     */
    private $attributes = [];

    /**
     *
     * @param CacheInterface $cache
     * @param SerializerInterface $serializer
     */
    public function __construct(
        private CacheInterface $cache,
        private SerializerInterface $serializer
    ) {
    }

    /**
     * Around plugin for LoadCustomerGroupIds
     *
     * @param \Magento\SalesRule\Model\ResourceModel\Rule $subject
     * @param \Closure $proceed
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return \Magento\Framework\Model\AbstractModel
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @deprecated 100.1.0
     * @see \Magento\SalesRule\Model\ResourceModel\Rule
     */
    public function aroundLoadCustomerGroupIds(
        RuleResource $subject,
        \Closure $proceed,
        \Magento\Framework\Model\AbstractModel $object
    ) {
        return $subject;
    }

    /**
     * Around plugin for LoadWebsiteIds
     *
     * @param RuleResource $subject
     * @param \Closure $proceed
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return \Magento\Framework\Model\AbstractModel
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @deprecated 100.1.0
     * @see \Magento\SalesRule\Model\ResourceModel\Rule
     */
    public function aroundLoadWebsiteIds(
        RuleResource $subject,
        \Closure $proceed,
        \Magento\Framework\Model\AbstractModel $object
    ) {
        return $subject;
    }

    /**
     * Save attribute in a temp object to be used in afterSetActualProductAttributes
     *
     * @param RuleResource $subject
     * @param \Magento\Framework\Model\AbstractModel $object
     * @param mixed $attributes
     * @return null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSetActualProductAttributes(
        RuleResource $subject,
        \Magento\Framework\Model\AbstractModel $object,
        mixed $attributes
    ) {
        $this->attributes = $attributes;
        return null;
    }

    /**
     * Clear cache if new attributes inserted in salesrule_product_attribute table
     *
     * @param RuleResource $subject
     * @param RuleResource $result
     * @return RuleResource
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSetActualProductAttributes(
        RuleResource $subject,
        RuleResource $result
    ): RuleResource {

        $cachedData = $this->cache->load(self::CACHE_KEY);

        if ($cachedData !== false) {
            $activeAttributeCodes = $this->serializer->unserialize($cachedData);
        } else {
            return $result;
        }

        if (array_diff($this->attributes, $activeAttributeCodes)) {
            $this->cache->clean([self::CACHE_TAG]);
        }
        return $result;
    }
}
