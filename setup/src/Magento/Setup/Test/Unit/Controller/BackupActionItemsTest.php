<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Controller;

use \Magento\Setup\Controller\BackupActionItems;
use \Magento\Setup\Controller\ResponseTypeInterface;

class BackupActionItemsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Setup\Model\ObjectManagerProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManagerProvider;

    /**
     * @var \Magento\Setup\Model\WebLogger|\PHPUnit_Framework_MockObject_MockObject
     */
    private $log;

    /**
     * @var \Magento\Framework\Setup\BackupRollback|\PHPUnit_Framework_MockObject_MockObject
     */
    private $backupRollback;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $directoryList;

    /**
     * @var \Magento\Framework\Backup\Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filesystem;

    /**
     * Controller
     *
     * @var \Magento\Setup\Controller\BackupActionItems
     */
    private $controller;

    public function setUp()
    {
        $this->directoryList = $this->getMock('Magento\Framework\App\Filesystem\DirectoryList', [], [], '', false);
        $this->objectManagerProvider = $this->getMock('Magento\Setup\Model\ObjectManagerProvider', [], [], '', false);
        $this->backupRollback = $this->getMock(
            'Magento\\Setup\Model\\BackupRollback',
            [],
            [],
            '',
            false
        );
        $objectManager = $this->getMock('Magento\Framework\ObjectManagerInterface', [], [], '', false);
        $objectManager->expects($this->once())->method('create')->willReturn($this->backupRollback);
        $this->objectManagerProvider->expects($this->once())->method('get')->willReturn($objectManager);
        $this->log = $this->getMock('Magento\Setup\Model\WebLogger', [], [], '', false);
        $this->filesystem = $this->getMock('Magento\Framework\Backup\Filesystem', [], [], '', false);
        $this->controller = new BackupActionItems(
            $this->objectManagerProvider,
            $this->log,
            $this->directoryList,
            $this->filesystem
        );
    }

    public function testCheckAction()
    {
        $this->directoryList->expects($this->once())->method('getPath')->willReturn(__DIR__);
        $this->filesystem->expects($this->once())->method('validateAvailableDiscSpace');
        $jsonModel = $this->controller->checkAction();
        $this->assertInstanceOf('Zend\View\Model\JsonModel', $jsonModel);
        $variables = $jsonModel->getVariables();
        $this->assertArrayHasKey('responseType', $variables);
        $this->assertEquals(ResponseTypeInterface::RESPONSE_TYPE_SUCCESS, $variables['responseType']);
        $this->assertArrayHasKey('size', $variables);
        $this->assertEquals(true, $variables['size']);
    }

    public function testCheckActionWithError()
    {
        $this->directoryList->expects($this->once())->method('getPath')->willReturn(__DIR__);
        $this->filesystem->expects($this->once())->method('validateAvailableDiscSpace')->will(
            $this->throwException(new \Exception("Test error message"))
        );
        $jsonModel = $this->controller->checkAction();
        $this->assertInstanceOf('Zend\View\Model\JsonModel', $jsonModel);
        $variables = $jsonModel->getVariables();
        $this->assertArrayHasKey('responseType', $variables);
        $this->assertEquals(ResponseTypeInterface::RESPONSE_TYPE_ERROR, $variables['responseType']);
        $this->assertArrayHasKey('error', $variables);
        $this->assertEquals("Test error message", $variables['error']);
    }

    public function testCreateAction()
    {
        $jsonModel = $this->controller->createAction();
        $this->assertInstanceOf('Zend\View\Model\JsonModel', $jsonModel);
        $variables = $jsonModel->getVariables();
        $this->assertArrayHasKey('responseType', $variables);
        $this->assertEquals(ResponseTypeInterface::RESPONSE_TYPE_SUCCESS, $variables['responseType']);
        $this->assertArrayHasKey('files', $variables);
        $this->assertEquals([], $variables['files']);
    }
}
