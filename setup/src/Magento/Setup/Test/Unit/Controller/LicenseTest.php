<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Controller;

use \Magento\Setup\Controller\License;

class LicenseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Model\License
     */
    private $licenseModel;

    /**
     * @var License
     */
    private $controller;

    public function setUp()
    {
        $this->licenseModel = $this->getMock('\Magento\Setup\Model\License', [], [], '', false);
        $this->controller = new License($this->licenseModel);
    }

    public function testIndexActionWithLicense()
    {
        $this->licenseModel->expects($this->once())->method('getContents')->willReturn('some license string');
        $viewModel = $this->controller->indexAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $viewModel);
        $this->assertArrayHasKey('license', $viewModel->getVariables());
    }

    public function testIndexActionNoLicense()
    {
        $this->licenseModel->expects($this->once())->method('getContents')->willReturn(false);
        $viewModel = $this->controller->indexAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $viewModel);
        $this->assertArrayHasKey('message', $viewModel->getVariables());
        $this->assertEquals('error/404', $viewModel->getTemplate());
    }
}
