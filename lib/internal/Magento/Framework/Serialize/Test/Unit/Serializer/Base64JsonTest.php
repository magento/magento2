<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Serialize\Test\Unit\Serializer;

class Base64JsonTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Serialize\Serializer\Base64Json
     */
    private $base64json;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->base64json = $objectManager->getObject(\Magento\Framework\Serialize\Serializer\Base64Json::class, [
            'jsonSerializer' => new \Magento\Framework\Serialize\Serializer\Json()
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

    public function serializeDataProvider()
    {
        $dataObject = new \Magento\Framework\DataObject(['something']);
        return [
            ['', 'IiI='], // ""
            ['string', 'InN0cmluZyI='], // "string"
            [null, 'bnVsbA=='], // null
            [false, 'ZmFsc2U='], // false
            [['a' => 'b', 'd' => 123], 'eyJhIjoiYiIsImQiOjEyM30='], // {"a":"b","d":123}
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

    public function unserializeDataProvider()
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
