<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Serialize\Test\Unit\Serializer;

use Magento\Framework\Serialize\Serializer\Serialize;
use Magento\Framework\Serialize\Signer;
use Psr\Log\LoggerInterface;
use Magento\Framework\Serialize\InvalidSignatureException;

class SerializeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Serialize
     */
    private $serialize;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->serialize = $objectManager->getObject(Serialize::class);
    }

    /**
     * @param string|int|float|bool|array|null $value
     * @param string $serializedValue
     * @dataProvider serializeDataProvider
     */
    public function testSerialize($value, $serializedValue)
    {
        $this->assertEquals($serializedValue, $this->serialize->serialize($value));
    }

    /**
     * @return array
     */
    public function serializeDataProvider()
    {
        return [
            ['string', 's:6:"string";'],
            ['', 's:0:"";'],
            [10, 'i:10;'],
            [10.5, 'd:10.5;'],
            [null, 'N;'],
            [false, 'b:0;'],
            [['foo' => 'bar'], 'a:1:{s:3:"foo";s:3:"bar";}'],
        ];
    }

    /**
     * @param string $serializedValue
     * @param string|int|float|bool|array|null $value
     * @dataProvider unserializeDataProvider
     */
    public function testUnserialize($serializedValue, $value)
    {
        $this->assertEquals($value, $this->serialize->unserialize($serializedValue));
    }

    /**
     * @return array
     */
    public function unserializeDataProvider()
    {
        return [
            ['s:6:"string";', 'string'],
            ['s:0:"";', ''],
            ['i:10;', 10],
            ['d:10.5;', 10.5],
            ['N;', null],
            ['b:0;', false],
            ['a:1:{s:3:"foo";s:3:"bar";}', ['foo' => 'bar']],
        ];
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Unable to serialize value.
     */
    public function testSerializeException()
    {
        $this->serialize->serialize(STDOUT);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Unable to unserialize value.
     * @dataProvider unserializeExceptionDataProvider
     */
    public function testUnserializeException($value)
    {
        $this->serialize->unserialize($value);
    }

    /**
     * @return array
     */
    public function unserializeExceptionDataProvider()
    {
        return [
            [''],
            [false],
            [null]
        ];
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Unable to unserialize value, string is corrupted.
     */
    public function testUnserializeExceptionCorruptedString()
    {
        $this->serialize->unserialize('a:');
    }
}
