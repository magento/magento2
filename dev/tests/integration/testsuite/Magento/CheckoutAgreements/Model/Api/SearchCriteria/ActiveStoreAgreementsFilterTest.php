<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CheckoutAgreements\Model\Api\SearchCriteria;

class ActiveStoreAgreementsFilterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Magento\CheckoutAgreements\Model\Api\SearchCriteria\ActiveStoreAgreementsFilter
     */
    private $model;

    public function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->model = $this->objectManager->create(
            \Magento\CheckoutAgreements\Model\Api\SearchCriteria\ActiveStoreAgreementsFilter::class
        );
    }

    public function testBuildSearchCriteria()
    {
        $expected = [
            'filter_groups' => [
                [
                    'filters' => [
                        [
                            'field' => 'store_id',
                            'condition_type' => 'eq',
                            'value' => 1,
                        ]
                    ]
                ],
                [
                    'filters' => [
                        [
                            'field' => 'is_active',
                            'condition_type' => 'eq',
                            'value' => 1,
                        ]
                    ]
                ],
            ]
        ];
        $searchCriteria = $this->model->buildSearchCriteria();
        $this->assertEquals($expected, $searchCriteria->__toArray());
    }
}
