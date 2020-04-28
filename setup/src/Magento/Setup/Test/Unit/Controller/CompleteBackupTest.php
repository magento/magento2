<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Controller;

use Laminas\Http\Response;
use Laminas\View\Model\ViewModel;
use Magento\Setup\Controller\CompleteBackup;
use PHPUnit\Framework\TestCase;

class CompleteBackupTest extends TestCase
{
    /**
     * Controller
     *
     * @var CompleteBackup
     */
    private $controller;

    protected function setUp(): void
    {
        $this->controller = new CompleteBackup();
    }

    public function testIndexAction()
    {
        $viewModel = $this->controller->indexAction();
        $this->assertInstanceOf(ViewModel::class, $viewModel);
        $this->assertSame('/error/404.phtml', $viewModel->getTemplate());
        $this->assertSame(
            Response::STATUS_CODE_404,
            $this->controller->getResponse()->getStatusCode()
        );
    }

    public function testProgressAction()
    {
        $viewModel = $this->controller->progressAction();
        $this->assertInstanceOf(ViewModel::class, $viewModel);
        $this->assertTrue($viewModel->terminate());
        $this->assertSame('/magento/setup/complete-backup/progress.phtml', $viewModel->getTemplate());
    }
}
