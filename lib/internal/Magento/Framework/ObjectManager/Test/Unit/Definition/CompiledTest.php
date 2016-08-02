<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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
            \Magento\Framework\Code\Reader\ClassReader::class,
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
            \Magento\Framework\ObjectManager\Test\Unit\Definition\CompiledStub::class,
            [
                'definitions' => [[], []],
                'reader' => $readerMock
            ]
        );
        $this->assertEquals($undefinedDefinitionSignature, $model->getParameters($className));
    }
}
