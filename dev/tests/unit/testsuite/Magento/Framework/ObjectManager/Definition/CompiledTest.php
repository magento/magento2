<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\ObjectManager\Definition;

class CompiledTest extends \PHPUnit_Framework_TestCase
{
    public function testGetParametersWithUndefinedDefinition()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
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
            'Magento\Framework\ObjectManager\Definition\CompiledStub',
            [
                'definitions' => [[], []],
                'reader' => $readerMock
            ]
        );
        $this->assertEquals($undefinedDefinitionSignature, $model->getParameters($className));
    }
}

/**
 * Stub class for abstract Magento\Framework\ObjectManager\Definition
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