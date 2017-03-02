<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Controller;

use \Magento\Setup\Controller\SystemConfig;

class SystemConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers \Magento\Setup\Controller\SystemConfig::indexAction
     */
    public function testIndexAction()
    {
        /** @var $controller SystemConfig */
        $controller = new SystemConfig();
        $viewModel = $controller->indexAction();
        $this->assertInstanceOf(\Zend\View\Model\ViewModel::class, $viewModel);
        $this->assertTrue($viewModel->terminate());
    }
}
