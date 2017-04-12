<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Controller;

use Magento\Setup\Controller\Environment;
use Magento\Setup\Controller\ReadinessCheckInstaller;
use Magento\Setup\Controller\ReadinessCheckUpdater;
use Magento\Setup\Controller\ResponseTypeInterface;
use Zend\View\Model\JsonModel;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EnvironmentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Setup\FilePermissions|\PHPUnit_Framework_MockObject_MockObject
     */
    private $permissions;

    /**
     * @var \Magento\Framework\Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filesystem;

    /**
     * @var \Magento\Setup\Model\CronScriptReadinessCheck|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cronScriptReadinessCheck;

    /**
     * @var \Magento\Setup\Model\PhpReadinessCheck|\PHPUnit_Framework_MockObject_MockObject
     */
    private $phpReadinessCheck;

    /**
     * @var Environment
     */
    private $environment;

    public function setUp()
    {
        $this->filesystem = $this->getMock(\Magento\Framework\Filesystem::class, [], [], '', false);
        $this->permissions = $this->getMock(\Magento\Framework\Setup\FilePermissions::class, [], [], '', false);
        $this->cronScriptReadinessCheck = $this->getMock(
            \Magento\Setup\Model\CronScriptReadinessCheck::class,
            [],
            [],
            '',
            false
        );
        $this->phpReadinessCheck = $this->getMock(\Magento\Setup\Model\PhpReadinessCheck::class, [], [], '', false);
        $this->environment = new Environment(
            $this->permissions,
            $this->filesystem,
            $this->cronScriptReadinessCheck,
            $this->phpReadinessCheck
        );
    }

    public function testFilePermissionsInstaller()
    {
        $request = $this->getMock(\Zend\Http\PhpEnvironment\Request::class, [], [], '', false);
        $response = $this->getMock(\Zend\Http\PhpEnvironment\Response::class, [], [], '', false);
        $routeMatch = $this->getMock(\Zend\Mvc\Router\RouteMatch::class, [], [], '', false);

        $mvcEvent = $this->getMock(\Zend\Mvc\MvcEvent::class, [], [], '', false);
        $mvcEvent->expects($this->once())->method('setRequest')->with($request)->willReturn($mvcEvent);
        $mvcEvent->expects($this->once())->method('setResponse')->with($response)->willReturn($mvcEvent);
        $mvcEvent->expects($this->once())->method('setTarget')->with($this->environment)->willReturn($mvcEvent);
        $mvcEvent->expects($this->any())->method('getRouteMatch')->willReturn($routeMatch);
        $this->permissions->expects($this->once())->method('getMissingWritablePathsForInstallation');
        $this->environment->setEvent($mvcEvent);
        $this->environment->dispatch($request, $response);
        $this->environment->filePermissionsAction();
    }

    public function testPhpVersionActionInstaller()
    {
        $request = $this->getMock(\Zend\Http\PhpEnvironment\Request::class, [], [], '', false);
        $response = $this->getMock(\Zend\Http\PhpEnvironment\Response::class, [], [], '', false);
        $routeMatch = $this->getMock(\Zend\Mvc\Router\RouteMatch::class, [], [], '', false);

        $mvcEvent = $this->getMock(\Zend\Mvc\MvcEvent::class, [], [], '', false);
        $mvcEvent->expects($this->once())->method('setRequest')->with($request)->willReturn($mvcEvent);
        $mvcEvent->expects($this->once())->method('setResponse')->with($response)->willReturn($mvcEvent);
        $mvcEvent->expects($this->once())->method('setTarget')->with($this->environment)->willReturn($mvcEvent);
        $mvcEvent->expects($this->any())->method('getRouteMatch')->willReturn($routeMatch);
        $request->expects($this->once())->method('getQuery')->willReturn(ReadinessCheckInstaller::INSTALLER);
        $this->phpReadinessCheck->expects($this->once())->method('checkPhpVersion');
        $this->environment->setEvent($mvcEvent);
        $this->environment->dispatch($request, $response);
        $this->environment->phpVersionAction();
    }

    public function testPhpVersionActionUpdater()
    {
        $request = $this->getMock(\Zend\Http\PhpEnvironment\Request::class, [], [], '', false);
        $response = $this->getMock(\Zend\Http\PhpEnvironment\Response::class, [], [], '', false);
        $routeMatch = $this->getMock(\Zend\Mvc\Router\RouteMatch::class, [], [], '', false);

        $mvcEvent = $this->getMock(\Zend\Mvc\MvcEvent::class, [], [], '', false);
        $mvcEvent->expects($this->once())->method('setRequest')->with($request)->willReturn($mvcEvent);
        $mvcEvent->expects($this->once())->method('setResponse')->with($response)->willReturn($mvcEvent);
        $mvcEvent->expects($this->once())->method('setTarget')->with($this->environment)->willReturn($mvcEvent);
        $mvcEvent->expects($this->any())->method('getRouteMatch')->willReturn($routeMatch);
        $request->expects($this->once())->method('getQuery')->willReturn(ReadinessCheckUpdater::UPDATER);
        $this->phpReadinessCheck->expects($this->never())->method('checkPhpVersion');
        $read =
            $this->getMockForAbstractClass(\Magento\Framework\Filesystem\Directory\ReadInterface::class, [], '', false);
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
        $request = $this->getMock(\Zend\Http\PhpEnvironment\Request::class, [], [], '', false);
        $response = $this->getMock(\Zend\Http\PhpEnvironment\Response::class, [], [], '', false);
        $routeMatch = $this->getMock(\Zend\Mvc\Router\RouteMatch::class, [], [], '', false);

        $mvcEvent = $this->getMock(\Zend\Mvc\MvcEvent::class, [], [], '', false);
        $mvcEvent->expects($this->once())->method('setRequest')->with($request)->willReturn($mvcEvent);
        $mvcEvent->expects($this->once())->method('setResponse')->with($response)->willReturn($mvcEvent);
        $mvcEvent->expects($this->once())->method('setTarget')->with($this->environment)->willReturn($mvcEvent);
        $mvcEvent->expects($this->any())->method('getRouteMatch')->willReturn($routeMatch);
        $request->expects($this->once())->method('getQuery')->willReturn(ReadinessCheckInstaller::INSTALLER);
        $this->phpReadinessCheck->expects($this->once())->method('checkPhpSettings');
        $this->environment->setEvent($mvcEvent);
        $this->environment->dispatch($request, $response);
        $this->environment->phpSettingsAction();
    }

    public function testPhpSettingsActionUpdater()
    {
        $request = $this->getMock(\Zend\Http\PhpEnvironment\Request::class, [], [], '', false);
        $response = $this->getMock(\Zend\Http\PhpEnvironment\Response::class, [], [], '', false);
        $routeMatch = $this->getMock(\Zend\Mvc\Router\RouteMatch::class, [], [], '', false);

        $mvcEvent = $this->getMock(\Zend\Mvc\MvcEvent::class, [], [], '', false);
        $mvcEvent->expects($this->once())->method('setRequest')->with($request)->willReturn($mvcEvent);
        $mvcEvent->expects($this->once())->method('setResponse')->with($response)->willReturn($mvcEvent);
        $mvcEvent->expects($this->once())->method('setTarget')->with($this->environment)->willReturn($mvcEvent);
        $mvcEvent->expects($this->any())->method('getRouteMatch')->willReturn($routeMatch);
        $request->expects($this->once())->method('getQuery')->willReturn(ReadinessCheckUpdater::UPDATER);
        $this->phpReadinessCheck->expects($this->never())->method('checkPhpSettings');
        $read =
            $this->getMockForAbstractClass(\Magento\Framework\Filesystem\Directory\ReadInterface::class, [], '', false);
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
        $request = $this->getMock(\Zend\Http\PhpEnvironment\Request::class, [], [], '', false);
        $response = $this->getMock(\Zend\Http\PhpEnvironment\Response::class, [], [], '', false);
        $routeMatch = $this->getMock(\Zend\Mvc\Router\RouteMatch::class, [], [], '', false);

        $mvcEvent = $this->getMock(\Zend\Mvc\MvcEvent::class, [], [], '', false);
        $mvcEvent->expects($this->once())->method('setRequest')->with($request)->willReturn($mvcEvent);
        $mvcEvent->expects($this->once())->method('setResponse')->with($response)->willReturn($mvcEvent);
        $mvcEvent->expects($this->once())->method('setTarget')->with($this->environment)->willReturn($mvcEvent);
        $mvcEvent->expects($this->any())->method('getRouteMatch')->willReturn($routeMatch);
        $request->expects($this->once())->method('getQuery')->willReturn(ReadinessCheckInstaller::INSTALLER);
        $this->phpReadinessCheck->expects($this->once())->method('checkPhpExtensions');
        $this->environment->setEvent($mvcEvent);
        $this->environment->dispatch($request, $response);
        $this->environment->phpExtensionsAction();
    }

    public function testPhpExtensionsActionUpdater()
    {
        $request = $this->getMock(\Zend\Http\PhpEnvironment\Request::class, [], [], '', false);
        $response = $this->getMock(\Zend\Http\PhpEnvironment\Response::class, [], [], '', false);
        $routeMatch = $this->getMock(\Zend\Mvc\Router\RouteMatch::class, [], [], '', false);

        $mvcEvent = $this->getMock(\Zend\Mvc\MvcEvent::class, [], [], '', false);
        $mvcEvent->expects($this->once())->method('setRequest')->with($request)->willReturn($mvcEvent);
        $mvcEvent->expects($this->once())->method('setResponse')->with($response)->willReturn($mvcEvent);
        $mvcEvent->expects($this->once())->method('setTarget')->with($this->environment)->willReturn($mvcEvent);
        $mvcEvent->expects($this->any())->method('getRouteMatch')->willReturn($routeMatch);
        $request->expects($this->once())->method('getQuery')->willReturn(ReadinessCheckUpdater::UPDATER);
        $this->phpReadinessCheck->expects($this->never())->method('checkPhpExtensions');
        $read =
            $this->getMockForAbstractClass(\Magento\Framework\Filesystem\Directory\ReadInterface::class, [], '', false);
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
        $this->assertInstanceOf(\Zend\View\Model\JsonModel::class, $model);
    }
}
