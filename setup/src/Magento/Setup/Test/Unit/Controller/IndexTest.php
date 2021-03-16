<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Controller;

use Laminas\View\Model\ViewModel;
use Magento\Framework\App\ProductMetadata;
use Magento\Setup\Controller\Index;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class IndexTest extends TestCase
{
    /**
     * Test Product Version Value
     */
    private const TEST_PRODUCT_VERSION = '222.333.444';

    /**
     * Test license string
     */
    private const TEST_LICENSE = 'some license string';

    /**
     * @var Index
     */
    private $controller;

    protected function setUp(): void
    {
        /** @var ProductMetadata|MockObject $productMetadataMock */
        $productMetadataMock =  $this->getMockBuilder(ProductMetadata::class)
            ->onlyMethods(['getVersion'])
            ->disableOriginalConstructor()
            ->getMock();
        $productMetadataMock->expects($this->once())
            ->method('getVersion')
            ->willReturn(self::TEST_PRODUCT_VERSION);

        $licenseModel = $this->createMock(\Magento\Setup\Model\License::class);
        $licenseModel->expects($this->once())->method('getContents')->willReturn(self::TEST_LICENSE);

        $this->controller = new Index($productMetadataMock, $licenseModel);
    }

    public function testIndexAction(): void
    {
        $viewModel = $this->controller->indexAction();

        //check view model
        $this->assertInstanceOf(ViewModel::class, $viewModel);
        $this->assertFalse($viewModel->terminate());
        $variables = $viewModel->getVariables();

        //version
        $this->assertArrayHasKey('version', $variables);
        $this->assertEquals(self::TEST_PRODUCT_VERSION, $variables['version']);

        //license
        $this->assertArrayHasKey('license', $viewModel->getVariables());
        $this->assertEquals(self::TEST_LICENSE, $variables['license']);
    }
}
