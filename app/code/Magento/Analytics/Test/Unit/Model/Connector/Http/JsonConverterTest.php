<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Unit\Model\Connector\Http;

use Magento\Analytics\Model\Connector\Http\JsonConverter;
use Magento\Framework\Serialize\Serializer\Json;

class JsonConverterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManagerHelper;

    /**
     * @var Json|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializerMock;

    /**
     * @var JsonConverter
     */
    private $converter;

    protected function setUp()
    {
        $this->objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->serializerMock = $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->converter = $this->objectManagerHelper->getObject(
            JsonConverter::class,
            ['serializer' => $this->serializerMock]
        );
    }

    public function testConverterContainsHeader()
    {
        $this->assertEquals(JsonConverter::CONTENT_TYPE_HEADER, $this->converter->getContentTypeHeader());
    }

    /**
     * @param array|null $unserializedResult
     * @param array $expected
     * @dataProvider convertBodyDataProvider
     */
    public function testConvertBody($unserializedResult, $expected)
    {
        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->willReturn($unserializedResult);
        $this->assertEquals($expected, $this->converter->fromBody('body'));
    }

    /**
     * @return array
     */
    public function convertBodyDataProvider()
    {
        return [
            [null, ['body']],
            [['unserializedBody'], ['unserializedBody']]
        ];
    }

    public function testConvertData()
    {
        $this->serializerMock->expects($this->once())
            ->method('serialize')
            ->willReturn('serializedResult');
        $this->assertEquals('serializedResult', $this->converter->toBody(["token" => "secret-token"]));
    }
}
