<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Controller;

use Laminas\View\Model\ViewModel;
use Magento\Framework\App\ProductMetadata;
use Magento\Setup\Controller\Landing;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LandingTest extends TestCase
{
    /**
     * Test Product Version Value
     */
    const TEST_PRODUCT_VERSION = '222.333.444';

    public function testIndexAction()
    {
        /** @var ProductMetadata|MockObject $productMetadataMock */
        $productMetadataMock =  $this->getMockBuilder(ProductMetadata::class)
            ->setMethods(['getVersion'])
            ->disableOriginalConstructor()
            ->getMock();
        $productMetadataMock->expects($this->once())
            ->method('getVersion')
            ->willReturn($this::TEST_PRODUCT_VERSION);
        /** @var Landing $controller */
        $controller = new Landing($productMetadataMock);
        $_SERVER['DOCUMENT_ROOT'] = 'some/doc/root/value';
        $viewModel = $controller->indexAction();
        $this->assertInstanceOf(ViewModel::class, $viewModel);
        $this->assertTrue($viewModel->terminate());
        $this->assertEquals('/magento/setup/landing.phtml', $viewModel->getTemplate());
        $variables = $viewModel->getVariables();
        $this->assertArrayHasKey('version', $variables);
        $this->assertEquals($this::TEST_PRODUCT_VERSION, $variables['version']);
    }
}
