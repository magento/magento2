<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Mview\Test\Unit\Config;

class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * @var \Magento\Framework\Mview\Config\Data
     */
    private $config;

    /**
     * @var \Magento\Framework\Mview\Config\Reader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $reader;

    /**
     * @var \Magento\Framework\Config\CacheInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cache;

    /**
     * @var \Magento\Framework\Mview\View\State\CollectionInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stateCollection;

    /**
     * @var string
     */
    private $cacheId = 'mview_config';

    /**
     * @var string
     */
    private $views = ['view1' => [], 'view3' => []];

    /**
     * @var \Magento\Framework\Json\JsonInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $jsonMock;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->reader = $this->getMock(\Magento\Framework\Mview\Config\Reader::class, ['read'], [], '', false);
        $this->cache = $this->getMockForAbstractClass(
            \Magento\Framework\Config\CacheInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['test', 'load', 'save']
        );
        $this->stateCollection = $this->getMockForAbstractClass(
            \Magento\Framework\Mview\View\State\CollectionInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['getItems']
        );

        $this->jsonMock = $this->getMock(\Magento\Framework\Json\JsonInterface::class);
        $this->objectManager->mockObjectManager([\Magento\Framework\Json\JsonInterface::class => $this->jsonMock]);
    }

    public function tearDown()
    {
        $this->objectManager->restoreObjectManager();
    }

    public function testConstructorWithCache()
    {
        $this->cache->expects($this->once())->method('test')->with($this->cacheId)->will($this->returnValue(true));
        $this->cache->expects($this->once())
            ->method('load')
            ->with($this->cacheId);

        $this->stateCollection->expects($this->never())->method('getItems');

        $this->jsonMock->method('decode')
            ->willReturn($this->views);

        $this->config = new \Magento\Framework\Mview\Config\Data(
            $this->reader,
            $this->cache,
            $this->stateCollection,
            $this->cacheId
        );
    }

    public function testConstructorWithoutCache()
    {
        $this->cache->expects($this->once())->method('test')->with($this->cacheId)->will($this->returnValue(false));
        $this->cache->expects($this->once())->method('load')->with($this->cacheId)->will($this->returnValue(false));

        $this->reader->expects($this->once())->method('read')->will($this->returnValue($this->views));

        $stateExistent = $this->getMock(
            \Magento\Framework\Mview\Indexer\State::class,
            ['getViewId', '__wakeup', 'delete'],
            [],
            '',
            false
        );
        $stateExistent->expects($this->once())->method('getViewId')->will($this->returnValue('view1'));
        $stateExistent->expects($this->never())->method('delete');

        $stateNonexistent = $this->getMock(
            \Magento\Framework\Mview\Indexer\State::class,
            ['getViewId', '__wakeup', 'delete'],
            [],
            '',
            false
        );
        $stateNonexistent->expects($this->once())->method('getViewId')->will($this->returnValue('view2'));
        $stateNonexistent->expects($this->once())->method('delete');

        $states = [$stateExistent, $stateNonexistent];

        $this->stateCollection->expects($this->once())->method('getItems')->will($this->returnValue($states));

        $this->config = new \Magento\Framework\Mview\Config\Data(
            $this->reader,
            $this->cache,
            $this->stateCollection,
            $this->cacheId
        );
    }
}
