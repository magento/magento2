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
namespace Magento\Indexer\Model;

class ProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Indexer\Model\Processor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $model;

    /**
     * @var \Magento\Indexer\Model\ConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    /**
     * @var \Magento\Indexer\Model\IndexerFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $indexerFactoryMock;

    /**
     * @var \Magento\Indexer\Model\Indexer\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $indexersFactoryMock;

    /**
     * @var \Magento\Framework\Mview\ProcessorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $viewProcessorMock;

    protected function setUp()
    {
        $this->configMock = $this->getMockForAbstractClass(
            'Magento\Indexer\Model\ConfigInterface',
            array(),
            '',
            false,
            false,
            true,
            array('getIndexers')
        );
        $this->indexerFactoryMock = $this->getMock(
            'Magento\Indexer\Model\IndexerFactory',
            array('create'),
            array(),
            '',
            false
        );
        $this->indexersFactoryMock = $this->getMock(
            'Magento\Indexer\Model\Indexer\CollectionFactory',
            array('create'),
            array(),
            '',
            false
        );
        $this->viewProcessorMock = $this->getMockForAbstractClass(
            'Magento\Framework\Mview\ProcessorInterface',
            array(),
            '',
            false
        );
        $this->model = new \Magento\Indexer\Model\Processor(
            $this->configMock,
            $this->indexerFactoryMock,
            $this->indexersFactoryMock,
            $this->viewProcessorMock
        );
    }

    public function testReindexAllInvalid()
    {
        $indexers = array('indexer1' => array(), 'indexer2' => array());

        $this->configMock->expects($this->once())->method('getIndexers')->will($this->returnValue($indexers));

        $state1Mock = $this->getMock(
            'Magento\Indexer\Model\Indexer\State',
            array('getStatus', '__wakeup'),
            array(),
            '',
            false
        );
        $state1Mock->expects(
            $this->once()
        )->method(
            'getStatus'
        )->will(
            $this->returnValue(Indexer\State::STATUS_INVALID)
        );
        $indexer1Mock = $this->getMock(
            'Magento\Indexer\Model\Indexer',
            array('load', 'getState', 'reindexAll'),
            array(),
            '',
            false
        );
        $indexer1Mock->expects($this->once())->method('getState')->will($this->returnValue($state1Mock));
        $indexer1Mock->expects($this->once())->method('reindexAll');

        $state2Mock = $this->getMock(
            'Magento\Indexer\Model\Indexer\State',
            array('getStatus', '__wakeup'),
            array(),
            '',
            false
        );
        $state2Mock->expects(
            $this->once()
        )->method(
            'getStatus'
        )->will(
            $this->returnValue(Indexer\State::STATUS_VALID)
        );
        $indexer2Mock = $this->getMock(
            'Magento\Indexer\Model\Indexer',
            array('load', 'getState', 'reindexAll'),
            array(),
            '',
            false
        );
        $indexer2Mock->expects($this->never())->method('reindexAll');
        $indexer2Mock->expects($this->once())->method('getState')->will($this->returnValue($state2Mock));

        $this->indexerFactoryMock->expects($this->at(0))->method('create')->will($this->returnValue($indexer1Mock));
        $this->indexerFactoryMock->expects($this->at(1))->method('create')->will($this->returnValue($indexer2Mock));

        $this->model->reindexAllInvalid();
    }

    public function testReindexAll()
    {
        $indexerMock = $this->getMock('Magento\Indexer\Model\Indexer', array(), array(), '', false);
        $indexerMock->expects($this->exactly(2))->method('reindexAll');
        $indexers = array($indexerMock, $indexerMock);

        $indexersMock = $this->getMock('Magento\Indexer\Model\Indexer\Collection', array(), array(), '', false);
        $this->indexersFactoryMock->expects($this->once())->method('create')->will($this->returnValue($indexersMock));
        $indexersMock->expects($this->once())->method('getItems')->will($this->returnValue($indexers));

        $this->model->reindexAll();
    }
}
