<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Serialize\Test\Unit\Serializer;

use Magento\Framework\Serialize\Serializer\Serialize;
use Magento\Framework\Serialize\Signer;
use Psr\Log\LoggerInterface;
use Magento\Framework\Serialize\InvalidSignatureException;

class SerializeTest extends \PHPUnit_Framework_TestCase
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
     * @param string|int|bool|array|null $value
     * @dataProvider serializeUnserializeDataProvider
     */
    public function testSerializeUnserialize($value)
    {
        $this->assertEquals(
            $this->serialize->unserialize($this->serialize->serialize($value)),
            $value
        );
    }

    public function serializeUnserializeDataProvider()
    {
        return [
            ['string'],
            [''],
            [null],
            [false],
            [['a' => 'b']],
        ];
    }
}
