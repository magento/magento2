<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Controller;

use Magento\Setup\Controller\Environment;
use Magento\Setup\Controller\ResponseTypeInterface;
use Zend\View\Model\JsonModel;

class EnvironmentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Setup\Model\PhpInformation|\PHPUnit_Framework_MockObject_MockObject
     */
    private $phpInfo;

    /**
     * @var \Magento\Setup\Model\FilePermissions|\PHPUnit_Framework_MockObject_MockObject
     */
    private $permissions;

    /**
     * @var \Composer\Package\Version\VersionParser|\PHPUnit_Framework_MockObject_MockObject
     */
    private $versionParser;

    /**
     * @var \Magento\Framework\Composer\ComposerInformation|\PHPUnit_Framework_MockObject_MockObject
     */
    private $composerInfo;

    /**
     * @var \Magento\Framework\Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filesystem;

    /**
     * @var \Magento\Setup\Model\CronScriptReadinessCheck|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cronScriptReadinessCheck;

    /**
     * @var \Magento\Setup\Model\DependencyReadinessCheck|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dependencyReadinessCheck;

    /**
     * @var Environment
     */
    private $environment;

    public function setUp()
    {
        $this->phpInfo = $this->getMock('Magento\Setup\Model\PhpInformation', [], [], '', false);
        $this->permissions = $this->getMock('Magento\Setup\Model\FilePermissions', [], [], '', false);
        $this->versionParser = $this->getMock('Composer\Package\Version\VersionParser', [], [], '', false);
        $this->composerInfo = $this->getMock('Magento\Framework\Composer\ComposerInformation', [], [], '', false);
        $this->filesystem = $this->getMock('Magento\Framework\Filesystem', [], [], '', false);
        $this->cronScriptReadinessCheck = $this->getMock(
            'Magento\Setup\Model\CronScriptReadinessCheck',
            [],
            [],
            '',
            false
        );
        $this->dependencyReadinessCheck = $this->getMock(
            'Magento\Setup\Model\DependencyReadinessCheck',
            [],
            [],
            '',
            false
        );
        $this->environment = new Environment(
            $this->phpInfo,
            $this->permissions,
            $this->versionParser,
            $this->composerInfo,
            $this->filesystem,
            $this->cronScriptReadinessCheck,
            $this->dependencyReadinessCheck
        );
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
            ['responseType' => ResponseTypeInterface::RESPONSE_TYPE_ERROR, 'errorMessage' => 'error message setup']
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
            ['responseType' => ResponseTypeInterface::RESPONSE_TYPE_ERROR, 'errorMessage' => 'error message updater']
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
                'errorMessage' => 'error message setup<br/>error message updater'
            ]
        );
        $this->assertEquals($expected, $this->environment->cronScriptAction());
    }
}
