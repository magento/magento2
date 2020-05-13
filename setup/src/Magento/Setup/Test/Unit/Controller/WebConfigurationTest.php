<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Controller;

use Laminas\View\Model\ViewModel;
use Magento\Setup\Controller\WebConfiguration;
use PHPUnit\Framework\TestCase;

class WebConfigurationTest extends TestCase
{
    public function testIndexAction()
    {
        /** @var WebConfiguration $controller */
        $controller = new WebConfiguration();
        $_SERVER['DOCUMENT_ROOT'] = 'some/doc/root/value';
        $viewModel = $controller->indexAction();
        $this->assertInstanceOf(ViewModel::class, $viewModel);
        $this->assertTrue($viewModel->terminate());
        $this->assertArrayHasKey('autoBaseUrl', $viewModel->getVariables());
    }
}
