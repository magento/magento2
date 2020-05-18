<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Serialize\Test\Unit\Serializer;

use Magento\Framework\DataObject;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Serialize\Serializer\JsonHexTag;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class JsonHexTagTest extends TestCase
{
    /**
     * @var Json
     */
    private $json;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->json = $objectManager->getObject(JsonHexTag::class);
    }

    /**
     * @param string|int|float|bool|array|null $value
     * @param string $expected
     * @dataProvider serializeDataProvider
     */
    public function testSerialize($value, $expected)
    {
        $this->assertEquals(
            $expected,
            $this->json->serialize($value)
        );
    }

    /**
     * @return array
     */
    public function serializeDataProvider()
    {
        $dataObject = new DataObject(['something']);
        return [
            ['', '""'],
            ['string', '"string"'],
            [null, 'null'],
            [false, 'false'],
            [['a' => 'b', 'd' => 123], '{"a":"b","d":123}'],
            [123, '123'],
            [10.56, '10.56'],
            [$dataObject, '{}'],
            ['< >', '"\u003C \u003E"'],
        ];
    }

    /**
     * @param string $value
     * @param string|int|float|bool|array|null $expected
     * @dataProvider unserializeDataProvider
     */
    public function testUnserialize($value, $expected)
    {
        $this->assertEquals(
            $expected,
            $this->json->unserialize($value)
        );
    }

    /**
     * @return array
     */
    public function unserializeDataProvider(): array
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
            ['"\u003C \u003E"', '< >'],
        ];
    }

    public function testSerializeException()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Unable to serialize value.');
        $this->json->serialize(STDOUT);
    }

    /**
     * @dataProvider unserializeExceptionDataProvider
     */
    public function testUnserializeException($value)
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Unable to unserialize value.');
        $this->json->unserialize($value);
    }

    /**
     * @return array
     */
    public function unserializeExceptionDataProvider(): array
    {
        return [
            [''],
            [false],
            [null],
            ['{']
        ];
    }
}
