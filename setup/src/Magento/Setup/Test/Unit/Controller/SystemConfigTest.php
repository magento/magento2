<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Controller;

use \Magento\Setup\Controller\SystemConfig;

class SystemConfigTest extends \PHPUnit\Framework\TestCase
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
