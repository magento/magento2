<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Controller;

use \Magento\Setup\Controller\ReadinessCheckUpdater;

class ReadinessCheckUpdaterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ReadinessCheckUpdater
     */
    private $controller;

    public function setUp()
    {
        $this->controller = new ReadinessCheckUpdater();
    }

    public function testIndexAction()
    {
        $viewModel = $this->controller->indexAction();
        $this->assertInstanceOf(\Zend\View\Model\ViewModel::class, $viewModel);
        $this->assertTrue($viewModel->terminate());
        $variables = $viewModel->getVariables();
        $this->assertArrayHasKey('actionFrom', $variables);
        $this->assertEquals('updater', $variables['actionFrom']);
    }

    public function testProgressAction()
    {
        $viewModel = $this->controller->progressAction();
        $this->assertInstanceOf(\Zend\View\Model\ViewModel::class, $viewModel);
        $this->assertTrue($viewModel->terminate());
        $this->assertSame('/magento/setup/readiness-check/progress.phtml', $viewModel->getTemplate());
    }
}
