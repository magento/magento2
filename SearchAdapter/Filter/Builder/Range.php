<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\SearchAdapter\Filter\Builder;

use Magento\Framework\Search\Request\Filter\Range as RangeFilterRequest;
use Magento\Framework\Search\Request\FilterInterface as RequestFilterInterface;
use Magento\Elasticsearch\SearchAdapter\FieldMapperInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Model\Session as CustomerSession;

class Range implements FilterInterface
{
    /**
     * @var FieldMapperInterface
     */
    protected $fieldMapper;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @param FieldMapperInterface $fieldMapper
     * @param StoreManagerInterface $storeManager
     * @param CustomerSession $customerSession
     */
    public function __construct(
        FieldMapperInterface $fieldMapper,
        StoreManagerInterface $storeManager,
        CustomerSession $customerSession
    ) {
        $this->fieldMapper = $fieldMapper;
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
    }

    /**
     * @param RequestFilterInterface|RangeFilterRequest $filter
     * @return array
     */
    public function buildFilter(RequestFilterInterface $filter)
    {
        $filterQuery = $from = $to = [];
        $fieldName = $this->fieldMapper->getFieldName($filter->getField());
        if ($fieldName == 'price') {
            $customerGroupId = $this->customerSession->getCustomerGroupId();
            $websiteId = $this->storeManager->getStore()->getWebsiteId();
            if ($filter->getFrom()) {
                $from = ['gte' => $filter->getFrom()];
            }
            if ($filter->getTo()) {
                $to = ['lte' => $filter->getTo()];
            }
            $filterQuery = [
                'nested' => [
                    'path' => 'price',
                    'filter' => [
                        'bool' => [
                            'must' => [
                                [
                                    'term' => [
                                        'price.customer_group_id' => $customerGroupId,
                                    ],
                                ],
                                [
                                    'term' => [
                                        'price.website_id' => $websiteId,
                                    ],
                                ],
                                [
                                    'range' => [
                                        'price.price' => array_merge($from, $to),
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ];
        } else {
            if ($filter->getFrom()) {
                $filterQuery['range'][$fieldName]['gte'] = $filter->getFrom();
            }
            if ($filter->getTo()) {
                $filterQuery['range'][$fieldName]['lte'] = $filter->getTo();
            }
        }
        return [$filterQuery];
    }
}
