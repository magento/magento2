<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Test\Unit\Model\Indexer\Fulltext\Plugin;

use \Magento\CatalogSearch\Model\Indexer\Fulltext\Plugin\Attribute;

class AttributeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Indexer\IndexerInterface
     */
    protected $indexerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Model\ResourceModel\Attribute
     */
    protected $subjectMock;

    /**
     * @var \Magento\Framework\Indexer\IndexerRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $indexerRegistryMock;

    /**
     * @var Attribute
     */
    protected $model;

    protected function setUp()
    {
        $this->subjectMock = $this->getMock('Magento\Catalog\Model\ResourceModel\Attribute', [], [], '', false);
        $this->indexerMock = $this->getMockForAbstractClass(
            'Magento\Framework\Indexer\IndexerInterface',
            [],
            '',
            false,
            false,
            true,
            ['getId', 'getState', '__wakeup']
        );
        $this->indexerRegistryMock = $this->getMock(
            'Magento\Framework\Indexer\IndexerRegistry',
            ['get'],
            [],
            '',
            false
        );
        $this->config =  $this->getMockBuilder(\Magento\Framework\Search\Request\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = new Attribute($this->indexerRegistryMock, $this->config);
    }

    /**
     * @param bool $isObjectNew
     * @param bool $isSearchableChanged
     * @param int $invalidateCounter
     * @return void
     * @dataProvider aroundSaveDataProvider
     */
    public function testAroundSave($isObjectNew, $isSearchableChanged, $invalidateCounter)
    {
        $attributeMock = $this->getMock(
            '\Magento\Catalog\Model\ResourceModel\Eav\Attribute',
            ['dataHasChangedFor', 'isObjectNew', '__wakeup'],
            [],
            '',
            false
        );
        $attributeMock->expects($this->any())
            ->method('dataHasChangedFor')
            ->will($this->returnValue($isSearchableChanged));

        $attributeMock->expects($this->any())->method('isObjectNew')->will($this->returnValue($isObjectNew));

        $closureMock = function (\Magento\Catalog\Model\ResourceModel\Eav\Attribute $object) use ($attributeMock) {
            $this->assertEquals($object, $attributeMock);
            return $this->subjectMock;
        };

        $this->indexerMock->expects($this->exactly($invalidateCounter))->method('invalidate');
        $this->prepareIndexer($invalidateCounter);

        $this->assertEquals(
            $this->subjectMock,
            $this->model->aroundSave($this->subjectMock, $closureMock, $attributeMock)
        );
    }

    /**
     * @return array
     */
    public function aroundSaveDataProvider()
    {
        return [
            [false, false, 0],
            [false, true, 1],
            [true, false, 0],
            [true, true, 0],
        ];
    }

    /**
     * @param bool $isObjectNew
     * @param bool $isSearchable
     * @param int $invalidateCounter
     * @return void
     * @dataProvider aroundDeleteDataProvider
     */
    public function testAroundDelete($isObjectNew, $isSearchable, $invalidateCounter)
    {
        $attributeMock = $this->getMock(
            '\Magento\Catalog\Model\ResourceModel\Eav\Attribute',
            ['getIsSearchable', 'isObjectNew', '__wakeup'],
            [],
            '',
            false
        );
        $attributeMock->expects($this->any())->method('getIsSearchable')->will($this->returnValue($isSearchable));
        $attributeMock->expects($this->once())->method('isObjectNew')->will($this->returnValue($isObjectNew));

        $closureMock = function (\Magento\Catalog\Model\ResourceModel\Eav\Attribute $object) use ($attributeMock) {
            $this->assertEquals($object, $attributeMock);
            return $this->subjectMock;
        };

        $this->indexerMock->expects($this->exactly($invalidateCounter))->method('invalidate');
        $this->prepareIndexer($invalidateCounter);

        $this->assertEquals(
            $this->subjectMock,
            $this->model->aroundDelete($this->subjectMock, $closureMock, $attributeMock)
        );
    }

    /**
     * @return array
     */
    public function aroundDeleteDataProvider()
    {
        return [
            [false, false, 0],
            [false, true, 1],
            [true, false, 0],
            [true, true, 0],
        ];
    }

    /**
     * @param $invalidateCounter
     */
    protected function prepareIndexer($invalidateCounter)
    {
        $this->indexerRegistryMock->expects($this->exactly($invalidateCounter))
            ->method('get')
            ->with(\Magento\CatalogSearch\Model\Indexer\Fulltext::INDEXER_ID)
            ->will($this->returnValue($this->indexerMock));
    }
}
