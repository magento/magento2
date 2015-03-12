<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GoogleShopping\Test\Unit\Helper;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class DataTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\GoogleShopping\Helper\Data */
    protected $data;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Framework\App\Helper\Context|\PHPUnit_Framework_MockObject_MockObject */
    protected $context;

    /** @var \Magento\Framework\Stdlib\String|\PHPUnit_Framework_MockObject_MockObject */
    protected $string;

    /** @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $storeManagerInterface;

    protected function setUp()
    {
        $this->context = $this->getMock('Magento\Framework\App\Helper\Context', [], [], '', false);
        $this->string = $this->getMock('Magento\Framework\Stdlib\String');
        $this->storeManagerInterface = $this->getMock('Magento\Store\Model\StoreManager', ['getStore'], [], '', false);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->data = $this->objectManagerHelper->getObject(
            'Magento\GoogleShopping\Helper\Data',
            [
                'context' => $this->context,
                'string' => $this->string,
                'storeManager' => $this->storeManagerInterface
            ]
        );
    }

    public function testBuildContentProductId()
    {
        $result = $this->data->buildContentProductId(2, 5);
        $this->assertEquals("2_5", $result);
    }

    public function gdataMessageDataProvider()
    {
        return [
            [
                'message' => 'Some string',
                'expectedResult' => 'Some string'
            ],
            [
                'message' => '<tag>insidetag</tag>outsidetag',
                'expectedResult' => ''
            ],
            [
                'message' => "multiline\n\nmessage",
                'expectedResult' => 'multiline. message'
            ],
            [
                'message' => '<tag reason="anyreason" type="anutype"></tag>>',
                'expectedResult' => 'Reason: anyreason. Type: anutype'
            ]
        ];
    }

    /**
     * @param string $message
     * @param string $expectedResult
     *
     * @dataProvider gdataMessageDataProvider
     */
    public function testParseGdataExceptionMessage($message, $expectedResult)
    {
        $result = $this->data->parseGdataExceptionMessage($message);
        $this->assertEquals($expectedResult, $result);
    }

    public function nameDataProvider()
    {
        return [
            ['name' => 'somename', 'normalizedName' => 'somename'],
            ['name' => 'so/m e\name', 'normalizedName' => 'so/m_e\name'],
            ['name' => '', 'normalizedName' => '']
        ];
    }

    /**
     * @param string $name
     * @param string $normalizedName
     *
     * @dataProvider nameDataProvider
     */
    public function testNormalizeName($name, $normalizedName)
    {
        $resultingName = $this->data->normalizeName($name);
        $this->assertEquals($normalizedName, $resultingName);
    }

    public function testParseGdataExceptionMessageWithProduct()
    {
        $message = "some message\n\nother message";
        $product = $this->getMock('Magento\Catalog\Model\Product', ['getName', 'getStoreId'], [], '', false);
        $product->expects($this->any())->method('getName')->will($this->returnValue("product name"));
        $storeId = 1;
        $product->expects($this->any())->method('getStoreId')->will($this->returnValue($storeId));
        $store = $this->getMock('Magento\Store\Model\Store', [], [], '', false);
        $this->storeManagerInterface->expects($this->any())->method('getStore')->with($storeId)->will(
            $this->returnValue($store)
        );
        $store->expects($this->any())->method('getName')->will($this->returnValue('store name'));
        $result = $this->data->parseGdataExceptionMessage($message, $product);
        $this->assertEquals(
            "some message for product 'product name' (in 'store name' store). " .
            "other message for product 'product name' (in 'store name' store)",
            $result
        );
    }
}
