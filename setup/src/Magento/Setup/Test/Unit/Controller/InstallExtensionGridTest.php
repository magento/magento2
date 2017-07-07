<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Controller;

use Magento\Setup\Controller\InstallExtensionGrid;
use Magento\Setup\Model\PackagesData;
use Magento\Framework\Composer\ComposerInformation;

class InstallExtensionGridTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Controller
     *
     * @var \Magento\Setup\Controller\InstallExtensionGrid
     */
    private $controller;

    /**
     * @var PackagesData|\PHPUnit_Framework_MockObject_MockObject
     */
    private $packagesData;

    public function setUp()
    {
        $this->packagesData = $this->getMockBuilder(PackagesData::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->controller = new InstallExtensionGrid(
            $this->packagesData
        );
    }

    /**
     * @covers \Magento\Setup\Controller\InstallExtensionGrid::indexAction
     */
    public function testIndexAction()
    {
        $viewModel = $this->controller->indexAction();
        static::assertInstanceOf(\Zend\View\Model\ViewModel::class, $viewModel);
    }

    /**
     * @param array $extensions
     * @dataProvider dataProviderForTestExtensionsAction
     * @covers \Magento\Setup\Controller\InstallExtensionGrid::extensionsAction
     */
    public function testExtensionsAction($extensions)
    {
        $this->packagesData->expects(static::once())
            ->method('getPackagesForInstall')
            ->willReturn($extensions);

        $jsonModel = $this->controller->extensionsAction();
        static::assertInstanceOf(\Zend\View\Model\JsonModel::class, $jsonModel);
        $variables = $jsonModel->getVariables();
        static::assertArrayHasKey('success', $variables);
        static::assertArrayHasKey('extensions', $variables);
        static::assertArrayHasKey('total', $variables);
        static::assertTrue($variables['success']);
    }

    /**
     * @return array
     */
    public function dataProviderForTestExtensionsAction()
    {
        $extensions['packages'] = [
            'magento/testing-extension' => [
                'name' => 'magento/testing-extension',
                'type' => ComposerInformation::MODULE_PACKAGE_TYPE,
                'vendor' => 'magento',
                'version' => '2.2.2',
                'author' => 'magento'],
            'magento/my-first-module' => [
                'name' => 'magento/my-first-module',
                'type' => ComposerInformation::MODULE_PACKAGE_TYPE,
                'vendor' => 'magento',
                'version' => '2.0.0',
                'author' => 'magento'],
            'magento/last-extension' => [
                'name' => 'magento/theme',
                'type' => ComposerInformation::THEME_PACKAGE_TYPE,
                'vendor' => 'magento',
                'version' => '2.1.1',
                'author' => 'magento'],
            'magento/magento-second-module' => [
                'name' => 'magento/magento-second-module',
                'type' => ComposerInformation::COMPONENT_PACKAGE_TYPE,
                'vendor' => 'magento',
                'version' => '2.0.0',
                'author' => 'magento']
        ];
        return [[$extensions]];
    }
}
