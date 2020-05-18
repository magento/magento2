<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Controller;

use Laminas\View\Model\ViewModel;
use Magento\Setup\Controller\AddDatabase;
use PHPUnit\Framework\TestCase;

class AddDatabaseTest extends TestCase
{
    public function testIndexAction()
    {
        /** @var AddDatabase $controller */
        $controller = new AddDatabase();
        $viewModel = $controller->indexAction();
        $this->assertInstanceOf(ViewModel::class, $viewModel);
        $this->assertTrue($viewModel->terminate());
    }
}
