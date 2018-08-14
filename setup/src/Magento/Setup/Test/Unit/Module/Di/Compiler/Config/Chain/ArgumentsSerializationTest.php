<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Module\Di\Compiler\Config\Chain;

use Magento\Setup\Module\Di\Compiler\Config\Chain\ArgumentsSerialization;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Unit test for the ArgumentsSerialization class.
 *
 * @deprecated We don't need anymore serialize arguments, this class will be removed in the next
 *             backward incompatible release.
 */
class ArgumentsSerializationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var SerializerInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $serializer;

    /**
     * Set up mocks.
     */
    protected function setUp()
    {
        $this->serializer = $this->getMockBuilder(SerializerInterface::class)
            ->getMock();
        $this->serializer->expects($this->any())->method('serialize')->willReturnCallback(function ($param) {
            return json_encode($param);
        });
    }

    public function testModifyArgumentsDoNotExist()
    {
        $inputConfig = [
            'data' => []
        ];
        $modifier = new ArgumentsSerialization($this->serializer);
        $this->assertSame($inputConfig, $modifier->modify($inputConfig));
    }

    public function testModifyArguments()
    {
        $inputConfig = [
            'arguments' => [
                'argument1' => [],
                'argument2' => null,
            ]
        ];

        $expected = [
            'arguments' => [
                'argument1' => json_encode([]),
                'argument2' => null,
            ]
        ];

        $modifier = new ArgumentsSerialization($this->serializer);
        $this->assertEquals($expected, $modifier->modify($inputConfig));
    }
}
