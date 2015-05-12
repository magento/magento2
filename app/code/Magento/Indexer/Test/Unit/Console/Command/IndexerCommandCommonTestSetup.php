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
     * @var \PHPUnit_Framework_MockObject_MockObject|CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ObjectManagerFactory
     */
    protected $objectManagerFactory;

    protected function setUp()
    {
        $this->objectManagerFactory = $this->getMock('Magento\Framework\App\ObjectManagerFactory', [], [], '', false);
        $objectManager = $this->getMockForAbstractClass('Magento\Framework\ObjectManagerInterface');

        $this->collectionFactory = $this->getMockBuilder('Magento\Indexer\Model\Indexer\CollectionFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->indexerFactory = $this->getMockBuilder('Magento\Indexer\Model\IndexerFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $objectManager
            ->expects($this->exactly(2))
            ->method('create')
            ->will($this->returnValueMap([
                ['Magento\Indexer\Model\Indexer\CollectionFactory', [], $this->collectionFactory],
                ['Magento\Indexer\Model\IndexerFactory', [], $this->indexerFactory],
            ]));

        $this->objectManagerFactory->expects($this->once())->method('create')->willReturn($objectManager);
    }
}
