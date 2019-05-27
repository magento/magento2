<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\CompiledInterception\Test\Integration\CompiledInterceptor;

use Magento\Framework\App\AreaList;
use Magento\Framework\Code\Generator\Io;
use Magento\Framework\CompiledInterception\Generator\CompiledInterceptor;

use Magento\Framework\CompiledInterception\Test\Unit\CompiledPluginList\CompiledPluginListTest;
use Magento\Framework\CompiledInterception\Test\Unit\Custom\Module\Model\ComplexItem;
use Magento\Framework\CompiledInterception\Test\Unit\Custom\Module\Model\ComplexItemTyped;
use Magento\Framework\CompiledInterception\Test\Unit\Custom\Module\Model\Item;
use PHPUnit\Framework\MockObject\MockObject as MockObject;

/**
 * Class CompiledInterceptorTest
 */
class CompiledInterceptorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Io|MockObject
     */
    private $ioGenerator;

    /**
     * @var AreaList|MockObject
     */
    private $areaList;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->ioGenerator = $this->getMockBuilder(Io::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->areaList = $this->getMockBuilder(AreaList::class)
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
            ->setConstructorArgs(
                [
                    $this->areaList,
                    $className,
                    $resultClassName,
                    $this->ioGenerator,
                    null,
                    null,
                    (new CompiledPluginListTest())->createScopeReaders()
                ]
            )
            ->getMock();

        $this->ioGenerator->method('generateResultFileName')->with('\\' . $resultClassName)
            ->willReturn($fileName . '.php');

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
                Item::class,
                Item::class . '\Interceptor',
                'Item'
            ],
            [
                ComplexItem::class,
                ComplexItem::class . '\Interceptor',
                'ComplexItem'
            ],
            [
                ComplexItemTyped::class,
                ComplexItemTyped::class . '\Interceptor',
                'ComplexItemTyped'
            ],
        ];
    }
}
