<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Webapi\Test\Unit\Rest\Response;

use \Magento\Framework\Webapi\Rest\Response\FieldsFilter;

/**
 * Unit test for FieldsFilter
 */
class FieldsFilterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var FieldsFilter SUT
     */
    protected $fieldsFilter;

    /**
     * @var string
     */
    protected $sampleResponseValue;

    /** @var \Magento\Framework\Webapi\Rest\Request|\PHPUnit_Framework_MockObject_MockObject */
    protected $requestMock;

    /**
     * Setup SUT
     */
    protected function setUp()
    {
        $this->requestMock = $this->createMock(\Magento\Framework\Webapi\Rest\Request::class);
        $this->processor = new FieldsFilter($this->requestMock);
        $this->sampleResponseValue = [
            'customer' => [
                    'id' => '1',
                    'website_id' => '0',
                    'created_in' => 'Default Store View',
                    'store_id' => '1',
                    'group_id' => '1',
                    'custom_attributes' => [
                            0 => [
                                    'attribute_code' => 'disable_auto_group_change',
                                    'value' => '0',
                                ],
                        ],
                    'firstname' => 'Jane',
                    'lastname' => 'Doe',
                    'email' => 'jdoe@example.com',
                    'default_billing' => '1',
                    'default_shipping' => '1',
                    'created_at' => '2014-05-27 18:59:43',
                    'dob' => '1983-05-26 00:00:00',
                    'taxvat' => '1212121212',
                    'gender' => '1',
                ],
            'addresses' => [
                    0 => [
                            'firstname' => 'Jane',
                            'lastname' => 'Doe',
                            'street' => [
                                    0 => '7700  Parmer ln',
                                ],
                            'city' => 'Austin',
                            'country_id' => 'US',
                            'region' => [
                                    'region' => 'Texas',
                                    'region_id' => 57,
                                    'region_code' => 'TX',
                                ],
                            'postcode' => '78728',
                            'telephone' => '1111111111',
                            'default_billing' => true,
                            'default_shipping' => true,
                            'id' => '1',
                            'customer_id' => '1',
                        ],
                    1 => [
                            'firstname' => 'Jane',
                            'lastname' => 'Doe',
                            'street' => [
                                    0 => '2211 N First St ',
                                ],
                            'city' => 'San Jose',
                            'country_id' => 'US',
                            'region' => [
                                    'region' => 'California',
                                    'region_id' => 23,
                                    'region_code' => 'CA',
                                ],
                            'postcode' => '98454',
                            'telephone' => '2222222222',
                            'default_billing' => true,
                            'default_shipping' => true,
                            'id' => '2',
                            'customer_id' => '1',
                        ],
                ],
        ];
    }

    public function testFilterNoNesting()
    {
        $expected = ['customer' => $this->sampleResponseValue['customer']];

        $simpleFilter = 'customer';
        $this->requestMock->expects($this->any())->method('getParam')->will($this->returnValue($simpleFilter));
        $filteredResponse = $this->processor->filter($this->sampleResponseValue);

        $this->assertEquals($expected, $filteredResponse);
    }

    public function testFilterSimpleNesting()
    {
        $expected = [
            'customer' => [
                'email' => $this->sampleResponseValue['customer']['email'],
                'id' => $this->sampleResponseValue['customer']['id'],
            ],
        ];

        $simpleFilter = "customer[email,id]";

        $this->requestMock->expects($this->any())->method('getParam')->will($this->returnValue($simpleFilter));
        $filteredResponse = $this->processor->filter($this->sampleResponseValue);

        $this->assertEquals($expected, $filteredResponse);
    }

    public function testFilterMultilevelNesting()
    {
        $expected = [
            'customer' => [
                    'id' => '1',
                    'email' => 'jdoe@example.com',
                ],
            'addresses' => [
                    0 => [
                            'city' => 'Austin',
                            'region' => [
                                    'region' => 'Texas',
                                    'region_code' => 'TX',
                                ],
                            'postcode' => '78728',
                        ],
                    1 => [
                            'city' => 'San Jose',
                            'region' => [
                                    'region' => 'California',
                                    'region_code' => 'CA',
                                ],
                            'postcode' => '98454',
                        ],
                ],
        ];

        $nestedFilter = 'customer[id,email],addresses[city,postcode,region[region_code,region]]';

        $this->requestMock->expects($this->any())->method('getParam')->will($this->returnValue($nestedFilter));
        $filteredResponse = $this->processor->filter($this->sampleResponseValue);

        $this->assertEquals($expected, $filteredResponse);
    }

    public function testNonExistentFieldFilter()
    {
        //TODO : Make sure if this behavior is acceptable
        $expected = [
            'customer' => [
                    'id' => '1',
                    'email' => 'jdoe@example.com',
                ],
            'addresses' => [
                    0 => [
                            //'city' => 'Austin', //City has been substituted with 'invalid' field
                            'region' => [
                                    'region' => 'Texas',
                                    'region_code' => 'TX',
                                ],
                            'postcode' => '78728',
                        ],
                    1 => [
                            //'city' => 'San Jose',
                            'region' => [
                                    'region' => 'California',
                                    'region_code' => 'CA',
                                ],
                            'postcode' => '98454',
                        ],
                ],
        ];

        $nonExistentFieldFilter = 'customer[id,email],addresses[invalid,postcode,region[region_code,region]]';

        $this->requestMock
            ->expects($this->any())
            ->method('getParam')
            ->will($this->returnValue($nonExistentFieldFilter));
        $filteredResponse = $this->processor->filter($this->sampleResponseValue);

        $this->assertEquals($expected, $filteredResponse);
    }

    /**
     * @dataProvider invalidFilterDataProvider
     */
    public function testInvalidFilters($invalidFilter)
    {
        $this->requestMock->expects($this->any())->method('getParam')->will($this->returnValue($invalidFilter));
        $filteredResponse = $this->processor->filter($this->sampleResponseValue);

        $this->assertEmpty($filteredResponse);
    }

    /**
     * Data provider for invalid Filters
     *
     * @return array
     */
    public function invalidFilterDataProvider()
    {
        return [
            ['  '],
            [null],
            ['customer(email)'],
            [' customer[email]'],
            ['-'],
            ['customer[id,email],addresses[city,postcode,region[region_code,region]'] //Missing last parentheses
        ];
    }
}
