<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Controller;

use \Magento\Setup\Controller\ReadinessCheckUpdater;

class ReadinessCheckUpdaterTest extends \PHPUnit_Framework_TestCase
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
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $viewModel);
        $this->assertTrue($viewModel->terminate());
    }

    public function testProgressAction()
    {
        $viewModel = $this->controller->progressAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $viewModel);
        $this->assertTrue($viewModel->terminate());
        $this->assertSame('/magento/setup/readiness-check-updater/progress.phtml', $viewModel->getTemplate());
    }
}
