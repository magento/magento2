<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Controller;

use Laminas\View\Model\ViewModel;
use Magento\Setup\Controller\CreateBackup;
use PHPUnit\Framework\TestCase;

class CreateBackupTest extends TestCase
{
    public function testIndexAction()
    {
        /** @var CreateBackup $controller */
        $controller = new CreateBackup();
        $viewModel = $controller->indexAction();
        $this->assertInstanceOf(ViewModel::class, $viewModel);
        $this->assertTrue($viewModel->terminate());
    }
}
