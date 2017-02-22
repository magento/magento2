<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\ObjectManager\Test\Unit\Definition;

use \Magento\Framework\ObjectManager\Definition\Compiled;

class CompiledTest extends \PHPUnit_Framework_TestCase
{
    public function testGetParametersWithUndefinedDefinition()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $undefinedDefinitionSignature = new \stdClass();
        $className = 'undefinedDefinition';
        $readerMock = $this->getMock(
            '\Magento\Framework\Code\Reader\ClassReader',
            ['getConstructor'],
            [],
            '',
            false
        );
        $readerMock->expects($this->once())
            ->method('getConstructor')
            ->with($className)
            ->willReturn($undefinedDefinitionSignature);
        $model = $objectManager->getObject(
            'Magento\Framework\ObjectManager\Test\Unit\Definition\CompiledStub',
            [
                'definitions' => [[], []],
                'reader' => $readerMock
            ]
        );
        $this->assertEquals($undefinedDefinitionSignature, $model->getParameters($className));
    }
}

/**
 * Stub class for abstract Magento\Framework\ObjectManager\DefinitionInterface
 */
class CompiledStub extends Compiled
{

    /**
     * Unpack signature
     *
     * @param string $signature
     * @return mixed
     */
    protected function _unpack($signature)
    {
        return unserialize($signature);
    }
}
