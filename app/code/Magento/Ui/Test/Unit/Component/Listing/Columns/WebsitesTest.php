<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);

namespace Magento\Ui\Test\Unit\Component\Listing\Columns;

use Magento\Catalog\Ui\Component\Listing\Columns\Websites;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;

/**
 * Testing for the Websites UI column
 */
class WebsitesTest extends ColumnTest
{
    /**
     * @var string
     */
    protected $columnClass = Websites::class;

    /**
     * @var string
     */
    protected $columnName = Websites::NAME;

    /**
     * @inheritDoc
     */
    public function testPrepare()
    {
        $collectionMock = $this->getMockBuilder(AbstractCollection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $selectMock = $this->createMock(Select::class);

        $selectMock->expects($this->once())
            ->method('order');

        $selectMock->expects($this->once())
            ->method('from')
            ->willReturn($selectMock);

        $selectMock->expects($this->atLeastOnce())
            ->method('joinLeft')
            ->willReturn($selectMock);

        $selectMock->expects($this->once())
            ->method('group');

        $connectionMock = $this->createMock(AdapterInterface::class);

        $connectionMock->expects($this->once())
            ->method('select')
            ->willReturn($selectMock);

        $this->dataProviderMock = $this->getMockBuilder(DataProviderInterface::class)
            ->setMethods(['getCollection', 'getSelect'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->dataProviderMock->expects($this->once())
            ->method('getCollection')
            ->willReturn($collectionMock);

        $collectionMock->expects($this->once())
            ->method('getConnection')
            ->willReturn($connectionMock);

        $collectionMock->expects($this->atLeastOnce())
            ->method('getTable')
            ->willReturn('test_table');

        $collectionMock->expects($this->once())
            ->method('getSelect')
            ->willReturn($selectMock);

        parent::testPrepare();
    }
}
