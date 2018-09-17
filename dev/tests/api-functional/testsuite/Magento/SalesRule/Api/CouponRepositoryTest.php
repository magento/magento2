<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Api;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;

class CouponRepositoryTest extends WebapiAbstract
{
    const SERVICE_NAME = 'salesRuleCouponRepositoryV1';
    const RESOURCE_PATH = '/V1/coupons';
    const SERVICE_VERSION = "V1";

    protected function getCouponData()
    {
        $data = [
                'rule_id' => '1',
                'code' => 'mycouponcode1',
                'times_used' => 0,
                'is_primary' => null,
                'created_at' => '2015-07-20 00:00:00',
                'type' => 1,
        ];
        return $data;
    }

    /**
     * @magentoApiDataFixture Magento/SalesRule/_files/rules_autogeneration.php
     */
    public function testCrud()
    {
        //test create
        $inputData = $this->getCouponData();

        /** @var $registry \Magento\Framework\Registry */
        $registry = Bootstrap::getObjectManager()->get('Magento\Framework\Registry');
        /** @var $salesRule \Magento\SalesRule\Model\Rule */
        $salesRule = $registry->registry('_fixture/Magento_SalesRule_Api_RuleRepository');
        $ruleId = $salesRule->getRuleId();

        $inputData['rule_id'] = $ruleId;
        $result = $this->createCoupon($inputData);

        $this->assertArrayHasKey('coupon_id', $result);
        $couponId = $result['coupon_id'];
        unset($result['coupon_id']);
        $result = $this->verifySalesRuleInfluence($result);
        $this->assertEquals($inputData, $result);

        //test getList
        $result = $this->verifyGetList($couponId);
        $inputData = array_merge(['coupon_id' => $couponId], $inputData);
        $result = $this->verifySalesRuleInfluence($result);
        $this->assertEquals($inputData, $result);

        //test update
        $inputData['times_used'] = 2;
        $inputData['code'] = 'mycouponcode2';
        $result = $this->updateCoupon($couponId, $inputData);
        $result = $this->verifySalesRuleInfluence($result);
        $this->assertEquals($inputData, $result);

        //test get
        $result = $this->getCoupon($couponId);
        $result = $this->verifySalesRuleInfluence($result);
        $this->assertEquals($inputData, $result);

        //test delete
        $this->assertEquals(true, $this->deleteCoupon($couponId));
    }

    // verify (and remove) the fields that are set by the Sales Rule
    protected function verifySalesRuleInfluence($result)
    {
        //optional
        unset($result['expiration_date']);

        $this->assertArrayHasKey('usage_per_customer', $result);
        unset($result['usage_per_customer']);

        $this->assertArrayHasKey('usage_limit', $result);
        unset($result['usage_limit']);

        return $result;
    }

    public function verifyGetList($couponId)
    {
        $searchCriteria = [
            'searchCriteria' => [
                'filter_groups' => [
                    [
                        'filters' => [
                            [
                                'field' => 'coupon_id',
                                'value' => $couponId,
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
        $this->assertEquals($couponId, $response['items'][0]['coupon_id']);

        return $response['items'][0];
    }

    protected function createCoupon($coupon)
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
        $requestData = ['coupon' => $coupon];
        return $this->_webApiCall($serviceInfo, $requestData);
    }

    protected function deleteCoupon($couponId)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $couponId,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_DELETE,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'DeleteById',
            ],
        ];

        return $this->_webApiCall($serviceInfo, ['coupon_id' => $couponId]);
    }

    protected function updateCoupon($couponId, $data)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $couponId,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Save',
            ],
        ];

        $data['coupon_id'] = $couponId;
        return $this->_webApiCall($serviceInfo, ['coupon_id' => $couponId, 'coupon' => $data]);
    }

    /**
     * Retrieve an existing coupon
     *
     * @param int $couponId
     * @return \Magento\SalesRule\Api\Data\CouponInterface
     */
    protected function getCoupon($couponId)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $couponId,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'GetById',
            ],
        ];

        return $this->_webApiCall($serviceInfo, ['coupon_id' => $couponId]);
    }
}
