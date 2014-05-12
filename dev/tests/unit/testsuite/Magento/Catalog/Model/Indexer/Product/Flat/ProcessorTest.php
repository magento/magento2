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
namespace Magento\Catalog\Model\Indexer\Product\Flat;

class ProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Flat\Processor
     */
    protected $_model;

    /**
     * @var \Magento\Indexer\Model\Indexer|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_indexerMock;

    /**
     * @var \Magento\Indexer\Model\IndexerFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_indexerFactoryMock;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Flat\State|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_stateMock;

    public function setUp()
    {
        $this->_objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->_indexerMock = $this->getMock(
            'Magento\Indexer\Model\Indexer',
            array('getId', 'invalidate'),
            array(),
            '',
            false
        );
        $this->_indexerMock->expects($this->any())->method('getId')->will($this->returnValue(1));

        $this->_indexerFactoryMock = $this->getMock(
            'Magento\Indexer\Model\IndexerFactory', array('create'), array(), '', false
        );

        $this->_indexerFactoryMock
            ->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->_indexerMock));

        $this->_stateMock = $this->getMock(
            'Magento\Catalog\Model\Indexer\Product\Flat\State',
            array('isFlatEnabled'),
            array(),
            '',
            false
        );
        $this->_model = $this->_objectManager->getObject('Magento\Catalog\Model\Indexer\Product\Flat\Processor', array(
            'indexerFactory' => $this->_indexerFactoryMock,
            'state'  => $this->_stateMock
        ));
    }

    /**
     * Test get indexer instance
     */
    public function testGetIndexer()
    {
        $this->assertInstanceOf('\Magento\Indexer\Model\Indexer', $this->_model->getIndexer());
    }

    /**
     * Test mark indexer as invalid if enabled
     */
    public function testMarkIndexerAsInvalid()
    {
        $this->_stateMock->expects($this->once())->method('isFlatEnabled')->will($this->returnValue(true));
        $this->_indexerMock->expects($this->once())->method('invalidate');
        $this->_model->markIndexerAsInvalid();
    }

    /**
     * Test mark indexer as invalid if disabled
     */
    public function testMarkDisabledIndexerAsInvalid()
    {
        $this->_stateMock->expects($this->once())->method('isFlatEnabled')->will($this->returnValue(false));
        $this->_indexerMock->expects($this->never())->method('invalidate');
        $this->_model->markIndexerAsInvalid();
    }
}
