<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Catalog\Model\Indexer\Product\Eav;

class AbstractActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Eav\AbstractAction|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_model;

    /**
     * @var \Magento\Catalog\Model\Resource\Product\Indexer\Eav\DecimalFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_eavDecimalFactoryMock;

    /**
     * @var \Magento\Catalog\Model\Resource\Product\Indexer\Eav\SourceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_eavSourceFactoryMock;

    protected function setUp()
    {
        $this->_eavDecimalFactoryMock = $this->getMock(
            'Magento\Catalog\Model\Resource\Product\Indexer\Eav\DecimalFactory',
            array('create'),
            array(),
            '',
            false
        );
        $this->_eavSourceFactoryMock = $this->getMock(
            'Magento\Catalog\Model\Resource\Product\Indexer\Eav\SourceFactory',
            array('create'),
            array(),
            '',
            false
        );

        $this->_model = $this->getMockForAbstractClass(
            'Magento\Catalog\Model\Indexer\Product\Eav\AbstractAction',
            array($this->_eavDecimalFactoryMock, $this->_eavSourceFactoryMock)
        );
    }

    public function testGetIndexers()
    {
        $expectedIndexers = array(
            'source' => 'source_instance',
            'decimal' => 'decimal_instance'
        );

        $this->_eavSourceFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($expectedIndexers['source']));

        $this->_eavDecimalFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($expectedIndexers['decimal']));

        $this->assertEquals($expectedIndexers, $this->_model->getIndexers());
    }

    /**
     * @expectedException \Magento\Framework\Model\Exception
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
        $eavSource = $this->getMockBuilder('Magento\Catalog\Model\Resource\Product\Indexer\Eav\Source')
            ->disableOriginalConstructor()
            ->getMock();

        $eavDecimal = $this->getMockBuilder('Magento\Catalog\Model\Resource\Product\Indexer\Eav\Decimal')
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
        $ids = array(1, 2, 3);

        $eavSource = $this->getMockBuilder('Magento\Catalog\Model\Resource\Product\Indexer\Eav\Source')
            ->disableOriginalConstructor()
            ->getMock();

        $eavDecimal = $this->getMockBuilder('Magento\Catalog\Model\Resource\Product\Indexer\Eav\Decimal')
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
