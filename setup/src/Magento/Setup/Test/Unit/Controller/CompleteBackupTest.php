<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Controller;

use \Magento\Setup\Controller\CompleteBackup;

class CompleteBackupTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Controller
     *
     * @var \Magento\Setup\Controller\CompleteBackup
     */
    private $controller;

    public function setUp()
    {
        $this->controller = new CompleteBackup();
    }

    public function testIndexAction()
    {
        $viewModel = $this->controller->indexAction();
        $this->assertInstanceOf(\Zend\View\Model\ViewModel::class, $viewModel);
        $this->assertSame('/error/404.phtml', $viewModel->getTemplate());
        $this->assertSame(
            \Zend\Http\Response::STATUS_CODE_404,
            $this->controller->getResponse()->getStatusCode()
        );
    }

    public function testProgressAction()
    {
        $viewModel = $this->controller->progressAction();
        $this->assertInstanceOf(\Zend\View\Model\ViewModel::class, $viewModel);
        $this->assertTrue($viewModel->terminate());
        $this->assertSame('/magento/setup/complete-backup/progress.phtml', $viewModel->getTemplate());
    }
}
