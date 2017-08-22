<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Json\Test\Unit\Helper;

class DataTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $helper;

    /** @var \Magento\Framework\Json\EncoderInterface | \PHPUnit_Framework_MockObject_MockObject */
    protected $jsonEncoderMock;

    /** @var \Magento\Framework\Json\DecoderInterface | \PHPUnit_Framework_MockObject_MockObject  */
    protected $jsonDecoderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Serialize\Serializer\Json
     */
    private $serializerMock;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->jsonEncoderMock = $this->getMockBuilder(\Magento\Framework\Json\EncoderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->jsonDecoderMock = $this->getMockBuilder(\Magento\Framework\Json\DecoderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->serializerMock = $this->getMockBuilder(\Magento\Framework\Serialize\Serializer\Json::class)
            ->getMock();
        $this->serializerMock->expects($this->any())
            ->method('unserialize')
            ->willReturnCallback(
                function ($serializedData) {
                    return json_decode($serializedData, true);
                }
            );
        $this->serializerMock->expects($this->any())
            ->method('serialize')
            ->willReturnCallback(
                function ($serializedData) {
                    return json_encode($serializedData);
                }
            );
        $this->helper = $objectManager->getObject(
            \Magento\Framework\Json\Helper\Data::class,
            [
                'jsonEncoder' => $this->jsonEncoderMock,
                'jsonDecoder' => $this->jsonDecoderMock,
                'serializer' => $this->serializerMock
            ]
        );
    }

    /**
     * @param string $value
     * @param string|int|float|bool|array|null $expected
     * @throws \InvalidArgumentException
     * @dataProvider getJsonEncodeDataProvider
     */
    public function testJsonEncode($value, $expected)
    {
        $this->assertEquals($expected, $this->helper->jsonEncode($value));
    }

    public function getJsonEncodeDataProvider()
    {
        return [
            ['', '""'],
            ['string', '"string"'],
            [null, 'null'],
            [false, 'false'],
            [['a' => 'b', 'd' => 123], '{"a":"b","d":123}'],
            [123, '123'],
            [10.56, '10.56'],
            [new \stdClass(), '{}'],
        ];
    }

    /**
     * @param string $value
     * @param string|int|float|bool|array|null $expected
     * @throws \InvalidArgumentException
     * @dataProvider getJsonDecodeDataProvider
     */
    public function testJsonDecode($value, $expected)
    {
        $this->assertEquals($expected, $this->helper->jsonDecode($value));
    }

    public function getJsonDecodeDataProvider()
    {
        return [
            ['""', ''],
            ['"string"', 'string'],
            ['null', null],
            ['false', false],
            ['{"a":"b","d":123}', ['a' => 'b', 'd' => 123]],
            ['123', 123],
            ['10.56', 10.56],
            ['{}', []],
        ];
    }
}
