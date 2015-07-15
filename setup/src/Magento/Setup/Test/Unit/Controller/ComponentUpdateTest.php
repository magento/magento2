<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Controller;

use \Magento\Setup\Controller\ComponentUpdate;

class ComponentUpdateTest extends \PHPUnit_Framework_TestCase
{
    public function testIndexAction()
    {
        $updater = $this->getMock('Magento\Setup\Model\Updater', [], [], '', false);
        /** @var $controller ComponentUpdate */
        $controller = new ComponentUpdate($updater);
        $viewModel = $controller->indexAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $viewModel);
        $this->assertTrue($viewModel->terminate());
    }

    public function testUpdateActionSuccess()
    {
        $updater = $this->getMock('Magento\Setup\Model\Updater', [], [], '', false);
        /** @var $controller ComponentUpdate */
        $controller = new ComponentUpdate($updater);
        $jsonModel = $controller->updateAction();
        $this->assertInstanceOf('Zend\View\Model\JsonModel', $jsonModel);
        $this->assertTrue($jsonModel->terminate());
    }
}
