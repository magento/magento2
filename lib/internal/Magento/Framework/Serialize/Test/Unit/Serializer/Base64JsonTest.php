<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Serialize\Test\Unit\Serializer;

use Magento\Framework\DataObject;
use Magento\Framework\Serialize\Serializer\Base64Json;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class Base64JsonTest extends TestCase
{
    /**
     * @var Base64Json
     */
    private $base64json;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->base64json = $objectManager->getObject(Base64Json::class, [
            'jsonSerializer' => new Json()
        ]);
    }

    /**
     * @param string|int|float|bool|array|null $value
     * @param string $expected
     * @dataProvider serializeDataProvider
     */
    public function testSerialize($value, $expected)
    {
        $this->assertEquals($expected, $this->base64json->serialize($value));
    }

    /**
     * @return array
     */
    public static function serializeDataProvider()
    {
        $dataObject = new DataObject(['something']);
        return [
            ['', 'IiI='], // ""
            ['string', 'InN0cmluZyI='], // "string"
            [null, 'bnVsbA=='], // null
            [false, 'ZmFsc2U='], // false
            [['a' => 'b', 'd' => 123], 'eyJhIjoiYiIsImQiOjEyM30='], // Means: {"a":"b","d":123}
            [123, 'MTIz'], // 123
            [10.56, 'MTAuNTY='], // 10.56
            [$dataObject, 'e30='], // {}
        ];
    }

    /**
     * @param string $value
     * @param string|int|float|bool|array|null $expected
     * @dataProvider unserializeDataProvider
     */
    public function testUnserialize($value, $expected)
    {
        $this->assertEquals($expected, $this->base64json->unserialize($value));
    }

    /**
     * @return array
     */
    public static function unserializeDataProvider()
    {
        return [
            ['""', ''],
            ['"string"', 'string'],
            ['null', null],
            ['false', false],
            ['{"a":"b","d":123}', ['a' => 'b', 'd' => 123]],
            ['123', 123],
            ['10.56', 10.56],
            ["IiI=", ''],
            ['InN0cmluZyI=', 'string'],
            ['bnVsbA==', null],
            ['ZmFsc2U=', false],
            ['eyJhIjoiYiIsImQiOjEyM30=', ['a' => 'b', 'd' => 123]],
            ['MTIz', 123],
            ['MTAuNTY=', 10.56],
            ['e30=', []],
        ];
    }
}
