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
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Webapi\Controller\Rest\Response;

/**
 * Unit test for PartialResponseProcessor
 */
class PartialResponseProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PartialResponseProcessor SUT
     */
    protected $processor;

    /**
     * @var string
     */
    protected $sampleResponseValue;

    /** @var \Magento\Webapi\Controller\Rest\Request|\PHPUnit_Framework_MockObject_MockObject */
    protected $requestMock;

    /**
     * Setup SUT
     */
    public function setUp()
    {
        $this->requestMock = $this->getMock('Magento\Webapi\Controller\Rest\Request', [], [], '', false);
        $this->processor = new PartialResponseProcessor($this->requestMock);
        $this->sampleResponseValue = array(
            'customer' =>
                array(
                    'id' => '1',
                    'website_id' => '0',
                    'created_in' => 'Default Store View',
                    'store_id' => '1',
                    'group_id' => '1',
                    'custom_attributes' =>
                        array(
                            0 =>
                                array(
                                    'attribute_code' => 'disable_auto_group_change',
                                    'value' => '0',
                                ),
                        ),
                    'firstname' => 'Jane',
                    'lastname' => 'Doe',
                    'email' => 'jdoe@ebay.com',
                    'default_billing' => '1',
                    'default_shipping' => '1',
                    'created_at' => '2014-05-27 18:59:43',
                    'dob' => '1983-05-26 00:00:00',
                    'taxvat' => '1212121212',
                    'gender' => '1',
                ),
            'addresses' =>
                array(
                    0 =>
                        array(
                            'firstname' => 'Jane',
                            'lastname' => 'Doe',
                            'street' =>
                                array(
                                    0 => '7700  Parmer ln',
                                ),
                            'city' => 'Austin',
                            'country_id' => 'US',
                            'region' =>
                                array(
                                    'region' => 'Texas',
                                    'region_id' => 57,
                                    'region_code' => 'TX',
                                ),
                            'postcode' => '78728',
                            'telephone' => '1111111111',
                            'default_billing' => true,
                            'default_shipping' => true,
                            'id' => '1',
                            'customer_id' => '1',
                        ),
                    1 =>
                        array(
                            'firstname' => 'Jane',
                            'lastname' => 'Doe',
                            'street' =>
                                array(
                                    0 => '2211 N First St ',
                                ),
                            'city' => 'San Jose',
                            'country_id' => 'US',
                            'region' =>
                                array(
                                    'region' => 'California',
                                    'region_id' => 23,
                                    'region_code' => 'CA',
                                ),
                            'postcode' => '98454',
                            'telephone' => '2222222222',
                            'default_billing' => true,
                            'default_shipping' => true,
                            'id' => '2',
                            'customer_id' => '1',
                        ),
                ),
        );
    }

    public function testFilterNoNesting()
    {
        $expected = array('customer' => $this->sampleResponseValue['customer']);

        $simpleFilter = 'customer';
        $this->requestMock->expects($this->any())->method('getParam')->will($this->returnValue($simpleFilter));
        $filteredResponse = $this->processor->filter($this->sampleResponseValue);

        $this->assertEquals($expected, $filteredResponse);
    }

    public function testFilterSimpleNesting()
    {
        $expected = array(
            'customer' => [
                'email' => $this->sampleResponseValue['customer']['email'],
                'id' => $this->sampleResponseValue['customer']['id']
            ]
        );

        $simpleFilter = "customer[email,id]";

        $this->requestMock->expects($this->any())->method('getParam')->will($this->returnValue($simpleFilter));
        $filteredResponse = $this->processor->filter($this->sampleResponseValue);

        $this->assertEquals($expected, $filteredResponse);
    }

    public function testFilterMultilevelNesting()
    {
        $expected = array(
            'customer' =>
                array(
                    'id' => '1',
                    'email' => 'jdoe@ebay.com',
                ),
            'addresses' =>
                array(
                    0 =>
                        array(
                            'city' => 'Austin',
                            'region' =>
                                array(
                                    'region' => 'Texas',
                                    'region_code' => 'TX',
                                ),
                            'postcode' => '78728',
                        ),
                    1 =>
                        array(
                            'city' => 'San Jose',
                            'region' =>
                                array(
                                    'region' => 'California',
                                    'region_code' => 'CA',
                                ),
                            'postcode' => '98454',
                        ),
                ),
        );

        $nestedFilter = 'customer[id,email],addresses[city,postcode,region[region_code,region]]';

        $this->requestMock->expects($this->any())->method('getParam')->will($this->returnValue($nestedFilter));
        $filteredResponse = $this->processor->filter($this->sampleResponseValue);

        $this->assertEquals($expected, $filteredResponse);
    }

    public function testNonExistentFieldFilter()
    {
        //TODO : Make sure if this behavior is acceptable
        $expected = array(
            'customer' =>
                array(
                    'id' => '1',
                    'email' => 'jdoe@ebay.com',
                ),
            'addresses' =>
                array(
                    0 =>
                        array(
                            //'city' => 'Austin', //City has been substituted with 'invalid' field
                            'region' =>
                                array(
                                    'region' => 'Texas',
                                    'region_code' => 'TX',
                                ),
                            'postcode' => '78728',
                        ),
                    1 =>
                        array(
                            //'city' => 'San Jose',
                            'region' =>
                                array(
                                    'region' => 'California',
                                    'region_code' => 'CA',
                                ),
                            'postcode' => '98454',
                        ),
                ),
        );

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
