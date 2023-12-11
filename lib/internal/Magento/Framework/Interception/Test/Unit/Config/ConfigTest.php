<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Interception\Test\Unit\Config;

use Magento\Framework\Config\ScopeListInterface;
use Magento\Framework\Interception\Config\CacheManager;
use Magento\Framework\Interception\Config\Config;
use Magento\Framework\Interception\ObjectManager\ConfigInterface;
use Magento\Framework\Interception\Test\Unit\Custom\Module\Model\Item;
use Magento\Framework\Interception\Test\Unit\Custom\Module\Model\Item\Enhanced;
use Magento\Framework\Interception\Test\Unit\Custom\Module\Model\ItemContainer;
use Magento\Framework\ObjectManager\Config\Reader\Dom;
use Magento\Framework\ObjectManager\DefinitionInterface;
use Magento\Framework\ObjectManager\Relations\Runtime;
use Magento\Framework\ObjectManager\RelationsInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../Custom/Module/Model/Item.php';
require_once __DIR__ . '/../Custom/Module/Model/Item/Enhanced.php';
require_once __DIR__ . '/../Custom/Module/Model/ItemContainer.php';
require_once __DIR__ . '/../Custom/Module/Model/ItemContainer/Enhanced.php';
require_once __DIR__ . '/../Custom/Module/Model/ItemContainerPlugin/Simple.php';
require_once __DIR__ . '/../Custom/Module/Model/ItemPlugin/Simple.php';
require_once __DIR__ . '/../Custom/Module/Model/ItemPlugin/Advanced.php';

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConfigTest extends TestCase
{
    /**
     * @var MockObject
     */
    private $configScopeMock;

    /**
     * @var MockObject
     */
    private $readerMock;

    /**
     * @var MockObject
     */
    private $omConfigMock;

    /**
     * @var MockObject
     */
    private $definitionMock;

    /**
     * @var MockObject
     */
    private $relationsMock;

    /**
     * @var CacheManager|MockObject
     */
    private $cacheManagerMock;

    /** @var ObjectManager */
    private $objectManagerHelper;

    protected function setUp(): void
    {
        $this->readerMock = $this->createMock(Dom::class);
        $this->configScopeMock = $this->getMockForAbstractClass(ScopeListInterface::class);
        $this->omConfigMock = $this->getMockForAbstractClass(
            ConfigInterface::class
        );
        $this->definitionMock = $this->getMockForAbstractClass(DefinitionInterface::class);
        $this->relationsMock = $this->getMockForAbstractClass(
            RelationsInterface::class
        );
        $this->cacheManagerMock = $this->createMock(CacheManager::class);
        $this->objectManagerHelper = new ObjectManager($this);
    }

    /**
     * @param boolean $expectedResult
     * @param string $type
     * @dataProvider hasPluginsDataProvider
     */
    public function testHasPluginsWhenDataIsNotCached($expectedResult, $type, $entityParents)
    {
        $readerMap = include __DIR__ . '/../_files/reader_mock_map.php';
        $this->readerMock->expects($this->any())
            ->method('read')
            ->willReturnMap($readerMap);
        $this->configScopeMock->expects($this->any())
            ->method('getAllScopes')
            ->willReturn(['global', 'backend', 'frontend']);
        // turn cache off
        $this->cacheManagerMock->expects($this->any())
            ->method('load')
            ->willReturn(null);
        $this->omConfigMock->expects($this->any())
            ->method('getOriginalInstanceType')
            ->willReturnMap(
                [
                    [
                        ItemContainer::class,
                        ItemContainer::class,
                    ],
                    [
                        Item::class,
                        Item::class,
                    ],
                    [
                        Enhanced::class,
                        Enhanced::class,
                    ],
                    [
                        \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\ItemContainer\Enhanced::class,
                        \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\ItemContainer\Enhanced::class,
                    ],
                    [
                        \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\ItemContainer\Proxy::class,
                        \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\ItemContainer\Proxy::class,
                    ],
                    [
                        \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\Item\Proxy::class,
                        \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\Item\Proxy::class,
                    ],
                    [
                        \Magento\Framework\Interception\Custom\Module\Model\Backslash\Item\Proxy::class,
                        \Magento\Framework\Interception\Custom\Module\Model\Backslash\Item\Proxy::class
                    ],
                    [
                        'virtual_custom_item',
                        Item::class
                    ],
                ]
            );
        $this->definitionMock->expects($this->any())->method('getClasses')->willReturn(
            [
                \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\Item\Proxy::class,
                \Magento\Framework\Interception\Custom\Module\Model\Backslash\Item\Proxy::class
            ]
        );
        $this->relationsMock->expects($this->any())->method('has')->willReturn($expectedResult);
        $this->relationsMock->expects($this->any())->method('getParents')->willReturn($entityParents);

        $model = $this->objectManagerHelper->getObject(
            Config::class,
            [
                'reader' => $this->readerMock,
                'scopeList' => $this->configScopeMock,
                'cacheManager' => $this->cacheManagerMock,
                'relations' => $this->relationsMock,
                'omConfig' => $this->omConfigMock,
                'classDefinitions' => $this->definitionMock,
            ]
        );

        $this->assertEquals($expectedResult, $model->hasPlugins($type));
    }

    /**
     * @param boolean $expectedResult
     * @param string $type
     * @dataProvider hasPluginsDataProvider
     */
    public function testHasPluginsWhenDataIsCached($expectedResult, $type)
    {
        $cacheId = 'interception';
        $interceptionData = [
            ItemContainer::class => true,
            Item::class => true,
            Enhanced::class => true,
            \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\ItemContainer\Enhanced::class => true,
            \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\ItemContainer\Proxy::class => true,
            \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\Item\Proxy::class => false,
            'virtual_custom_item' => true
        ];
        $this->readerMock->expects($this->never())->method('read');
        $this->cacheManagerMock->expects($this->never())->method('save');
        $this->cacheManagerMock->expects($this->any())
            ->method('load')
            ->with($cacheId)
            ->willReturn($interceptionData);

        $model = $this->objectManagerHelper->getObject(
            Config::class,
            [
                'reader' => $this->readerMock,
                'scopeList' => $this->configScopeMock,
                'cacheManager' => $this->cacheManagerMock,
                'relations' => $this->objectManagerHelper->getObject(
                    Runtime::class
                ),
                'omConfig' => $this->omConfigMock,
                'classDefinitions' => $this->definitionMock,
                'cacheId' => $cacheId,
            ]
        );

        $this->assertEquals($expectedResult, $model->hasPlugins($type));
    }

    /**
     * @return array
     */
    public function hasPluginsDataProvider()
    {
        return [
            // item container has plugins only in the backend scope
            [
                true, ItemContainer::class,
                []
            ],
            [
                true, Item::class,
                []
            ],
            [
                true, Enhanced::class,
                []
            ],
            [
                // the following model has only inherited plugins
                true, \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\ItemContainer\Proxy::class,
                [ItemContainer::class]
            ],
            [
                // the following model has only inherited plugins
                true, \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\ItemContainer\Proxy::class,
                [ItemContainer::class]
            ],
            [
                false, \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\Item\Proxy::class,
                []
            ],
            [
                true,
                'virtual_custom_item',
                []
            ]
        ];
    }
}
