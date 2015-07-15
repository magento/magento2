<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Test\Unit\Console\Command;

use Magento\Framework\App\ObjectManagerFactory;

class IndexerCommandCommonTestSetup extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|IndexerFactory
     */
    protected $indexerFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\State
     */
    protected $stateMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ObjectManagerFactory
     */
    protected $objectManagerFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManagerFactory = $this->getMock('Magento\Framework\App\ObjectManagerFactory', [], [], '', false);
        $this->objectManager = $this->getMockForAbstractClass('Magento\Framework\ObjectManagerInterface');
        $this->objectManagerFactory->expects($this->any())->method('create')->willReturn($this->objectManager);

        $this->stateMock = $this->getMock('Magento\Framework\App\State', [], [], '', false);
        $this->objectManager->expects($this->any())
            ->method('get')
            ->with('Magento\Framework\App\State')
            ->willReturn($this->stateMock);

        $this->collectionFactory = $this->getMockBuilder('Magento\Indexer\Model\Indexer\CollectionFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->indexerFactory = $this->getMockBuilder('Magento\Indexer\Model\IndexerFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->objectManager
            ->expects($this->any())
            ->method('create')
            ->will($this->returnValueMap([
                ['Magento\Indexer\Model\Indexer\CollectionFactory', [], $this->collectionFactory],
                ['Magento\Indexer\Model\IndexerFactory', [], $this->indexerFactory],
            ]));
    }
}
