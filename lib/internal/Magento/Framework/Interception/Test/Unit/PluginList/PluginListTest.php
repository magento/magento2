<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Interception\Test\Unit\PluginList;

use Magento\Framework\Config\CacheInterface;
use Magento\Framework\Config\ScopeInterface;
use Magento\Framework\Interception\ObjectManager\ConfigInterface;
use Magento\Framework\Interception\PluginList\PluginList;
use Magento\Framework\Interception\Test\Unit\Custom\Module\Model\Item;
use Magento\Framework\Interception\Test\Unit\Custom\Module\Model\Item\Enhanced;
use Magento\Framework\Interception\Test\Unit\Custom\Module\Model\ItemContainer;
use Magento\Framework\Interception\Test\Unit\Custom\Module\Model\ItemPlugin\Advanced;
use Magento\Framework\Interception\Test\Unit\Custom\Module\Model\ItemPlugin\Simple;
use Magento\Framework\Interception\Test\Unit\Custom\Module\Model\StartingBackslash;
use Magento\Framework\Interception\Test\Unit\Custom\Module\Model\StartingBackslash\Plugin;
use Magento\Framework\ObjectManager\Config\Reader\Dom;
use Magento\Framework\ObjectManager\Definition\Runtime;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

require_once __DIR__ . '/../Custom/Module/Model/Item.php';
require_once __DIR__ . '/../Custom/Module/Model/Item/Enhanced.php';
require_once __DIR__ . '/../Custom/Module/Model/ItemContainer.php';
require_once __DIR__ . '/../Custom/Module/Model/ItemContainer/Enhanced.php';
require_once __DIR__ . '/../Custom/Module/Model/ItemContainerPlugin/Simple.php';
require_once __DIR__ . '/../Custom/Module/Model/ItemPlugin/Simple.php';
require_once __DIR__ . '/../Custom/Module/Model/ItemPlugin/Advanced.php';
require_once __DIR__ . '/../Custom/Module/Model/StartingBackslash.php';
require_once __DIR__ . '/../Custom/Module/Model/StartingBackslash/Plugin.php';

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PluginListTest extends TestCase
{
    /**
     * @var PluginList
     */
    private $object;

    /**
     * @var ScopeInterface|MockObject
     */
    private $configScopeMock;

    /**
     * @var CacheInterface|MockObject
     */
    private $cacheMock;

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    /**
     * @var SerializerInterface|MockObject
     */
    private $serializerMock;

    /**
     * @var ObjectManagerInterface||\PHPUnit\Framework\MockObject\MockObject
     */
    private $objectManagerMock;

    protected function setUp(): void
    {
        $readerMap = include __DIR__ . '/../_files/reader_mock_map.php';
        $readerMock = $this->createMock(Dom::class);
        $readerMock->expects($this->any())->method('read')->willReturnMap($readerMap);

        $this->configScopeMock = $this->getMockForAbstractClass(ScopeInterface::class);
        $this->cacheMock = $this->getMockBuilder(CacheInterface::class)
            ->setMethods(['get'])
            ->getMockForAbstractClass();
        // turn cache off
        $this->cacheMock->expects($this->any())
            ->method('get')
            ->willReturn(false);

        $omConfigMock =  $this->getMockForAbstractClass(
            ConfigInterface::class
        );

        $omConfigMock->expects($this->any())->method('getOriginalInstanceType')->willReturnArgument(0);

        $this->objectManagerMock = $this->getMockBuilder(ObjectManagerInterface::class)
            ->setMethods(['get'])
            ->getMockForAbstractClass();
        $this->objectManagerMock->expects($this->any())
            ->method('get')
            ->willReturnArgument(0);
        $this->serializerMock = $this->getMockForAbstractClass(SerializerInterface::class);

        $definitions = new Runtime();

        $objectManagerHelper = new ObjectManager($this);
        $this->object = $objectManagerHelper->getObject(
            PluginList::class,
            [
                'reader' => $readerMock,
                'configScope' => $this->configScopeMock,
                'cache' => $this->cacheMock,
                'relations' => new \Magento\Framework\ObjectManager\Relations\Runtime(),
                'omConfig' => $omConfigMock,
                'definitions' => new \Magento\Framework\Interception\Definition\Runtime(),
                'objectManager' => $this->objectManagerMock,
                'classDefinitions' => $definitions,
                'scopePriorityScheme' => ['global'],
                'cacheId' => 'interception',
                'serializer' => $this->serializerMock
            ]
        );

        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $objectManagerHelper->setBackwardCompatibleProperty(
            $this->object,
            'logger',
            $this->loggerMock
        );
    }

    public function testGetPlugin()
    {
        $this->configScopeMock->expects($this->any())->method('getCurrentScope')->willReturn('backend');
        $this->object->getNext(Item::class, 'getName');
        $this->object->getNext(
            ItemContainer::class,
            'getName'
        );
        $this->object->getNext(
            StartingBackslash::class,
            'getName'
        );
        $this->assertEquals(
            Simple::class,
            $this->object->getPlugin(
                Item::class,
                'simple_plugin'
            )
        );
        $this->assertEquals(
            Advanced::class,
            $this->object->getPlugin(
                Item::class,
                'advanced_plugin'
            )
        );
        $this->assertEquals(
            \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\ItemContainerPlugin\Simple::class,
            $this->object->getPlugin(
                ItemContainer::class,
                'simple_plugin'
            )
        );
        $this->assertEquals(
            Plugin::class,
            $this->object->getPlugin(
                StartingBackslash::class,
                'simple_plugin'
            )
        );
    }

    /**
     * @param $expectedResult
     * @param $type
     * @param $method
     * @param $scopeCode
     * @param string $code
     * @dataProvider getPluginsDataProvider
     */
    public function testGetPlugins($expectedResult, $type, $method, $scopeCode, $code = '__self')
    {
        $this->configScopeMock->expects(
            $this->any()
        )->method(
            'getCurrentScope'
        )->willReturn(
            $scopeCode
        );
        $this->assertEquals($expectedResult, $this->object->getNext($type, $method, $code));
    }

    /**
     * @return array
     */
    public function getPluginsDataProvider()
    {
        return [
            [
                [4 => ['simple_plugin']], Item::class,
                'getName',
                'global',
            ],
            [
                // advanced plugin has lower sort order
                [2 => 'advanced_plugin', 4 => ['advanced_plugin']],
                Item::class,
                'getName',
                'backend'
            ],
            [
                // advanced plugin has lower sort order
                [4 => ['simple_plugin']],
                Item::class,
                'getName',
                'backend',
                'advanced_plugin'
            ],
            // simple plugin is disabled in configuration for
            // \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\Item in frontend
            [null, Item::class, 'getName', 'frontend'],
            // test plugin inheritance
            [
                [4 => ['simple_plugin']],
                Enhanced::class,
                'getName',
                'global'
            ],
            [
                // simple plugin is disabled in configuration for parent
                [2 => 'advanced_plugin', 4 => ['advanced_plugin']],
                Enhanced::class,
                'getName',
                'frontend'
            ],
            [
                null,
                ItemContainer::class,
                'getName',
                'global'
            ],
            [
                [4 => ['simple_plugin']],
                ItemContainer::class,
                'getName',
                'backend'
            ]
        ];
    }

    /**
     * @covers \Magento\Framework\Interception\PluginList\PluginList::getNext
     * @covers \Magento\Framework\Interception\PluginList\PluginList::_inheritPlugins
     */
    public function testInheritPluginsWithNonExistingClass()
    {
        $this->expectException('InvalidArgumentException');
        $this->configScopeMock->expects($this->any())
            ->method('getCurrentScope')
            ->willReturn('frontend');

        $this->object->getNext('SomeType', 'someMethod');
    }

    public function testLoadScopedDataNotCached()
    {
        $this->configScopeMock->expects($this->exactly(3))
            ->method('getCurrentScope')
            ->willReturn('scope');
        $this->serializerMock->expects($this->once())
            ->method('serialize');
        $this->serializerMock->expects($this->never())
            ->method('unserialize');
        $this->cacheMock->expects($this->once())
            ->method('save');

        $this->assertNull($this->object->getNext('Type', 'method'));
    }

    /**
     * @covers \Magento\Framework\Interception\PluginList\PluginList::getNext
     * @covers \Magento\Framework\Interception\PluginList\PluginList::_inheritPlugins
     */
    public function testInheritPluginsWithNotExistingPlugin()
    {
        $this->loggerMock->expects($this->once())
            ->method('info')
            ->with("Reference to undeclared plugin with name 'simple_plugin'.");
        $this->configScopeMock->expects($this->any())
            ->method('getCurrentScope')
            ->willReturn('frontend');

        $this->assertNull($this->object->getNext('typeWithoutInstance', 'someMethod'));
    }

    /**
     * @covers \Magento\Framework\Interception\PluginList\PluginList::getNext
     * @covers \Magento\Framework\Interception\PluginList\PluginList::_loadScopedData
     */
    public function testLoadScopedDataCached()
    {
        $this->configScopeMock->expects($this->once())
            ->method('getCurrentScope')
            ->willReturn('scope');

        $data = [['key'], ['key'], ['key']];
        $serializedData = 'serialized data';

        $this->serializerMock->expects($this->never())
            ->method('serialize');
        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->willReturn($data);
        $this->cacheMock->expects($this->once())
            ->method('load')
            ->with('global|scope|interception')
            ->willReturn($serializedData);

        $this->assertNull($this->object->getNext('Type', 'method'));
    }

    /**
     * @covers \Magento\Framework\Interception\PluginList\PluginList::getNext
     * @covers \Magento\Framework\Interception\PluginList\PluginList::_loadScopedData
     */
    public function testLoadScopeDataWithEmptyData()
    {
        $this->objectManagerMock->expects($this->any())
            ->method('get')
            ->willReturnArgument(0);
        $this->configScopeMock->expects($this->any())
            ->method('getCurrentScope')
            ->willReturn('emptyscope');

        $this->assertEquals(
            [4 => ['simple_plugin']],
            $this->object->getNext(
                Item::class,
                'getName'
            )
        );
        $this->assertEquals(
            Simple::class,
            $this->object->getPlugin(
                Item::class,
                'simple_plugin'
            )
        );
    }
}
