<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\CompiledInterception\Test\Integration\CompiledInterceptor;

use Magento\Framework\CompiledInterception\Generator\AreasPluginList;
use Magento\Framework\App\AreaList;
use Magento\Framework\Code\Generator\Io;
use Magento\Framework\CompiledInterception\Generator\CompiledInterceptor;
use Magento\Framework\CompiledInterception\Generator\CompiledPluginList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\CompiledInterception\Test\Integration\CompiledInterceptor\Custom\Module\Model\SecondItem;
use Magento\Framework\CompiledInterception\Test\Integration\CompiledInterceptor\Custom\Module\Model\ComplexItem;
use Magento\Framework\CompiledInterception\Test\Integration\CompiledInterceptor\Custom\Module\Model\ComplexItemTyped;
use Magento\Framework\CompiledInterception\Test\Integration\CompiledInterceptor\Custom\Module\Model\Item;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\NullLogger;

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
     * @return array
     */
    public function createScopeReaders()
    {
        $readerMap = include __DIR__ . '/../_files/reader_mock_map.php';
        $readerMock = $this->createMock(\Magento\Framework\ObjectManager\Config\Reader\Dom::class);
        $readerMock->expects($this->any())->method('read')->will($this->returnValueMap($readerMap));

        $omMock = $this->createMock(ObjectManager::class);
        $omMock->method('get')->with(\Psr\Log\LoggerInterface::class)->willReturn(new NullLogger());

        $omConfigMock =  $this->getMockForAbstractClass(
            \Magento\Framework\Interception\ObjectManager\ConfigInterface::class
        );

        $omConfigMock->expects($this->any())->method('getOriginalInstanceType')->will($this->returnArgument(0));
        $ret = [];
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        foreach ($readerMap as $readerLine) {
            $ret[$readerLine[0]] = $objectManagerHelper->getObject(
                CompiledPluginList::class,
                [
                    'objectManager' => $omMock,
                    'scope' => $readerLine[0],
                    'reader' => $readerMock,
                    'omConfig' => $omConfigMock,
                    'cachePath' => false
                ]
            );
        }
        return $ret;
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
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        /** @var AreasPluginList $areaPlugins */
        $areaPlugins = $objectManagerHelper->getObject(
            AreasPluginList::class,
            [
                'areaList' => $this->areaList,
                'plugins' => $this->createScopeReaders()
            ]
        );


        /** @var CompiledInterceptor|MockObject $interceptor */
        $interceptor = $this->getMockBuilder(CompiledInterceptor::class)
            ->setMethods(['_validateData'])
            ->setConstructorArgs(
                [
                    $areaPlugins,
                    $className,
                    $resultClassName,
                    $this->ioGenerator,
                    null,
                    null
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

        /*
        eval( $code );
        $className  = "\\$resultClassName";
        $interceptor = new $className();
        */
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
            [
                SecondItem::class,
                SecondItem::class . '\Interceptor',
                'SecondItem'
            ],
        ];
    }
}
