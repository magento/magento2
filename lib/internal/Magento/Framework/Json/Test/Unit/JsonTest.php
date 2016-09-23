<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Json\Test\Unit\Helper;

use Magento\Framework\Json\JsonInterface;
use Magento\Framework\Json\Json;

class JsonTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Json
     */
    private $json;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->json = $objectManager->getObject(Json::class);
    }

    /**
     * @param null|bool|array|\stdClass $value
     * @param int $objectDecodeType
     * @dataProvider encodeDecodeDataProvider
     */
    public function testEncodeDecode($value, $objectDecodeType)
    {
        $this->assertEquals(
            $this->json->decode($this->json->encode($value), $objectDecodeType),
            $value
        );
    }

    public function encodeDecodeDataProvider()
    {
        $object = new \stdClass();
        $object->a = 'b';
        return [
            ['', JsonInterface::TYPE_ARRAY],
            [null, JsonInterface::TYPE_ARRAY],
            [false, JsonInterface::TYPE_ARRAY],
            [['a' => 'b'], JsonInterface::TYPE_ARRAY],
            [$object, JsonInterface::TYPE_OBJECT]
        ];
    }
}
