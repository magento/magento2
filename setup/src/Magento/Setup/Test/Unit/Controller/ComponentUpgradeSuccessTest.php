<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Controller;

use \Magento\Setup\Controller\ComponentUpgradeSuccess;

class ComponentUpgradeSuccessTest extends \PHPUnit_Framework_TestCase
{
    public function testIndexAction()
    {
        $maintenanceMode = $this->getMock('Magento\Framework\App\MaintenanceMode', [], [], '', false);
        $maintenanceMode->expects($this->once())->method('set')->with(false);
        /** @var $controller ComponentUpgradeSuccess */
        $controller = new ComponentUpgradeSuccess($maintenanceMode);
        $viewModel = $controller->indexAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $viewModel);
        $this->assertTrue($viewModel->terminate());
    }
}
