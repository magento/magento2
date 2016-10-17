<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Serialize\Test\Unit;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Serialize\Json;

class JsonTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Serialize\Json
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
    public function testEncodeDecode($value)
    {
        $this->assertEquals(
            $this->json->unserialize($this->json->serialize($value)),
            $value
        );
    }

    public function encodeDecodeDataProvider()
    {
        $object = new \stdClass();
        $object->a = 'b';
        return [
            [''],
            [null],
            [false],
            [['a' => 'b']],
        ];
    }
}
