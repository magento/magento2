<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Controller;

use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use Magento\Setup\Controller\Navigation;
use Magento\Setup\Model\ObjectManagerProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class NavigationTest extends TestCase
{
    /**
     * @var MockObject|\Magento\Setup\Model\Navigation
     */
    private $navigationModel;

    /**
     * @var \Magento\Setup\Controller\Navigation
     */
    private $controller;

    /**
     * @var ObjectManagerProvider|MockObject
     */
    private $objectManagerProvider;

    protected function setUp(): void
    {
        $this->navigationModel = $this->createMock(\Magento\Setup\Model\Navigation::class);
        $this->objectManagerProvider =
            $this->createMock(ObjectManagerProvider::class);
        $this->controller = new Navigation($this->navigationModel, $this->objectManagerProvider);
    }

    public function testIndexAction()
    {
        $this->navigationModel->expects($this->once())->method('getData')->willReturn('some data');
        $viewModel = $this->controller->indexAction();

        $this->assertInstanceOf(JsonModel::class, $viewModel);
        $this->assertArrayHasKey('nav', $viewModel->getVariables());
    }

    public function testMenuActionUpdater()
    {
        $viewModel = $this->controller->menuAction();
        $this->assertInstanceOf(ViewModel::class, $viewModel);
        $variables = $viewModel->getVariables();
        $this->assertArrayHasKey('menu', $variables);
        $this->assertArrayHasKey('main', $variables);
        $this->assertTrue($viewModel->terminate());
        $this->assertSame('/magento/setup/navigation/menu.phtml', $viewModel->getTemplate());
    }

    public function testMenuActionInstaller()
    {
        $viewModel = $this->controller->menuAction();
        $this->assertInstanceOf(ViewModel::class, $viewModel);
        $variables = $viewModel->getVariables();
        $this->assertArrayHasKey('menu', $variables);
        $this->assertArrayHasKey('main', $variables);
        $this->assertTrue($viewModel->terminate());
        $this->assertSame('/magento/setup/navigation/menu.phtml', $viewModel->getTemplate());
    }
}
