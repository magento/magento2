<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\GoogleShopping\Helper;

use \Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

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
        $this->storeManagerInterface = $this->getMock('Magento\Store\Model\StoreManagerInterface');

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

    public function dataProviderGDataMessage()
    {
        return [
            ['message' => 'Some string', 'expectedResult' => 'Some string'],
            [
                'message' => '<tag>insidetag</tag>outsidetag',
                'expectedResult' => ''
            ],
            [
                'message' => 'multiline

message',
                'expectedResult' => 'multiline. message'
            ]
        ];
    }

    /**
     * @param string $message
     * @param string $expectedResult
     *
     * @dataProvider dataProviderGDataMessage
     */
    public function testParseGdataExceptionMessage($message, $expectedResult)
    {
        $result = $this->data->parseGdataExceptionMessage($message);
        $this->assertEquals($expectedResult, $result);
    }

    public function dataProviderName()
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
     * @dataProvider dataProviderName
     */
    public function testNormalizeName($name, $normalizedName)
    {
        $resultingName = $this->data->normalizeName($name);
        $this->assertEquals($normalizedName, $resultingName);
    }
}
