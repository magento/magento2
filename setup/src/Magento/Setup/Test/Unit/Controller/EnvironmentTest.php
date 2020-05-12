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
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Setup\FilePermissions;
use Magento\Setup\Controller\Environment;
use Magento\Setup\Controller\ReadinessCheckInstaller;
use Magento\Setup\Controller\ReadinessCheckUpdater;
use Magento\Setup\Controller\ResponseTypeInterface;
use Magento\Setup\Model\CronScriptReadinessCheck;
use Magento\Setup\Model\PhpReadinessCheck;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EnvironmentTest extends TestCase
{
    /**
     * @var FilePermissions|MockObject
     */
    private $permissions;

    /**
     * @var Filesystem|MockObject
     */
    private $filesystem;

    /**
     * @var CronScriptReadinessCheck|MockObject
     */
    private $cronScriptReadinessCheck;

    /**
     * @var PhpReadinessCheck|MockObject
     */
    private $phpReadinessCheck;

    /**
     * @var Environment
     */
    private $environment;

    protected function setUp(): void
    {
        $this->filesystem = $this->createMock(Filesystem::class);
        $this->permissions = $this->createMock(FilePermissions::class);
        $this->cronScriptReadinessCheck = $this->createMock(CronScriptReadinessCheck::class);
        $this->phpReadinessCheck = $this->createMock(PhpReadinessCheck::class);
        $this->environment = new Environment(
            $this->permissions,
            $this->filesystem,
            $this->cronScriptReadinessCheck,
            $this->phpReadinessCheck
        );
    }

    public function testFilePermissionsInstaller()
    {
        $request = $this->createMock(Request::class);
        $response = $this->createMock(Response::class);
        $routeMatch = $this->createMock(RouteMatch::class);

        $mvcEvent = $this->getMvcEventMock($request, $response, $routeMatch);

        $this->permissions->expects($this->once())->method('getMissingWritablePathsForInstallation');
        $this->environment->setEvent($mvcEvent);
        $this->environment->dispatch($request, $response);
        $this->environment->filePermissionsAction();
    }

    public function testPhpVersionActionInstaller()
    {
        $request = $this->createMock(Request::class);
        $response = $this->createMock(Response::class);
        $routeMatch = $this->createMock(RouteMatch::class);

        $mvcEvent = $this->getMvcEventMock($request, $response, $routeMatch);

        $request->expects($this->once())->method('getQuery')->willReturn(ReadinessCheckInstaller::INSTALLER);
        $this->phpReadinessCheck->expects($this->once())->method('checkPhpVersion');
        $this->environment->setEvent($mvcEvent);
        $this->environment->dispatch($request, $response);
        $this->environment->phpVersionAction();
    }

    public function testPhpVersionActionUpdater()
    {
        $request = $this->createMock(Request::class);
        $response = $this->createMock(Response::class);
        $routeMatch = $this->createMock(RouteMatch::class);

        $mvcEvent = $this->getMvcEventMock($request, $response, $routeMatch);

        $request->expects($this->once())->method('getQuery')->willReturn(ReadinessCheckUpdater::UPDATER);
        $this->phpReadinessCheck->expects($this->never())->method('checkPhpVersion');
        $read =
            $this->getMockForAbstractClass(ReadInterface::class, [], '', false);
        $this->filesystem->expects($this->once())->method('getDirectoryRead')->willReturn($read);
        $read->expects($this->once())
            ->method('readFile')
            ->willReturn('');
        $this->environment->setEvent($mvcEvent);
        $this->environment->dispatch($request, $response);
        $this->environment->phpVersionAction();
    }

    public function testPhpSettingsActionInstaller()
    {
        $request = $this->createMock(Request::class);
        $response = $this->createMock(Response::class);
        $routeMatch = $this->createMock(RouteMatch::class);

        $mvcEvent = $this->getMvcEventMock($request, $response, $routeMatch);

        $request->expects($this->once())->method('getQuery')->willReturn(ReadinessCheckInstaller::INSTALLER);
        $this->phpReadinessCheck->expects($this->once())->method('checkPhpSettings');
        $this->environment->setEvent($mvcEvent);
        $this->environment->dispatch($request, $response);
        $this->environment->phpSettingsAction();
    }

    public function testPhpSettingsActionUpdater()
    {
        $request = $this->createMock(Request::class);
        $response = $this->createMock(Response::class);
        $routeMatch = $this->createMock(RouteMatch::class);

        $mvcEvent = $this->getMvcEventMock($request, $response, $routeMatch);

        $request->expects($this->once())->method('getQuery')->willReturn(ReadinessCheckUpdater::UPDATER);
        $this->phpReadinessCheck->expects($this->never())->method('checkPhpSettings');
        $read =
            $this->getMockForAbstractClass(ReadInterface::class, [], '', false);
        $this->filesystem->expects($this->once())->method('getDirectoryRead')->willReturn($read);
        $read->expects($this->once())
            ->method('readFile')
            ->willReturn('');
        $this->environment->setEvent($mvcEvent);
        $this->environment->dispatch($request, $response);
        $this->environment->phpSettingsAction();
    }

    public function testPhpExtensionsActionInstaller()
    {
        $request = $this->createMock(Request::class);
        $response = $this->createMock(Response::class);
        $routeMatch = $this->createMock(RouteMatch::class);

        $mvcEvent = $this->getMvcEventMock($request, $response, $routeMatch);

        $request->expects($this->once())->method('getQuery')->willReturn(ReadinessCheckInstaller::INSTALLER);
        $this->phpReadinessCheck->expects($this->once())->method('checkPhpExtensions');
        $this->environment->setEvent($mvcEvent);
        $this->environment->dispatch($request, $response);
        $this->environment->phpExtensionsAction();
    }

    public function testPhpExtensionsActionUpdater()
    {
        $request = $this->createMock(Request::class);
        $response = $this->createMock(Response::class);
        $routeMatch = $this->createMock(RouteMatch::class);

        $mvcEvent = $this->getMvcEventMock($request, $response, $routeMatch);

        $request->expects($this->once())->method('getQuery')->willReturn(ReadinessCheckUpdater::UPDATER);
        $this->phpReadinessCheck->expects($this->never())->method('checkPhpExtensions');
        $read =
            $this->getMockForAbstractClass(ReadInterface::class, [], '', false);
        $this->filesystem->expects($this->once())->method('getDirectoryRead')->willReturn($read);
        $read->expects($this->once())
            ->method('readFile')
            ->willReturn('');
        $this->environment->setEvent($mvcEvent);
        $this->environment->dispatch($request, $response);
        $this->environment->phpExtensionsAction();
    }

    public function testCronScriptAction()
    {
        $this->cronScriptReadinessCheck->expects($this->once())->method('checkSetup')->willReturn(['success' => true]);
        $this->cronScriptReadinessCheck->expects($this->once())
            ->method('checkUpdater')
            ->willReturn(['success' => true]);
        $expected = new JsonModel(['responseType' => ResponseTypeInterface::RESPONSE_TYPE_SUCCESS]);
        $this->assertEquals($expected, $this->environment->cronScriptAction());
    }

    public function testCronScriptActionSetupFailed()
    {
        $this->cronScriptReadinessCheck->expects($this->once())
            ->method('checkSetup')
            ->willReturn(['success' => false, 'error' => 'error message setup']);
        $this->cronScriptReadinessCheck->expects($this->once())
            ->method('checkUpdater')
            ->willReturn(['success' => true]);
        $expected = new JsonModel(
            [
                'responseType' => ResponseTypeInterface::RESPONSE_TYPE_ERROR,
                'setupErrorMessage' => 'Error from Setup Application Cron Script:<br/>error message setup'
            ]
        );
        $this->assertEquals($expected, $this->environment->cronScriptAction());
    }

    public function testCronScriptActionUpdaterFailed()
    {
        $this->cronScriptReadinessCheck->expects($this->once())->method('checkSetup')->willReturn(['success' => true]);
        $this->cronScriptReadinessCheck->expects($this->once())
            ->method('checkUpdater')
            ->willReturn(['success' => false, 'error' => 'error message updater']);
        $expected = new JsonModel(
            [
                'responseType' => ResponseTypeInterface::RESPONSE_TYPE_ERROR,
                'updaterErrorMessage' => 'Error from Updater Application Cron Script:<br/>error message updater'
            ]
        );
        $this->assertEquals($expected, $this->environment->cronScriptAction());
    }

    public function testCronScriptActionBothFailed()
    {
        $this->cronScriptReadinessCheck->expects($this->once())
            ->method('checkSetup')
            ->willReturn(['success' => false, 'error' => 'error message setup']);
        $this->cronScriptReadinessCheck->expects($this->once())
            ->method('checkUpdater')
            ->willReturn(['success' => false, 'error' => 'error message updater']);
        $expected = new JsonModel(
            [
                'responseType' => ResponseTypeInterface::RESPONSE_TYPE_ERROR,
                'setupErrorMessage' => 'Error from Setup Application Cron Script:<br/>error message setup',
                'updaterErrorMessage' => 'Error from Updater Application Cron Script:<br/>error message updater',
            ]
        );
        $this->assertEquals($expected, $this->environment->cronScriptAction());
    }

    public function testCronScriptActionSetupNotice()
    {
        $this->cronScriptReadinessCheck->expects($this->once())
            ->method('checkSetup')
            ->willReturn(['success' => true, 'notice' => 'notice setup']);
        $this->cronScriptReadinessCheck->expects($this->once())
            ->method('checkUpdater')
            ->willReturn(['success' => true]);
        $expected = new JsonModel(
            [
                'responseType' => ResponseTypeInterface::RESPONSE_TYPE_SUCCESS,
                'setupNoticeMessage' => 'Notice from Setup Application Cron Script:<br/>notice setup'
            ]
        );
        $this->assertEquals($expected, $this->environment->cronScriptAction());
    }

    public function testCronScriptActionUpdaterNotice()
    {
        $this->cronScriptReadinessCheck->expects($this->once())->method('checkSetup')->willReturn(['success' => true]);
        $this->cronScriptReadinessCheck->expects($this->once())
            ->method('checkUpdater')
            ->willReturn(['success' => true, 'notice' => 'notice updater']);
        $expected = new JsonModel(
            [
                'responseType' => ResponseTypeInterface::RESPONSE_TYPE_SUCCESS,
                'updaterNoticeMessage' => 'Notice from Updater Application Cron Script:<br/>notice updater'
            ]
        );
        $this->assertEquals($expected, $this->environment->cronScriptAction());
    }

    public function testCronScriptActionBothNotice()
    {
        $this->cronScriptReadinessCheck->expects($this->once())
            ->method('checkSetup')
            ->willReturn(['success' => true, 'notice' => 'notice setup']);
        $this->cronScriptReadinessCheck->expects($this->once())
            ->method('checkUpdater')
            ->willReturn(['success' => true, 'notice' => 'notice updater']);
        $expected = new JsonModel(
            [
                'responseType' => ResponseTypeInterface::RESPONSE_TYPE_SUCCESS,
                'setupNoticeMessage' => 'Notice from Setup Application Cron Script:<br/>notice setup',
                'updaterNoticeMessage' => 'Notice from Updater Application Cron Script:<br/>notice updater'
            ]
        );
        $this->assertEquals($expected, $this->environment->cronScriptAction());
    }

    public function testIndexAction()
    {
        $model = $this->environment->indexAction();
        $this->assertInstanceOf(JsonModel::class, $model);
    }

    /**
     * @param MockObject $request
     * @param MockObject $response
     * @param MockObject $routeMatch
     *
     * @return MockObject
     */
    protected function getMvcEventMock(
        MockObject $request,
        MockObject $response,
        MockObject $routeMatch
    ) {
        $mvcEvent = $this->createMock(MvcEvent::class);
        $mvcEvent->expects($this->once())->method('setRequest')->with($request)->willReturn($mvcEvent);
        $mvcEvent->expects($this->once())->method('setResponse')->with($response)->willReturn($mvcEvent);
        $mvcEvent->expects($this->once())->method('setTarget')->with($this->environment)->willReturn($mvcEvent);
        $mvcEvent->expects($this->any())->method('getRouteMatch')->willReturn($routeMatch);
        $mvcEvent->expects($this->any())->method('getName')->willReturn('dispatch');

        return $mvcEvent;
    }
}
