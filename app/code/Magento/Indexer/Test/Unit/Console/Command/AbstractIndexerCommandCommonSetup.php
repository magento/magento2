<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Test\Unit\Console\Command;

use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Indexer\Model\Indexer\Collection;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AbstractIndexerCommandCommonSetup extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\ObjectManager\ConfigLoader
     */
    protected $configLoaderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Indexer\IndexerInterfaceFactory
     */
    protected $indexerFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\State
     */
    protected $stateMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Indexer\Model\Indexer\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\ObjectManagerFactory
     */
    protected $objectManagerFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $indexerCollectionMock;

    protected function setUp()
    {
        $this->objectManagerFactory = $this->getMock(
            \Magento\Framework\App\ObjectManagerFactory::class,
            [],
            [],
            '',
            false
        );
        $this->objectManager = $this->getMockForAbstractClass(\Magento\Framework\ObjectManagerInterface::class);
        $this->objectManagerFactory->expects($this->any())->method('create')->willReturn($this->objectManager);

        $this->stateMock = $this->getMock(\Magento\Framework\App\State::class, [], [], '', false);
        $this->configLoaderMock = $this->getMock(
            \Magento\Framework\App\ObjectManager\ConfigLoader::class,
            [],
            [],
            '',
            false
        );

        $this->collectionFactory = $this->getMockBuilder(\Magento\Indexer\Model\Indexer\CollectionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->indexerCollectionMock = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->collectionFactory
            ->method('create')
            ->willReturn($this->indexerCollectionMock);

        $this->indexerFactory = $this->getMockBuilder(\Magento\Framework\Indexer\IndexerInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->objectManager->expects($this->any())
            ->method('get')
            ->will(
                $this->returnValueMap(
                    array_merge(
                        $this->getObjectManagerReturnValueMap(),
                        [
                            [\Magento\Indexer\Model\Indexer\CollectionFactory::class, $this->collectionFactory],
                            [\Magento\Framework\Indexer\IndexerInterfaceFactory::class, $this->indexerFactory],
                        ]
                    )
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
            [\Magento\Framework\App\State::class, $this->stateMock],
            [\Magento\Framework\ObjectManager\ConfigLoaderInterface::class, $this->configLoaderMock]
        ];
    }

    protected function configureAdminArea()
    {
        $config = ['test config'];
        $this->configLoaderMock->expects($this->once())
            ->method('load')
            ->with(FrontNameResolver::AREA_CODE)
            ->will($this->returnValue($config));
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
     * @return \PHPUnit_Framework_MockObject_MockObject|IndexerInterface
     */
    protected function getIndexerMock(array $methods = [], array $data = [])
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|IndexerInterface $indexer */
        $indexer = $this->getMockBuilder(IndexerInterface::class)
            ->setMethods(array_merge($methods, ['getId', 'getTitle']))
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
