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
        $updater = $this->getMock('Magento\Setup\Model\Updater', [], [], '', false);
        /** @var $controller ComponentUpgradeSuccess */
        $controller = new ComponentUpgradeSuccess($updater);
        $viewModel = $controller->indexAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $viewModel);
        $this->assertTrue($viewModel->terminate());
    }

}
