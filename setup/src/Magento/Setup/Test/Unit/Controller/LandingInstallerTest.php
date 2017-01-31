<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Controller;

use \Magento\Setup\Controller\LandingInstaller;
use \Magento\Framework\App\ProductMetadata;
use \Magento\Framework\Composer\ComposerJsonFinder;
use Magento\Framework\App\Filesystem\DirectoryList;

class LandingInstallerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test Product Version Value
     */
    const TEST_PRODUCT_VERSION = '222.333.444';

    public function testIndexAction()
    {
        /** @var \Magento\Framework\App\ProductMetadata|\PHPUnit_Framework_MockObject_MockObject $productMetadataMock */
        $productMetadataMock =  $this->getMockBuilder('Magento\Framework\App\ProductMetadata')
            ->setMethods(['getVersion'])
            ->disableOriginalConstructor()
            ->getMock();
        $productMetadataMock->expects($this->once())
            ->method('getVersion')
            ->willReturn($this::TEST_PRODUCT_VERSION);
        /** @var $controller LandingInstaller */
        $controller = new LandingInstaller($productMetadataMock);
        $_SERVER['DOCUMENT_ROOT'] = 'some/doc/root/value';
        $viewModel = $controller->indexAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $viewModel);
        $this->assertTrue($viewModel->terminate());
        $this->assertEquals('/magento/setup/landing.phtml', $viewModel->getTemplate());
        $variables = $viewModel->getVariables();
        $this->assertArrayHasKey('version', $variables);
        $this->assertEquals($this::TEST_PRODUCT_VERSION, $variables['version']);
        $this->assertArrayHasKey('welcomeMsg', $variables);
        $this->assertArrayHasKey('docRef', $variables);
        $this->assertArrayHasKey('agreeButtonText', $variables);
        $this->assertEquals('Agree and Setup Magento', $variables['agreeButtonText']);
    }
}
