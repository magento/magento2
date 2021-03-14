<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\CompiledInterception\CompiledInterceptor;

use Magento\Framework\App\AreaList;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Code\Generator\Io;
use Magento\Framework\CompiledInterception\Generator\AreasPluginList;
use Magento\Framework\CompiledInterception\Generator\CompiledInterceptor;
use Magento\Framework\CompiledInterception\Generator\CompiledPluginList;
use Magento\Framework\CompiledInterception\Generator\CompiledPluginListFactory;
use Magento\Framework\CompiledInterception\Generator\FileCache;
use Magento\Framework\CompiledInterception\Generator\NoSerialize;
use Magento\Framework\CompiledInterception\Generator\StaticScope;
use Magento\Framework\CompiledInterception\CompiledInterceptor\Custom\Module\Model\ComplexItem;
use Magento\Framework\CompiledInterception\CompiledInterceptor\Custom\Module\Model\ComplexItemTyped;
use Magento\Framework\CompiledInterception\CompiledInterceptor\Custom\Module\Model\Item;
use Magento\Framework\CompiledInterception\CompiledInterceptor\Custom\Module\Model\SecondItem;
use Magento\Framework\Config\ScopeInterfaceFactory;
use Magento\Framework\Interception\ObjectManager\ConfigInterface;
use Magento\Framework\Interception\PluginList\PluginList;
use Magento\Framework\ObjectManager\Config\Reader\Dom;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;

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
    protected function setUp(): void
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
        $readerMap = include __DIR__ . '/_files/reader_mock_map.php';
        $readerMock = $this->createMock(Dom::class);
        $readerMock->method('read')->willReturnMap($readerMap);

        $omMock = $this->createMock(ObjectManager::class);

        $omConfigMock =  $this->getMockForAbstractClass(
            ConfigInterface::class
        );

        $omConfigMock->method('getOriginalInstanceType')->willReturnArgument(0);
        $ret = [];
        $objectManagerHelper = new ObjectManagerHelper($this);
        $directoryList = ObjectManager::getInstance()->get(DirectoryList::class);
        //clear static cache
        $fileCache = new FileCache($directoryList);
        $fileCache->clean();
        foreach ($readerMap as $readerLine) {
            $pluginList = ObjectManager::getInstance()->create(
                PluginList::class,
                [
                    'objectManager' => $omMock,
                    'configScope' => new StaticScope($readerLine[0]),
                    'reader' => $readerMock,
                    'omConfig' => $omConfigMock,
                    'cache' => $fileCache,
                    'cachePath' => false,
                    'serializer' => new NoSerialize()
                ]
            );

            $ret[$readerLine[0]] = $objectManagerHelper->getObject(
                CompiledPluginList::class,
                [
                    'pluginList' => $pluginList
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
        $objectManagerHelper = new ObjectManagerHelper($this);
        /** @var AreasPluginList $areaPlugins */
        $areaPlugins = $objectManagerHelper->getObject(
            AreasPluginList::class,
            [
                'areaList' => $this->areaList,
                'scopeInterfaceFactory' => $objectManagerHelper->getObject(ScopeInterfaceFactory::class),
                'compiledPluginListFactory' => $objectManagerHelper->getObject(CompiledPluginListFactory::class),
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
