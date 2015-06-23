<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup\Test\Unit;

use Magento\Framework\Setup\BackupRollbackFactory;

class BackupRollbackFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManager;

    /**
     * @var \Magento\Framework\Setup\BackupRollback|\PHPUnit_Framework_MockObject_MockObject
     */
    private $factory;

    public function setUp()
    {
        $this->objectManager = $this->getMockForAbstractClass(
            'Magento\Framework\ObjectManagerInterface', [], '',
            false
        );
        $this->factory = $this->getMock('Magento\Framework\Setup\BackupRollback', [], [], '', false);

    }

    public function testCreateOutputInterface()
    {
        $consoleLogger = $this->getMock('Magento\Framework\Setup\ConsoleLogger', [], [], '', false);
        $output = $this->getMockForAbstractClass(
            'Symfony\Component\Console\Output\OutputInterface',
            [],
            '',
            false
        );
        $this->objectManager->expects($this->exactly(2))
            ->method('create')
            ->will($this->returnValueMap([
                ['Magento\Framework\Setup\ConsoleLogger', ['output' => $output], $consoleLogger],
                ['Magento\Framework\Setup\BackupRollback', ['log' => $consoleLogger], $this->factory],
            ]));
        $model = new BackupRollbackFactory($this->objectManager);
        $this->assertInstanceOf('Magento\Framework\Setup\BackupRollback', $model->create($output));
    }

    public function testCreateLoggerInterface()
    {
        $logger = $this->getMock('Magento\Framework\Setup\LoggerInterface', [], [], '', false);
        $this->objectManager->expects($this->once())
            ->method('create')
            ->will($this->returnValueMap([
                ['Magento\Framework\Setup\BackupRollback', ['log' => $logger], $this->factory],
            ]));
        $model = new BackupRollbackFactory($this->objectManager);
        $this->assertInstanceOf('Magento\Framework\Setup\BackupRollback', $model->create($logger));
    }
}
