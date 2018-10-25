<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Interception\Test\Unit\Config;

use Magento\Framework\Serialize\SerializerInterface;

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
class ConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $configScopeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $readerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $omConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $definitionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $relationsMock;

    /**
     * @var \Magento\Framework\Interception\Config\CacheManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cacheManagerMock;

    /** @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager */
    private $objectManagerHelper;

    protected function setUp()
    {
        $this->readerMock = $this->createMock(\Magento\Framework\ObjectManager\Config\Reader\Dom::class);
        $this->configScopeMock = $this->createMock(\Magento\Framework\Config\ScopeListInterface::class);
        $this->omConfigMock = $this->getMockForAbstractClass(
            \Magento\Framework\Interception\ObjectManager\ConfigInterface::class
        );
        $this->definitionMock = $this->createMock(\Magento\Framework\ObjectManager\DefinitionInterface::class);
        $this->relationsMock = $this->getMockForAbstractClass(
            \Magento\Framework\ObjectManager\RelationsInterface::class
        );
        $this->cacheManagerMock = $this->createMock(\Magento\Framework\Interception\Config\CacheManager::class);
        $this->objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
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
            ->will($this->returnValueMap($readerMap));
        $this->configScopeMock->expects($this->any())
            ->method('getAllScopes')
            ->will($this->returnValue(['global', 'backend', 'frontend']));
        // turn cache off
        $this->cacheManagerMock->expects($this->any())
            ->method('load')
            ->will($this->returnValue(null));
        $this->omConfigMock->expects($this->any())
            ->method('getOriginalInstanceType')
            ->will($this->returnValueMap(
                [
                    [
                        \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\ItemContainer::class,
                        \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\ItemContainer::class,
                    ],
                    [
                        \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\Item::class,
                        \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\Item::class,
                    ],
                    [
                        \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\Item\Enhanced::class,
                        \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\Item\Enhanced::class,
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
                        \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\ItemProxy::class,
                        \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\ItemProxy::class,
                    ],
                    [
                        \Magento\Framework\Interception\Custom\Module\Model\Backslash\ItemProxy::class,
                        \Magento\Framework\Interception\Custom\Module\Model\Backslash\ItemProxy::class
                    ],
                    [
                        'virtual_custom_item',
                        \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\Item::class
                    ],
                ]
            ));
        $this->definitionMock->expects($this->any())->method('getClasses')->will($this->returnValue(
            [
                \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\ItemProxy::class,
                \Magento\Framework\Interception\Custom\Module\Model\Backslash\ItemProxy::class
            ]
        ));
        $this->relationsMock->expects($this->any())->method('has')->will($this->returnValue($expectedResult));
        $this->relationsMock->expects($this->any())->method('getParents')->will($this->returnValue($entityParents));

        $model = $this->objectManagerHelper->getObject(
            \Magento\Framework\Interception\Config\Config::class,
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
            \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\ItemContainer::class => true,
            \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\Item::class => true,
            \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\Item\Enhanced::class => true,
            \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\ItemContainer\Enhanced::class => true,
            \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\ItemContainer\Proxy::class => true,
            \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\ItemProxy::class => false,
            'virtual_custom_item' => true
        ];
        $this->readerMock->expects($this->never())->method('read');
        $this->cacheManagerMock->expects($this->never())->method('save');
        $this->cacheManagerMock->expects($this->any())
            ->method('load')
            ->with($cacheId)
            ->will($this->returnValue($interceptionData));

        $model = $this->objectManagerHelper->getObject(
            \Magento\Framework\Interception\Config\Config::class,
            [
                'reader' => $this->readerMock,
                'scopeList' => $this->configScopeMock,
                'cacheManager' => $this->cacheManagerMock,
                'relations' => $this->objectManagerHelper->getObject(
                    \Magento\Framework\ObjectManager\Relations\Runtime::class
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
                true, \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\ItemContainer::class,
                []
            ],
            [
                true, \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\Item::class,
                []
            ],
            [
                true, \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\Item\Enhanced::class,
                []
            ],
            [
                // the following model has only inherited plugins
                true, \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\ItemContainer\Proxy::class,
                [\Magento\Framework\Interception\Test\Unit\Custom\Module\Model\ItemContainer::class]
            ],
            [
                // the following model has only inherited plugins
                true, \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\ItemContainer\Proxy::class,
                [\Magento\Framework\Interception\Test\Unit\Custom\Module\Model\ItemContainer::class]
            ],
            [
                false, \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\ItemProxy::class,
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
