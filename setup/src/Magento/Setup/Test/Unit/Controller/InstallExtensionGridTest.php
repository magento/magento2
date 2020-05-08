<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Controller;

use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use Magento\Framework\Composer\ComposerInformation;
use Magento\Setup\Controller\InstallExtensionGrid;
use Magento\Setup\Model\PackagesData;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class InstallExtensionGridTest extends TestCase
{
    /**
     * Controller
     *
     * @var InstallExtensionGrid
     */
    private $controller;

    /**
     * @var PackagesData|MockObject
     */
    private $packagesData;

    protected function setUp(): void
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
        static::assertInstanceOf(ViewModel::class, $viewModel);
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
        static::assertInstanceOf(JsonModel::class, $jsonModel);
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
