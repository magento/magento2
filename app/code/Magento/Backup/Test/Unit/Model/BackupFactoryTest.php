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
    protected $instance;

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var Collection
     */
    protected $fsCollection;

    /**
     * @var Backup
     */
    protected $backupModel;

    /**
     * @var array
     */
    protected $data;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->data = [
            'id' => '1385661590_snapshot',
            'time' => 1385661590,
            'path' => 'C:\test\test\var\backups',
            'name' => '',
            'type' => 'snapshot'
        ];
        $this->fsCollection = $this->createMock(Collection::class);
        $this->fsCollection
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([new DataObject($this->data)]));

        $this->backupModel = $this->createMock(Backup::class);

        $this->objectManager = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->objectManager
            ->method('create')
            ->willReturnCallback(function ($arg1, $arg2) {
                if ($arg1 == Collection::class) {
                    return $this->fsCollection;
                } elseif ($arg1 == Backup::class) {
                    return $this->backupModel;
                }
            });

        $this->instance = new BackupFactory($this->objectManager);
    }

    /**
     * @return void
     */
    public function testCreate(): void
    {
        $this->backupModel->expects($this->once())
            ->method('setType')
            ->with($this->data['type'])
            ->willReturnSelf();

        $this->backupModel->expects($this->once())
            ->method('setTime')
            ->with($this->data['time'])
            ->willReturnSelf();

        $this->backupModel->expects($this->once())
            ->method('setName')
            ->with($this->data['name'])
            ->willReturnSelf();

        $this->backupModel->expects($this->once())
            ->method('setPath')
            ->with($this->data['path'])
            ->willReturnSelf();

        $this->backupModel->expects($this->once())
            ->method('setData')
            ->willReturnSelf();

        $this->instance->create('1385661590', 'snapshot');
    }

    /**
     * @return void
     */
    public function testCreateInvalid(): void
    {
        $this->backupModel->expects($this->never())->method('setType');
        $this->backupModel->expects($this->never())->method('setTime');
        $this->backupModel->expects($this->never())->method('setName');
        $this->backupModel->expects($this->never())->method('setPath');

        $this->instance->create('451094400', 'snapshot');
    }
}
