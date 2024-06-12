<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\DB\Test\Unit\DataConverter;

use Magento\Framework\DB\DataConverter\SerializedToJson;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Serialize\Serializer\Serialize;
use PHPUnit\Framework\TestCase;

class SerializedToJsonTest extends TestCase
{
    /**
     * @var SerializedToJson
     */
    private $serializedToJson;

    protected function setUp(): void
    {
        $this->serializedToJson =  new SerializedToJson(
            new Serialize(),
            new Json()
        );
    }

    /**
     * Tests converting from serialized to JSON format with different precision settings.
     *
     * @param $serializedData
     * @param $expectedJson
     * @dataProvider convertDataProvider
     */
    public function testConvert($serializedData, $expectedJson)
    {
        $this->assertEquals($expectedJson, $this->serializedToJson->convert($serializedData));
    }

    /**
     * @case #1 - Serialized 0.1234567890123456789 with serialize_precision = 17 (default for PHP version < 7.1.0)
     * @case #2 - Serialized 2.203 with serialize_precision = 17 (default for PHP version < 7.1.0 )
     * @return array
     */
    public static function convertDataProvider()
    {
        return [
            1 => ['serializedData' => 'a:1:{i:0;d:0.12345678901234568;}', 'expectedJson' => '[0.12345678901234568]'],
            2 => ['serializedData' => 'a:1:{i:0;d:2.2029999999999998;}', 'expectedJson' => '[2.2029999999999998]']
        ];
    }
}
