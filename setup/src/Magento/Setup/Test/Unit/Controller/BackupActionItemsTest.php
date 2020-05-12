<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Controller;

use Laminas\Http\PhpEnvironment\Request;
use Laminas\Http\PhpEnvironment\Response;
use Laminas\Mvc\MvcEvent;
use Laminas\Mvc\Router\RouteMatch;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Backup\Filesystem;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Setup\BackupRollback;
use Magento\Setup\Controller\BackupActionItems;
use Magento\Setup\Controller\ResponseTypeInterface;
use Magento\Setup\Model\ObjectManagerProvider;
use Magento\Setup\Model\WebLogger;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BackupActionItemsTest extends TestCase
{
    /**
     * @var ObjectManagerProvider|MockObject
     */
    private $objectManagerProvider;

    /**
     * @var WebLogger|MockObject
     */
    private $log;

    /**
     * @var BackupRollback|MockObject
     */
    private $backupRollback;

    /**
     * @var DirectoryList|MockObject
     */
    private $directoryList;

    /**
     * @var Filesystem|MockObject
     */
    private $filesystem;

    /**
     * Controller
     *
     * @var BackupActionItems
     */
    private $controller;

    protected function setUp(): void
    {
        $this->directoryList =
            $this->createMock(DirectoryList::class);
        $this->objectManagerProvider =
            $this->createMock(ObjectManagerProvider::class);
        $this->backupRollback =
            $this->createPartialMock(BackupRollback::class, ['getDBDiskSpace', 'dbBackup']);
        $objectManager = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $objectManager->expects($this->once())->method('create')->willReturn($this->backupRollback);
        $this->objectManagerProvider->expects($this->once())->method('get')->willReturn($objectManager);
        $this->log = $this->createMock(WebLogger::class);
        $this->filesystem = $this->createMock(Filesystem::class);

        $this->controller = new BackupActionItems(
            $this->objectManagerProvider,
            $this->log,
            $this->directoryList,
            $this->filesystem
        );

        $request = $this->createMock(Request::class);
        $response = $this->createMock(Response::class);
        $routeMatch = $this->createMock(RouteMatch::class);

        $mvcEvent = $this->createMock(MvcEvent::class);
        $mvcEvent->expects($this->any())->method('setRequest')->with($request)->willReturn($mvcEvent);
        $mvcEvent->expects($this->any())->method('setResponse')->with($response)->willReturn($mvcEvent);
        $mvcEvent->expects($this->any())->method('setTarget')->with($this->controller)->willReturn($mvcEvent);
        $mvcEvent->expects($this->any())->method('getRouteMatch')->willReturn($routeMatch);
        $mvcEvent->expects($this->any())->method('getName')->willReturn('dispatch');

        $contentArray = '{"options":{"code":false,"media":false,"db":true}}';
        $request->expects($this->any())->method('getContent')->willReturn($contentArray);

        $this->controller->setEvent($mvcEvent);
        $this->controller->dispatch($request, $response);
    }

    public function testCheckAction()
    {
        $this->backupRollback->expects($this->once())->method('getDBDiskSpace')->willReturn(500);
        $this->directoryList->expects($this->once())->method('getPath')->willReturn(__DIR__);
        $this->filesystem->expects($this->once())->method('validateAvailableDiscSpace');
        $jsonModel = $this->controller->checkAction();
        $this->assertInstanceOf(JsonModel::class, $jsonModel);
        $variables = $jsonModel->getVariables();
        $this->assertArrayHasKey('responseType', $variables);
        $this->assertEquals(ResponseTypeInterface::RESPONSE_TYPE_SUCCESS, $variables['responseType']);
        $this->assertArrayHasKey('size', $variables);
        $this->assertTrue($variables['size']);
    }

    public function testCheckActionWithError()
    {
        $this->directoryList->expects($this->once())->method('getPath')->willReturn(__DIR__);
        $this->filesystem->expects($this->once())->method('validateAvailableDiscSpace')->willThrowException(
            new \Exception("Test error message")
        );
        $jsonModel = $this->controller->checkAction();
        $this->assertInstanceOf(JsonModel::class, $jsonModel);
        $variables = $jsonModel->getVariables();
        $this->assertArrayHasKey('responseType', $variables);
        $this->assertEquals(ResponseTypeInterface::RESPONSE_TYPE_ERROR, $variables['responseType']);
        $this->assertArrayHasKey('error', $variables);
        $this->assertEquals("Test error message", $variables['error']);
    }

    public function testCreateAction()
    {
        $this->backupRollback->expects($this->once())->method('dbBackup')->willReturn('backup/path/');
        $jsonModel = $this->controller->createAction();
        $this->assertInstanceOf(JsonModel::class, $jsonModel);
        $variables = $jsonModel->getVariables();
        $this->assertArrayHasKey('responseType', $variables);
        $this->assertEquals(ResponseTypeInterface::RESPONSE_TYPE_SUCCESS, $variables['responseType']);
        $this->assertArrayHasKey('files', $variables);
        $this->assertEquals(['backup/path/'], $variables['files']);
    }

    public function testIndexAction()
    {
        $model = $this->controller->indexAction();
        $this->assertInstanceOf(ViewModel::class, $model);
    }
}
