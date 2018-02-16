<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Controller;

use \Magento\Setup\Controller\InstallExtensionGrid;

class InstallExtensionGridTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Controller
     *
     * @var \Magento\Setup\Controller\InstallExtensionGrid
     */
    private $controller;

    /**
     * @var \Magento\Setup\Model\PackagesData
     */
    private $packagesData;

    public function setUp()
    {
        $this->packagesData =
            $this->getMock('Magento\Setup\Model\PackagesData', ['getPackagesForInstall'], [], '', false);
        $this->controller = new InstallExtensionGrid($this->packagesData);
    }

    /**
     * @covers \Magento\Setup\Controller\InstallExtensionGrid::indexAction
     */
    public function testIndexAction()
    {
        $viewModel = $this->controller->indexAction();
        $this->assertInstanceOf('\Zend\View\Model\ViewModel', $viewModel);
    }

    /**
     * @param array $extensions
     * @dataProvider dataProviderForTestExtensionsAction
     * @covers \Magento\Setup\Controller\InstallExtensionGrid::extensionsAction
     */
    public function testExtensionsAction($extensions)
    {
        $this->packagesData
            ->expects($this->once())
            ->method('getPackagesForInstall')
            ->will($this->returnValue($extensions));
        $jsonModel = $this->controller->extensionsAction();
        $this->assertInstanceOf('\Zend\View\Model\JsonModel', $jsonModel);
        $variables = $jsonModel->getVariables();
        $this->assertArrayHasKey('success', $variables);
        $this->assertArrayHasKey('extensions', $variables);
        $this->assertArrayHasKey('total', $variables);
        $this->assertTrue($variables['success']);
    }

    /**
     * @return array
     */
    public function dataProviderForTestExtensionsAction()
    {
        $extensions = [
            'magento/testing-extension' => [
                'name' => 'magento/testing-extension',
                'type' => 'module',
                'version' => '2.2.2',
                'author' => 'magento'],
            'magento/my-first-module' => [
                'name' => 'magento/my-first-module',
                'type' => 'module',
                'version' => '2.0.0',
                'author' => 'magento'],
            'magento/last-extension' => [
                'name' => 'magento/last-extension',
                'type' => 'module',
                'version' => '2.1.1',
                'author' => 'magento'],
            'magento/magento-second-module' => [
                'name' => 'magento/magento-second-module',
                'type' => 'module',
                'version' => '2.0.0',
                'author' => 'magento']
        ];
        return [[$extensions]];
    }
}
