<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backup\Test\Unit\Model;

use Magento\Backup\Model\Backup;
use Magento\Backup\Model\BackupFactory;
use Magento\Backup\Model\Fs\Collection;
use Magento\Framework\DataObject;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\TestCase;

class BackupFactoryTest extends TestCase
{
    /**
     * @var BackupFactory
     */
    protected $_instance;

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var Collection
     */
    protected $_fsCollection;

    /**
     * @var Backup
     */
    protected $_backupModel;

    /**
     * @var array
     */
    protected $_data;

    protected function setUp(): void
    {
        $this->_data = [
            'id' => '1385661590_snapshot',
            'time' => 1385661590,
            'path' => 'C:\test\test\var\backups',
            'name' => '',
            'type' => 'snapshot',
        ];
        $this->_fsCollection = $this->createMock(Collection::class);
        $this->_fsCollection->expects(
            $this->at(0)
        )->method(
            'getIterator'
        )->willReturn(
            new \ArrayIterator([new DataObject($this->_data)])
        );

        $this->_backupModel = $this->createMock(Backup::class);

        $this->_objectManager = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->_objectManager->expects(
            $this->at(0)
        )->method(
            'create'
        )->with(
            Collection::class
        )->willReturn(
            $this->_fsCollection
        );
        $this->_objectManager->expects(
            $this->at(1)
        )->method(
            'create'
        )->with(
            Backup::class
        )->willReturn(
            $this->_backupModel
        );

        $this->_instance = new BackupFactory($this->_objectManager);
    }

    public function testCreate()
    {
        $this->_backupModel->expects($this->once())
            ->method('setType')
            ->with($this->_data['type'])
            ->willReturnSelf();

        $this->_backupModel->expects($this->once())
            ->method('setTime')
            ->with($this->_data['time'])
            ->willReturnSelf();

        $this->_backupModel->expects($this->once())
            ->method('setName')
            ->with($this->_data['name'])
            ->willReturnSelf();

        $this->_backupModel->expects($this->once())
            ->method('setPath')
            ->with($this->_data['path'])
            ->willReturnSelf();

        $this->_backupModel->expects($this->once())
            ->method('setData')
            ->willReturnSelf();

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
