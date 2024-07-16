<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Indexer\Test\Unit\Console\Command;

use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Framework\App\ObjectManager\ConfigLoader;
use Magento\Framework\App\ObjectManagerFactory;
use Magento\Framework\App\State;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Framework\Indexer\IndexerInterfaceFactory;
use Magento\Framework\ObjectManager\ConfigLoaderInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Indexer\Model\Indexer\Collection;
use Magento\Indexer\Model\Indexer\CollectionFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AbstractIndexerCommandCommonSetup extends TestCase
{
    /**
     * @var MockObject|ConfigLoader
     */
    protected $configLoaderMock;

    /**
     * @var MockObject|IndexerInterfaceFactory
     */
    protected $indexerFactory;

    /**
     * @var MockObject|State
     */
    protected $stateMock;

    /**
     * @var MockObject|CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var MockObject|ObjectManagerFactory
     */
    protected $objectManagerFactory;

    /**
     * @var MockObject|ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var Collection|MockObject
     */
    protected $indexerCollectionMock;

    protected function setUp(): void
    {
        $this->objectManagerFactory = $this->createMock(ObjectManagerFactory::class);
        $this->objectManager = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->objectManagerFactory->expects($this->any())->method('create')->willReturn($this->objectManager);

        $this->stateMock = $this->createMock(State::class);
        $this->configLoaderMock = $this->createMock(ConfigLoader::class);

        $this->collectionFactory = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $this->indexerCollectionMock = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->collectionFactory
            ->method('create')
            ->willReturn($this->indexerCollectionMock);

        $this->indexerFactory = $this->getMockBuilder(IndexerInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $this->objectManager->expects($this->any())
            ->method('get')
            ->willReturnMap(
                array_merge(
                    $this->getObjectManagerReturnValueMap(),
                    [
                        [CollectionFactory::class, $this->collectionFactory],
                        [IndexerInterfaceFactory::class, $this->indexerFactory],
                    ]
                )
            );
    }

    /**
     * Return value map for object manager
     *
     * @return array
     */
    protected function getObjectManagerReturnValueMap()
    {
        return [
            [State::class, $this->stateMock],
            [ConfigLoaderInterface::class, $this->configLoaderMock]
        ];
    }

    protected function configureAdminArea()
    {
        $config = ['test config'];
        $this->configLoaderMock->expects($this->once())
            ->method('load')
            ->with(FrontNameResolver::AREA_CODE)
            ->willReturn($config);
        $this->objectManager->expects($this->once())
            ->method('configure')
            ->with($config);
        $this->stateMock->expects($this->once())
            ->method('setAreaCode')
            ->with(FrontNameResolver::AREA_CODE);
    }

    /**
     * @param array $methods
     * @param array $data
     * @return MockObject|IndexerInterface
     */
    protected function getIndexerMock(array $methods = [], array $data = [])
    {
        /** @var MockObject|IndexerInterface $indexer */
        $indexer = $this->getMockBuilder(IndexerInterface::class)
            ->onlyMethods(array_merge($methods, ['getId', 'getTitle']))
            ->getMockForAbstractClass();
        $indexer->method('getId')
            ->willReturn($data['indexer_id'] ?? '');
        $indexer->method('getTitle')
            ->willReturn($data['title'] ?? '');
        return $indexer;
    }

    /**
     * Init Indexer Collection Mock by items.
     *
     * @param IndexerInterface[] $items
     * @throws \Exception
     */
    protected function initIndexerCollectionByItems(array $items)
    {
        $this->indexerCollectionMock
            ->method('getItems')
            ->with()
            ->willReturn($items);
    }
}
