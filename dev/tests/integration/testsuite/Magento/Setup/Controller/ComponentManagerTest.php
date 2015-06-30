<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Controller;

use Magento\Setup\Controller\ComponentManager;

class ComponentManagerTest extends \PHPUnit_Framework_TestCase
{
    public function testIndexAction()
    {
        $controller = new \Magento\Setup\Controller\ComponentManager(
            new \Magento\Framework\Composer\ComponentReader(BP)
        );
        $viewModel = $controller->indexAction();
        $this->assertInstanceOf('Zend\View\Model\JsonModel', $viewModel);
        $this->assertTrue($viewModel->getVariable('success'));
        $this->assertNotNull($viewModel->getVariable('components'));
    }
}
