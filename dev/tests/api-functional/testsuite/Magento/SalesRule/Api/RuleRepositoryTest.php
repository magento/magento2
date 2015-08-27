<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Api;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;

class RuleRepositoryTest extends WebapiAbstract
{
    const SERVICE_NAME = 'salesRuleRuleRepositoryV1';
    const RESOURCE_PATH = '/V1/salesRules';
    const SERVICE_VERSION = "V1";

    protected function getSalesRuleData()
    {
        $data = [
            'name' => '40% Off on Large Orders',
            'store_labels' => [
                [
                    'store_id' => 0,
                    'store_label' => 'TestRule_Label',
                ],
                [
                    'store_id' => 1,
                    'store_label' => 'TestRule_Label_default',
                ],
            ],
            'description' => 'Test sales rule',
            'website_ids' => [1],
            'customer_group_ids' => [0, 1, 3],
            'from_date' => '2015-06-09',
            'to_date' => '2018-06-09',
            'uses_per_customer' => 2,
            'is_active' => 1,
            'condition' => [
                'condition_type' => 'Magento\SalesRule\Model\Rule\Condition\Combine',
                'conditions' => [
                    [
                        'condition_type' => 'Magento\SalesRule\Model\Rule\Condition\Address',
                        'operator' => '>',
                        'attribute_name' => 'base_subtotal',
                        'value' => 800
                    ]
                ],
                'aggregator_type' => 'all',
                'operator' => null,
                'value' => null,
            ],
            'action_condition' => [
                'condition_type' =>  'Magento\SalesRule\Model\Rule\Condition\Product\Combine',
                "conditions" => [
                    [
                        'condition_type' => 'Magento\SalesRule\Model\Rule\Condition\Product',
                        'operator' => '==',
                        'attribute_name' => 'attribute_set_id',
                        'value' => '4',
                    ]
                ],
                'aggregator_type' => 'all',
                'operator' => null,
                'value' => null,
            ],
            'stop_rules_processing' => true,
            'is_advanced' => true,
            'sort_order' => 2,
            'simple_action' => 'cart_fixed',
            'discount_amount' => 40,
            'discount_qty' => 2,
            'discount_step' => 0,
            'apply_to_shipping' => false,
            'times_used' => 1,
            'is_rss' => true,
            'coupon_type' => \Magento\SalesRule\Api\Data\RuleInterface::COUPON_TYPE_SPECIFIC_COUPON,
            'use_auto_generation' => false,
            'uses_per_coupon' => 0,
            'simple_free_shipping' => 0,
        ];
        return $data;
    }

    public function testCrud()
    {
        //test create
        $inputData = $this->getSalesRuleData();
        $result = $this->createRule($inputData);
        $ruleId = $result['rule_id'];
        $this->assertArrayHasKey('rule_id', $result);
        $this->assertEquals($ruleId, $result['rule_id']);
        unset($result['rule_id']);
        $this->assertEquals($inputData, $result);

        //test getList
        $result = $this->verifyGetList($ruleId);
        unset($result['rule_id']);
        $this->assertEquals($inputData, $result);

        //test update
        $inputData['times_used'] = 2;
        $inputData['customer_group_ids'] = [0, 1, 3];
        $inputData['discount_amount'] = 30;
        $result = $this->updateRule($ruleId, $inputData);
        unset($result['rule_id']);
        $this->assertEquals($inputData, $result);

        //test get
        $result = $this->getRule($ruleId);
        unset($result['rule_id']);
        $this->assertEquals($inputData, $result);

        //test delete
        $this->assertEquals(true, $this->deleteRule($ruleId));
    }

    public function verifyGetList($ruleId)
    {
        $searchCriteria = [
            'searchCriteria' => [
                'filter_groups' => [
                    [
                        'filters' => [
                            [
                                'field' => 'rule_id',
                                'value' => $ruleId,
                                'condition_type' => 'eq',
                            ],
                        ],
                    ],
                ],
                'current_page' => 1,
                'page_size' => 2,
            ],
        ];

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/search' . '?' . http_build_query($searchCriteria),
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'GetList',
            ],
        ];

        $response = $this->_webApiCall($serviceInfo, $searchCriteria);

        $this->assertArrayHasKey('search_criteria', $response);
        $this->assertArrayHasKey('total_count', $response);
        $this->assertArrayHasKey('items', $response);

        $this->assertEquals($searchCriteria['searchCriteria'], $response['search_criteria']);
        $this->assertTrue($response['total_count'] > 0);
        $this->assertTrue(count($response['items']) > 0);

        $this->assertNotNull($response['items'][0]['rule_id']);
        $this->assertEquals($ruleId, $response['items'][0]['rule_id']);

        return $response['items'][0];
    }

    /**
     * Create Sales rule
     *
     * @param $rule
     * @return array
     */
    protected function createRule($rule)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Save',
            ],
        ];
        $requestData = ['rule' => $rule];
        return $this->_webApiCall($serviceInfo, $requestData);
    }

    /**
     * @param int $id
     * @return bool
     * @throws \Exception
     */
    protected function deleteRule($id)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $id,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_DELETE,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'DeleteById',
            ],
        ];

        return $this->_webApiCall($serviceInfo, ['rule_id' => $id]);
    }

    protected function updateRule($id, $data)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $id,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Save',
            ],
        ];

        $data['rule_id'] = $id;
        return $this->_webApiCall($serviceInfo, ['rule_id' => $id, 'rule' => $data]);
    }

    protected function getRule($id)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $id,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'GetById',
            ],
        ];

        return $this->_webApiCall($serviceInfo, ['rule_id' => $id]);
    }
}
