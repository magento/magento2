<?php
/**
 * Test API data helper.
 *
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Api_Helper_DataTest extends PHPUnit_Framework_TestCase
{
    /** @var Mage_Api_Helper_Data */
    protected $_helper;

    /** @var PHPUnit_Framework_MockObject_MockObject */
    protected $_requestMock;

    public function setUp()
    {
        $this->_requestMock = $this->getMock('Mage_Core_Controller_Request_Http', array('getPathInfo'));

        /** @var $contextMock Mage_Core_Helper_Context */
        $contextMock = $this->getMockBuilder('Mage_Core_Helper_Context')->disableOriginalConstructor()->getMock();
        $this->_helper = new Mage_Api_Helper_Data($contextMock, $this->_requestMock);
    }

    /**
     * Check if it is possible to use several filters by the same field. Non WS-I compatible mode.
     *
     * @dataProvider providerParseFiltersMultipleConditionsOnSameField
     */
    public function testParseFiltersMultipleConditionsOnSameField($input, $expectedOutput)
    {
        $this->_requestMock->expects($this->any())->method('getPathInfo')->will($this->returnValue('api/soap'));
        $output = $this->_helper->parseFilters($input);
        $this->assertEquals(
            $expectedOutput,
            $output,
            "Multiple filters by the same field are not processed correctly in non WS-I mode."
        );
    }

    /**
     * Data provider for both WS-I and non WS-I modes.
     *
     * @return array
     */
    public static function providerParseFiltersMultipleConditionsOnSameField()
    {
        return array(
            array(
                (object)array(
                    'complex_filter' => array(
                        (object)array(
                            'key' => 'created_at',
                            'value' => (object)array('key' => 'from', 'value' => '2000-01-01 00:00:00')
                        ),
                        (object)array(
                            'key' => 'created_at',
                            'value' => (object)array('key' => 'to', 'value' => '2001-01-01 00:00:00')
                        ),
                        (object)array(
                            'key' => 'updated_at',
                            'value' => (object)array('key' => 'gt', 'value' => '2002-01-01 00:00:00')
                        ),
                    )
                ),
                array(
                    'created_at' => array('from' => '2000-01-01 00:00:00', 'to' => '2001-01-01 00:00:00'),
                    'updated_at' => array('gt' => '2002-01-01 00:00:00')
                )
            )
        );
    }

    /**
     * Check if it is possible to use several filters by the same field. WS-I compatible mode.
     *
     * @dataProvider providerParseFiltersMultipleConditionsOnSameField
     */
    public function testParseFiltersMultipleConditionsOnSameFieldWsi($input, $expectedOutput)
    {
        $this->_requestMock->expects($this->any())->method('getPathInfo')->will($this->returnValue('api/soap_wsi'));
        $output = $this->_helper->parseFilters($input);
        $this->assertEquals(
            $expectedOutput,
            $output,
            "Multiple filters by the same field are not processed correctly in WS-I mode."
        );
    }

    /**
     * Test simple filter WS-I unpacking.
     *
     * @dataProvider providerWsiArrayUnpackerWithFilter
     */
    public function testWsiArrayUnpackerWithFilter($rawWsiData, $expectedUnpackedData)
    {
        /** Execute process data with tested method by reference. */
        $this->_helper->wsiArrayUnpacker($rawWsiData);
        $unpackedData = $rawWsiData;

        $this->assertEquals(
            $unpackedData,
            $expectedUnpackedData,
            'Simple filters were formatted incorrectly.'
        );
    }

    public static function providerWsiArrayUnpackerWithFilter()
    {
        return array(
            /** Case with several simple filters applied */
            array(
                (object)array(
                    'sessionId' => 'session',
                    'filters' => (object)array(
                        'filter' => (object)array(
                            'complexObjectArray' => array(
                                (object)array('key' => 'order_id', 'value' => 1),
                                (object)array('key' => 'status', 'value' => 2)
                            )
                        )
                    )
                ),
                (object)array(
                    'sessionId' => 'session',
                    'filters' => (object)array(
                        'filter' => array(
                            'order_id' => 1,
                            'status' => 2
                        )
                    )
                )
            ),
            /** Case with single simple filter applied */
            array(
                (object)array(
                    'sessionId' => 'session',
                    'filters' => (object)array(
                        'filter' => (object)array(
                            'complexObjectArray' => (object)array('key' => 'order_id', 'value' => 1)
                        )
                    )
                ),
                (object)array(
                    'sessionId' => 'session',
                    'filters' => (object)array(
                        'filter' => array(
                            'order_id' => 1
                        )
                    )
                )
            )
        );
    }

    /**
     * Test complex filter WS-I unpacking.
     */
    public function testWsiArrayUnpackerWithComplexFilter()
    {
        $rawWsiData = (object)array(
            'sessionId' => 'session',
            'filters' => (object)array(
                'complex_filter' => (object)array(
                    'complexObjectArray' => array(
                        (object)array('key' => 'order_id', 'value' => (object)array('key' => 'gt', 'value' => 1)),
                        (object)array('key' => 'order_id', 'value' => (object)array('key' => 'lt', 'value' => 5)),
                        (object)array('key' => 'status', 'value' => (object)array('key' => 'eq', 'value' => 2))
                    )
                )
            )
        );
        $expectedUnpackedData = (object)array(
            'sessionId' => 'session',
            'filters' => (object)array(
                'complex_filter' => array(
                    (object)array('key' => 'order_id', 'value' => (object)array('key' => 'gt', 'value' => 1)),
                    (object)array('key' => 'order_id', 'value' => (object)array('key' => 'lt', 'value' => 5)),
                    (object)array('key' => 'status', 'value' => (object)array('key' => 'eq', 'value' => 2)),
                )
            )
        );

        /** Execute process data with tested method by reference. */
        $this->_helper->wsiArrayUnpacker($rawWsiData);
        $unpackedData = $rawWsiData;

        $this->assertEquals(
            $unpackedData,
            $expectedUnpackedData,
            'Complex filters were formatted incorrectly.'
        );
    }
}
