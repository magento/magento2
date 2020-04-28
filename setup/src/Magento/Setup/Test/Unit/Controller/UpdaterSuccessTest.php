<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Controller;

use Laminas\View\Model\ViewModel;
use Magento\Framework\App\MaintenanceMode;
use Magento\Setup\Controller\UpdaterSuccess;
use PHPUnit\Framework\TestCase;

class UpdaterSuccessTest extends TestCase
{
    public function testIndexAction()
    {
        /** @var MaintenanceMode $maintenanceMode */
        $maintenanceMode = $this->createMock(MaintenanceMode::class);
        $maintenanceMode->expects($this->once())->method('set')->with(false);
        /** @var UpdaterSuccess $controller */
        $controller = new UpdaterSuccess($maintenanceMode);
        $viewModel = $controller->indexAction();
        $this->assertInstanceOf(ViewModel::class, $viewModel);
        $this->assertTrue($viewModel->terminate());
    }
}
