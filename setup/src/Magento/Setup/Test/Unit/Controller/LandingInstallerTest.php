<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Controller;

use \Magento\Setup\Controller\LandingInstaller;

class LandingInstallerTest extends \PHPUnit_Framework_TestCase
{
    public function testIndexAction()
    {
        /** @var $controller LandingInstaller */
        $controller = new LandingInstaller();
        $_SERVER['DOCUMENT_ROOT'] = 'some/doc/root/value';
        $viewModel = $controller->indexAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $viewModel);
        $this->assertTrue($viewModel->terminate());
        $this->assertEquals('/magento/setup/landing.phtml', $viewModel->getTemplate());
        $variables = $viewModel->getVariables();
        $this->assertArrayHasKey('version', $variables);
        $this->assertArrayHasKey('welcomeMsg', $variables);
        $this->assertArrayHasKey('docRef', $variables);
        $this->assertArrayHasKey('agreeButtonText', $variables);
        $this->assertEquals('Agree and Setup Magento', $variables['agreeButtonText']);
    }
}
