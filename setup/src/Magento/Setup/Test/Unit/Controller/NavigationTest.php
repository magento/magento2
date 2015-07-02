<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Controller;

use \Magento\Setup\Controller\Navigation;
use Magento\Setup\Model\Navigation as NavModel;

class NavigationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Model\Navigation
     */
    private $navigationModel;

    /**
     * @var \Magento\Setup\Controller\Navigation
     */
    private $controller;

    /**
     * @var \Magento\Setup\Model\Cron\Status|\PHPUnit_Framework_MockObject_MockObject
     */
    private $status;

    public function setUp()
    {
        $this->navigationModel = $this->getMock('\Magento\Setup\Model\Navigation', [], [], '', false);
        $this->status = $this->getMock('Magento\Setup\Model\Cron\Status', [], [], '', false);
        $this->controller = new Navigation($this->navigationModel, $this->status);
    }

    public function testIndexAction()
    {
        $this->navigationModel->expects($this->once())->method('getData')->willReturn('some data');
        $viewModel = $this->controller->indexAction();

        $this->assertInstanceOf('\Zend\View\Model\JsonModel', $viewModel);
        $this->assertArrayHasKey('nav', $viewModel->getVariables());
    }

    public function testMenuActionUpdater()
    {
        $this->navigationModel->expects($this->once())->method('getType')->willReturn(NavModel::NAV_UPDATER);
        $viewModel = $this->controller->menuAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $viewModel);
        $variables = $viewModel->getVariables();
        $this->assertArrayHasKey('headerTitle', $variables);
        $this->assertEquals('Magento Component Manager', $variables['headerTitle']);
        $this->assertArrayHasKey('menu', $variables);
        $this->assertArrayHasKey('main', $variables);
        $this->assertTrue($viewModel->terminate());
        $this->assertSame('/magento/setup/navigation/menu.phtml', $viewModel->getTemplate());
    }

    public function testMenuActionInstaller()
    {
        $this->navigationModel->expects($this->once())->method('getType')->willReturn(NavModel::NAV_INSTALLER);
        $viewModel = $this->controller->menuAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $viewModel);
        $variables = $viewModel->getVariables();
        $this->assertArrayHasKey('headerTitle', $variables);
        $this->assertEquals('Magento Installation', $variables['headerTitle']);
        $this->assertArrayHasKey('menu', $variables);
        $this->assertArrayHasKey('main', $variables);
        $this->assertTrue($viewModel->terminate());
        $this->assertSame('/magento/setup/navigation/menu.phtml', $viewModel->getTemplate());
    }
}
