<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Serialize\Test\Unit\Serializer;

use Magento\Framework\Serialize\Serializer\Json;

class JsonTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $json;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->json = $objectManager->getObject(Json::class);
    }

    /**
     * @param null|bool|array $value
     * @param int $objectDecodeType
     * @dataProvider serializeUnserializeDataProvider
     */
    public function testSerializeUnserialize($value)
    {
        $this->assertEquals(
            $this->json->unserialize($this->json->serialize($value)),
            $value
        );
    }

    public function serializeUnserializeDataProvider()
    {
        return [
            [''],
            [null],
            [false],
            [['a' => 'b']],
        ];
    }
}
