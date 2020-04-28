<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Controller;

use Laminas\View\Model\ViewModel;
use Magento\Setup\Controller\CreateAdminAccount;
use PHPUnit\Framework\TestCase;

class CreateAdminAccountTest extends TestCase
{
    public function testIndexAction()
    {
        $controller = new CreateAdminAccount();
        $viewModel = $controller->indexAction();
        $this->assertInstanceOf(ViewModel::class, $viewModel);
        $this->assertTrue($viewModel->terminate());
    }
}
