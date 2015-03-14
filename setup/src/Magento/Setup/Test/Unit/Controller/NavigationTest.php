<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Controller;

use \Magento\Setup\Controller\Navigation;

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

    public function setUp()
    {
        $this->navigationModel = $this->getMock('\Magento\Setup\Model\Navigation', [], [], '', false);
        $this->controller = new Navigation($this->navigationModel);
    }

    public function testIndexAction()
    {
        $this->navigationModel->expects($this->once())->method('getData')->willReturn('some data');
        $viewModel = $this->controller->indexAction();
        $this->assertInstanceOf('\Zend\View\Model\JsonModel', $viewModel);
        $this->assertArrayHasKey('nav', $viewModel->getVariables());
    }

    public function testMenuAction()
    {
        $viewModel = $this->controller->menuAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $viewModel);
        $variables = $viewModel->getVariables();
        $this->assertArrayHasKey('menu', $variables);
        $this->assertArrayHasKey('main', $variables);
        $this->assertTrue($viewModel->terminate());
        $this->assertSame('/magento/setup/navigation/menu.phtml', $viewModel->getTemplate());
    }
}
