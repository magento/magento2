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
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Setup\SampleData\State;
use Magento\Setup\Controller\Install;
use Magento\Setup\Model\Installer;
use Magento\Setup\Model\Installer\Progress;
use Magento\Setup\Model\Installer\ProgressFactory;
use Magento\Setup\Model\InstallerFactory;
use Magento\Setup\Model\RequestDataConverter;
use Magento\Setup\Model\WebLogger;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InstallTest extends TestCase
{
    /**
     * @var MockObject|WebLogger
     */
    private $webLogger;

    /**
     * @var MockObject|Installer
     */
    private $installer;

    /**
     * @var MockObject|ProgressFactory
     */
    private $progressFactory;

    /**
     * @var MockObject|RequestDataConverter
     */
    private $requestDataConverter;

    /**
     * @var Install
     */
    private $controller;

    /**
     * @var State|MockObject
     */
    private $sampleDataState;

    /**
     * @var DeploymentConfig|MockObject
     */
    private $deploymentConfig;

    protected function setUp(): void
    {
        $this->webLogger = $this->createMock(WebLogger::class);
        $installerFactory = $this->createMock(InstallerFactory::class);
        $this->installer = $this->createMock(Installer::class);
        $this->progressFactory =
            $this->createMock(ProgressFactory::class);
        $this->sampleDataState = $this->createMock(State::class);
        $this->deploymentConfig = $this->createMock(DeploymentConfig::class);
        $this->requestDataConverter = $this->createMock(RequestDataConverter::class);

        $installerFactory->expects($this->once())->method('create')->with($this->webLogger)
            ->willReturn($this->installer);
        $this->controller = new Install(
            $this->webLogger,
            $installerFactory,
            $this->progressFactory,
            $this->sampleDataState,
            $this->deploymentConfig,
            $this->requestDataConverter
        );
    }

    public function testIndexAction()
    {
        $viewModel = $this->controller->indexAction();
        $this->assertInstanceOf(ViewModel::class, $viewModel);
        $this->assertTrue($viewModel->terminate());
    }

    public function testStartAction()
    {
        $this->webLogger->expects($this->once())->method('clear');
        $this->installer->expects($this->once())->method('install');
        $this->installer->expects($this->exactly(2))
            ->method('getInstallInfo')
            ->willReturn(
                [
                    'key' => null,
                    'message' => null,
                ]
            );
        $this->deploymentConfig->expects($this->once())->method('isAvailable')->willReturn(false);
        $jsonModel = $this->controller->startAction();
        $this->assertInstanceOf(JsonModel::class, $jsonModel);
        $variables = $jsonModel->getVariables();
        $this->assertArrayHasKey('key', $variables);
        $this->assertArrayHasKey('success', $variables);
        $this->assertArrayHasKey('messages', $variables);
        $this->assertTrue($variables['success']);
    }

    public function testStartActionPriorInstallException()
    {
        $this->webLogger->expects($this->once())->method('clear');
        $this->installer->expects($this->never())->method('install');
        $this->installer->expects($this->never())->method('getInstallInfo');
        $this->deploymentConfig->expects($this->once())->method('isAvailable')->willReturn(true);
        $jsonModel = $this->controller->startAction();
        $this->assertInstanceOf(JsonModel::class, $jsonModel);
        $variables = $jsonModel->getVariables();
        $this->assertArrayHasKey('success', $variables);
        $this->assertArrayHasKey('messages', $variables);
        $this->assertFalse($variables['success']);
    }

    public function testStartActionInstallException()
    {
        $this->webLogger->expects($this->once())->method('clear');
        $this->deploymentConfig->expects($this->once())->method('isAvailable')->willReturn(false);
        $this->installer->expects($this->once())->method('install')
            ->willThrowException($this->createMock('\Exception'));
        $jsonModel = $this->controller->startAction();
        $this->assertNull($jsonModel->getVariable('isSampleDataError'));
    }

    public function testStartActionWithSampleDataError()
    {
        $this->webLogger->expects($this->once())->method('clear');
        $this->webLogger->expects($this->never())->method('logError');
        $this->deploymentConfig->expects($this->once())->method('isAvailable')->willReturn(false);
        $this->installer->method('install');
        $this->installer->expects($this->exactly(2))
            ->method('getInstallInfo')
            ->willReturn(
                [
                    'key' => null,
                    'message' => null,
                ]
            );
        $this->sampleDataState->expects($this->once())->method('hasError')->willReturn(true);
        $jsonModel = $this->controller->startAction();
        $this->assertInstanceOf(JsonModel::class, $jsonModel);
        $variables = $jsonModel->getVariables();
        $this->assertArrayHasKey('success', $variables);
        $this->assertTrue($variables['success']);
        $this->assertTrue($jsonModel->getVariable('isSampleDataError'));
    }

    public function testProgressAction()
    {
        $numValue = 42;
        $consoleMessages = ['key1' => 'log message 1', 'key2' => 'log message 2'];

        $this->webLogger->expects($this->once())->method('logfileExists')->willReturn(true);
        $progress = $this->createMock(Progress::class);
        $this->progressFactory->expects($this->once())->method('createFromLog')->with($this->webLogger)
            ->willReturn($progress);
        $progress->expects($this->once())->method('getRatio')->willReturn($numValue);
        $this->webLogger->expects($this->once())->method('get')->willReturn($consoleMessages);
        $jsonModel = $this->controller->progressAction();
        $this->assertInstanceOf(JsonModel::class, $jsonModel);
        $variables = $jsonModel->getVariables();
        $this->assertArrayHasKey('progress', $variables);
        $this->assertArrayHasKey('success', $variables);
        $this->assertArrayHasKey('console', $variables);
        $this->assertSame($consoleMessages, $variables['console']);
        $this->assertTrue($variables['success']);
        $this->assertSame(sprintf('%d', $numValue * 100), $variables['progress']);
    }

    public function testProgressActionWithError()
    {
        $e = 'Some exception message';
        $this->webLogger->expects($this->once())->method('logfileExists')->willReturn(true);
        $this->progressFactory->expects($this->once())->method('createFromLog')
            ->willThrowException(new \LogicException($e));
        $jsonModel = $this->controller->progressAction();
        $this->assertInstanceOf(JsonModel::class, $jsonModel);
        $variables = $jsonModel->getVariables();
        $this->assertArrayHasKey('success', $variables);
        $this->assertArrayHasKey('console', $variables);
        $this->assertFalse($variables['success']);
        $this->assertStringContainsString('LogicException', $variables['console'][0]);
        $this->assertStringContainsString($e, $variables['console'][0]);
    }

    public function testProgressActionWithSampleDataError()
    {
        $numValue = 42;
        $this->webLogger->expects($this->once())->method('logfileExists')->willReturn(true);
        $progress = $this->createMock(Progress::class);
        $progress->expects($this->once())->method('getRatio')->willReturn($numValue);
        $this->progressFactory->expects($this->once())->method('createFromLog')->willReturn($progress);
        $this->sampleDataState->expects($this->once())->method('hasError')->willReturn(true);
        $jsonModel = $this->controller->progressAction();
        $this->assertInstanceOf(JsonModel::class, $jsonModel);
        $variables = $jsonModel->getVariables();
        $this->assertArrayHasKey('success', $variables);
        $this->assertArrayHasKey('console', $variables);
        $this->assertTrue($variables['success']);
        $this->assertTrue($jsonModel->getVariable('isSampleDataError'));
        $this->assertSame(sprintf('%d', $numValue * 100), $variables['progress']);
    }

    public function testProgressActionNoInstallLogFile()
    {
        $this->webLogger->expects($this->once())->method('logfileExists')->willReturn(false);
        $jsonModel = $this->controller->progressAction();
        $this->assertInstanceOf(JsonModel::class, $jsonModel);
        $variables = $jsonModel->getVariables();
        $this->assertArrayHasKey('success', $variables);
        $this->assertArrayHasKey('console', $variables);
        $this->assertTrue($variables['success']);
        $this->assertEmpty($variables['console']);
        $this->assertSame(0, $variables['progress']);
    }

    public function testDispatch()
    {
        $request = $this->createMock(Request::class);
        $response = $this->createMock(Response::class);
        $routeMatch = $this->createMock(RouteMatch::class);

        $mvcEvent = $this->createMock(MvcEvent::class);
        $mvcEvent->expects($this->once())->method('setRequest')->with($request)->willReturn($mvcEvent);
        $mvcEvent->expects($this->once())->method('setResponse')->with($response)->willReturn($mvcEvent);
        $mvcEvent->expects($this->once())->method('setTarget')->with($this->controller)->willReturn($mvcEvent);
        $mvcEvent->expects($this->any())->method('getRouteMatch')->willReturn($routeMatch);
        $mvcEvent->expects($this->any())->method('getName')->willReturn('dispatch');

        $contentArray = '{"config": { "address": { "base_url": "http://123.45.678.12"}}}';
        $request->expects($this->any())->method('getContent')->willReturn($contentArray);
        $this->controller->setEvent($mvcEvent);
        $this->controller->dispatch($request, $response);
        $this->controller->startAction();
    }
}
