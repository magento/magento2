<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Test\Unit\Console\Command;

use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Framework\App\ObjectManagerFactory;

class AbstractIndexerCommandCommonSetup extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\ObjectManager\ConfigLoader
     */
    protected $configLoaderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Indexer\Model\IndexerFactory
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

    protected function setUp()
    {
        $this->objectManagerFactory = $this->createMock(\Magento\Framework\App\ObjectManagerFactory::class);
        $this->objectManager = $this->getMockForAbstractClass(\Magento\Framework\ObjectManagerInterface::class);
        $this->objectManagerFactory->expects($this->any())->method('create')->willReturn($this->objectManager);

        $this->stateMock = $this->createMock(\Magento\Framework\App\State::class);
        $this->configLoaderMock = $this->createMock(\Magento\Framework\App\ObjectManager\ConfigLoader::class);

        $this->collectionFactory = $this->getMockBuilder(\Magento\Indexer\Model\Indexer\CollectionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->indexerFactory = $this->getMockBuilder(\Magento\Indexer\Model\IndexerFactory::class)
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
                            [\Magento\Indexer\Model\IndexerFactory::class, $this->indexerFactory],
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
}
