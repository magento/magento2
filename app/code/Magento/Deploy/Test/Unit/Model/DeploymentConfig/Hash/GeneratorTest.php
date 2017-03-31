<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Test\Unit\Model\DeploymentConfig\Hash;

use Magento\Deploy\Model\DeploymentConfig\Hash\Generator;
use Magento\Framework\Serialize\SerializerInterface;

class GeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SerializerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializerMock;

    /**
     * @var Generator
     */
    private $generator;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->serializerMock = $this->getMockBuilder(SerializerInterface::class)
            ->getMockForAbstractClass();

        $this->generator = new Generator($this->serializerMock);
    }

    /**
     * @return void
     */
    public function testGenerate()
    {
        $data = 'some config';
        $serializedData = 'serialized content';
        $hash = '40c185113eb5154ad9aa5a8854f197c818e17f62';

        $this->serializerMock->expects($this->once())
            ->method('serialize')
            ->with($data)
            ->willReturn($serializedData);

        $this->assertSame($hash, $this->generator->generate($data));
    }
}
