<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Controller;

use \Magento\Setup\Controller\SelectVersion;
use \Magento\Setup\Controller\ResponseTypeInterface;

class SelectVersionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Setup\Model\SystemPackage|\PHPUnit_Framework_MockObject_MockObject
     */
    private $systemPackage;

    /**
     * @var \Magento\Framework\Composer\ComposerInformation|\PHPUnit_Framework_MockObject_MockObject
     */
    private $composerInformation;

    /**
     * @var \Magento\Composer\InfoCommand|\PHPUnit_Framework_MockObject_MockObject
     */
    private $infoCommand;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $directoryList;

    /**
     * Controller
     *
     * @var \Magento\Setup\Controller\SelectVersion
     */
    private $controller;

    public function setUp()
    {
        $this->directoryList = $this->getMock('Magento\Framework\App\Filesystem\DirectoryList', [], [], '', false);
        $this->systemPackage = $this->getMock('Magento\Setup\Model\SystemPackage', [], [], '', false);
        $this->composerInformation = $this->getMock(
            'Magento\Framework\Composer\ComposerInformation',
            [],
            [],
            '',
            false
        );
        $this->infoCommand = $this->getMock('Magento\Composer\InfoCommand', [], [], '', false);
        $magentoComposerApplicationFactory = $this->getMock(
            'Magento\Framework\Composer\MagentoComposerApplicationFactory',
            [],
            [],
            '',
            false
        );
        $magentoComposerApplicationFactory->expects($this->once())
            ->method('createInfoCommand')
            ->willReturn($this->infoCommand);
        $this->controller = new SelectVersion(
            $this->systemPackage,
            $this->composerInformation,
            $magentoComposerApplicationFactory,
            $this->directoryList
        );
    }

    public function testIndexAction()
    {
        $viewModel = $this->controller->indexAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $viewModel);
        $this->assertTrue($viewModel->terminate());
    }

    public function testSystemPackageActionAction()
    {
        $this->systemPackage->expects($this->once())
            ->method('getPackageVersions')
            ->willReturn(['package' => 'SamplePackage']);
        $jsonModel = $this->controller->systemPackageAction();
        $this->assertInstanceOf('Zend\View\Model\JsonModel', $jsonModel);
        $variables = $jsonModel->getVariables();
        $this->assertArrayHasKey('responseType', $variables);
        $this->assertEquals(ResponseTypeInterface::RESPONSE_TYPE_SUCCESS, $variables['responseType']);
    }

    public function testSystemPackageActionActionWithError()
    {
        $this->systemPackage->expects($this->once())
            ->method('getPackageVersions')
            ->will($this->throwException(new \Exception("Test error message")));
        $jsonModel = $this->controller->systemPackageAction();
        $this->assertInstanceOf('Zend\View\Model\JsonModel', $jsonModel);
        $variables = $jsonModel->getVariables();
        $this->assertArrayHasKey('responseType', $variables);
        $this->assertEquals(ResponseTypeInterface::RESPONSE_TYPE_ERROR, $variables['responseType']);
    }

    public function testComponentsActionAction()
    {
        $this->composerInformation->expects($this->once())
            ->method('getRootRequiredPackageTypesByNameVersion')
            ->willReturn([]);
        $jsonModel = $this->controller->componentsAction();
        $this->assertInstanceOf('Zend\View\Model\JsonModel', $jsonModel);
        $variables = $jsonModel->getVariables();
        $this->assertArrayHasKey('responseType', $variables);
        $this->assertEquals(ResponseTypeInterface::RESPONSE_TYPE_SUCCESS, $variables['responseType']);
        $this->assertArrayHasKey('success', $variables);
        $this->assertEquals(true, $variables['success']);
        $this->assertArrayHasKey('components', $variables);
        $this->assertEquals([], $variables['components']);
        $this->assertArrayHasKey('total', $variables);
        $this->assertEquals(0, $variables['total']);
    }

    public function testComponentsActionActionWithError()
    {
        $this->composerInformation->expects($this->once())
            ->method('getRootRequiredPackageTypesByNameVersion')
            ->will($this->throwException(new \Exception("Test error message")));
        $jsonModel = $this->controller->componentsAction();
        $this->assertInstanceOf('Zend\View\Model\JsonModel', $jsonModel);
        $variables = $jsonModel->getVariables();
        $this->assertArrayHasKey('responseType', $variables);
        $this->assertEquals(ResponseTypeInterface::RESPONSE_TYPE_ERROR, $variables['responseType']);
    }
}
