<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Analytics\Test\Unit\Model\Connector\Http;

use Magento\Analytics\Model\Connector\Http\JsonConverter;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class JsonConverterTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManagerHelper;

    /**
     * @var Json|MockObject
     */
    private $serializerMock;

    /**
     * @var JsonConverter
     */
    private $converter;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManager($this);
        $this->serializerMock = $this->createMock(Json::class);
        $this->converter = $this->objectManagerHelper->getObject(
            JsonConverter::class,
            ['serializer' => $this->serializerMock]
        );
    }

    /**
     * @return void
     */
    public function testConverterContainsHeader()
    {
        $this->assertEquals(
            'Content-Type: ' . JsonConverter::CONTENT_MEDIA_TYPE,
            $this->converter->getContentTypeHeader()
        );
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

    /**
     *  return void
     */
    public function testConvertData()
    {
        $this->serializerMock->expects($this->once())
            ->method('serialize')
            ->willReturn('serializedResult');
        $this->assertEquals('serializedResult', $this->converter->toBody(["token" => "secret-token"]));
    }
}
