<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Controller;

use \Magento\Setup\Controller\UpdaterReadinessCheck;

class UpdaterReadinessCheckTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UpdaterReadinessCheck
     */
    private $controller;

    public function setUp()
    {
        $this->controller = new UpdaterReadinessCheck();
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
        $this->assertSame('/magento/setup/updater-readiness-check/progress.phtml', $viewModel->getTemplate());
    }
}
