<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\CompiledInterception\Test\Unit\CompiledInterceptor;

use Magento\Framework\Code\Generator\Io;
use Magento\Framework\CompiledInterception\Generator\CompiledInterceptor;

use Magento\Framework\CompiledInterception\Test\Unit\CompiledPluginList\CompiledPluginListTest;
use \PHPUnit_Framework_MockObject_MockObject as MockObject;

class CompiledInterceptorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Io|MockObject
     */
    private $ioGenerator;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->ioGenerator = $this->getMockBuilder(Io::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Checks a test case when interceptor generates code for the specified class.
     *
     * @param string $className
     * @param string $resultClassName
     * @param string $fileName
     * @dataProvider interceptorDataProvider
     */
    public function testGenerate($className, $resultClassName, $fileName)
    {
        /** @var CompiledInterceptor|MockObject $interceptor */
        $interceptor = $this->getMockBuilder(CompiledInterceptor::class)
            ->setMethods(['_validateData'])
            ->setConstructorArgs([
                $className,
                $resultClassName,
                $this->ioGenerator,
                null,
                null,
                (new CompiledPluginListTest())->createScopeReaders()
            ])
            ->getMock();

        $this->ioGenerator->method('generateResultFileName')->with('\\' . $resultClassName)->willReturn($fileName . '.php');

        $code = file_get_contents(__DIR__ . '/_out_interceptors/' . $fileName . '.txt');

        $this->ioGenerator->method('writeResultFile')->with($fileName . '.php', $code);
        $interceptor->method('_validateData')->willReturn(true);

        $generated = $interceptor->generate();
        $this->assertEquals($fileName . '.php', $generated, 'Generated interceptor is invalid.');
    }

    /**
     * Gets list of interceptor samples.
     *
     * @return array
     */
    public function interceptorDataProvider()
    {
        return [
            [
                \Magento\Framework\CompiledInterception\Test\Unit\Custom\Module\Model\Item::class,
                \Magento\Framework\CompiledInterception\Test\Unit\Custom\Module\Model\Item\Interceptor::class,
                'Item'
            ],
            [
                \Magento\Framework\CompiledInterception\Test\Unit\Custom\Module\Model\ComplexItem::class,
                \Magento\Framework\CompiledInterception\Test\Unit\Custom\Module\Model\ComplexItem\Interceptor::class,
                'ComplexItem'
            ],
            [
                \Magento\Framework\CompiledInterception\Test\Unit\Custom\Module\Model\ComplexItemTyped::class,
                \Magento\Framework\CompiledInterception\Test\Unit\Custom\Module\Model\ComplexItemTyped\Interceptor::class,
                'ComplexItemTyped'
            ],
        ];
    }
}
