<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Indexer\Product\Eav;

class AbstractActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Eav\AbstractAction|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_eavDecimalFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_eavSourceFactoryMock;

    protected function setUp()
    {
        $this->_eavDecimalFactoryMock = $this->getMock(
            'Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\DecimalFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->_eavSourceFactoryMock = $this->getMock(
            'Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\SourceFactory',
            ['create'],
            [],
            '',
            false
        );

        $this->_model = $this->getMockForAbstractClass(
            'Magento\Catalog\Model\Indexer\Product\Eav\AbstractAction',
            [$this->_eavDecimalFactoryMock, $this->_eavSourceFactoryMock]
        );
    }

    public function testGetIndexers()
    {
        $expectedIndexers = [
            'source' => 'source_instance',
            'decimal' => 'decimal_instance',
        ];

        $this->_eavSourceFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($expectedIndexers['source']));

        $this->_eavDecimalFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($expectedIndexers['decimal']));

        $this->assertEquals($expectedIndexers, $this->_model->getIndexers());
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Unknown EAV indexer type "unknown_type".
     */
    public function testGetIndexerWithUnknownTypeThrowsException()
    {
        $this->_eavSourceFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue('return_value'));

        $this->_eavDecimalFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue('return_value'));

        $this->_model->getIndexer('unknown_type');
    }

    public function testGetIndexer()
    {
        $this->_eavSourceFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue('source_return_value'));

        $this->_eavDecimalFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue('decimal_return_value'));

        $this->assertEquals('source_return_value', $this->_model->getIndexer('source'));
    }

    public function testReindexWithoutArgumentsExecutesReindexAll()
    {
        $eavSource = $this->getMockBuilder('Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\Source')
            ->disableOriginalConstructor()
            ->getMock();

        $eavDecimal = $this->getMockBuilder('Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\Decimal')
            ->disableOriginalConstructor()
            ->getMock();

        $eavDecimal->expects($this->once())
            ->method('reindexAll');

        $eavSource->expects($this->once())
            ->method('reindexAll');

        $this->_eavSourceFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($eavSource));

        $this->_eavDecimalFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($eavDecimal));

        $this->_model->reindex();
    }

    public function testReindexWithNotNullArgumentExecutesReindexEntities()
    {
        $ids = [1, 2, 3];

        $eavSource = $this->getMockBuilder('Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\Source')
            ->disableOriginalConstructor()
            ->getMock();

        $eavDecimal = $this->getMockBuilder('Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\Decimal')
            ->disableOriginalConstructor()
            ->getMock();

        $eavDecimal->expects($this->once())
            ->method('reindexEntities')
            ->with($ids);

        $eavSource->expects($this->once())
            ->method('reindexEntities')
            ->with($ids);

        $this->_eavSourceFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($eavSource));

        $this->_eavDecimalFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($eavDecimal));

        $this->_model->reindex($ids);
    }
}
