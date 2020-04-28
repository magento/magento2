<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Controller;

use Laminas\View\Model\ViewModel;
use Magento\Setup\Controller\License;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LicenseTest extends TestCase
{
    /**
     * @var MockObject|\Magento\Setup\Model\License
     */
    private $licenseModel;

    /**
     * @var License
     */
    private $controller;

    public function setUp(): void
    {
        $this->licenseModel = $this->createMock(\Magento\Setup\Model\License::class);
        $this->controller = new License($this->licenseModel);
    }

    public function testIndexActionWithLicense()
    {
        $this->licenseModel->expects($this->once())->method('getContents')->willReturn('some license string');
        $viewModel = $this->controller->indexAction();
        $this->assertInstanceOf(ViewModel::class, $viewModel);
        $this->assertArrayHasKey('license', $viewModel->getVariables());
    }

    public function testIndexActionNoLicense()
    {
        $this->licenseModel->expects($this->once())->method('getContents')->willReturn(false);
        $viewModel = $this->controller->indexAction();
        $this->assertInstanceOf(ViewModel::class, $viewModel);
        $this->assertArrayHasKey('message', $viewModel->getVariables());
        $this->assertEquals('error/404', $viewModel->getTemplate());
    }
}
