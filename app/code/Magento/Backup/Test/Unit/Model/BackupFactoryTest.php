<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backup\Test\Unit\Model;

class BackupFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Backup\Model\BackupFactory
     */
    protected $_instance;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Backup\Model\Fs\Collection
     */
    protected $_fsCollection;

    /**
     * @var \Magento\Backup\Model\Backup
     */
    protected $_backupModel;

    /**
     * @var array
     */
    protected $_data;

    protected function setUp()
    {
        $this->_data = [
            'id' => '1385661590_snapshot',
            'time' => 1385661590,
            'path' => 'C:\test\test\var\backups',
            'name' => '',
            'type' => 'snapshot',
        ];
        $this->_fsCollection = $this->createMock(\Magento\Backup\Model\Fs\Collection::class);
        $this->_fsCollection->expects(
            $this->at(0)
        )->method(
            'getIterator'
        )->will(
            $this->returnValue(new \ArrayIterator([new \Magento\Framework\DataObject($this->_data)]))
        );

        $this->_backupModel = $this->createMock(\Magento\Backup\Model\Backup::class);

        $this->_objectManager = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $this->_objectManager->expects(
            $this->at(0)
        )->method(
            'create'
        )->with(
            \Magento\Backup\Model\Fs\Collection::class
        )->will(
            $this->returnValue($this->_fsCollection)
        );
        $this->_objectManager->expects(
            $this->at(1)
        )->method(
            'create'
        )->with(
            \Magento\Backup\Model\Backup::class
        )->will(
            $this->returnValue($this->_backupModel)
        );

        $this->_instance = new \Magento\Backup\Model\BackupFactory($this->_objectManager);
    }

    public function testCreate()
    {
        $this->_backupModel->expects($this->once())
            ->method('setType')
            ->with($this->_data['type'])
            ->will($this->returnSelf());

        $this->_backupModel->expects($this->once())
            ->method('setTime')
            ->with($this->_data['time'])
            ->will($this->returnSelf());

        $this->_backupModel->expects($this->once())
            ->method('setName')
            ->with($this->_data['name'])
            ->will($this->returnSelf());

        $this->_backupModel->expects($this->once())
            ->method('setPath')
            ->with($this->_data['path'])
            ->will($this->returnSelf());

        $this->_backupModel->expects($this->once())
            ->method('setData')
            ->will($this->returnSelf());

        $this->_instance->create('1385661590', 'snapshot');
    }

    public function testCreateInvalid()
    {
        $this->_backupModel->expects($this->never())->method('setType');
        $this->_backupModel->expects($this->never())->method('setTime');
        $this->_backupModel->expects($this->never())->method('setName');
        $this->_backupModel->expects($this->never())->method('setPath');

        $this->_instance->create('451094400', 'snapshot');
    }
}
