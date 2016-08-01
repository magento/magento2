<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Controller;

use Magento\Setup\Controller\InstallExtensionGrid;
use Magento\Setup\Model\Grid\TypeMapper;
use Magento\Setup\Model\PackagesData;

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

    /**
     * @var TypeMapper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $typeMapperMock;

    public function setUp()
    {
        $this->packagesData = $this->getMockBuilder(PackagesData::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->typeMapperMock = $this->getMockBuilder(TypeMapper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->controller = new InstallExtensionGrid(
            $this->packagesData,
            $this->typeMapperMock
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
        $this->typeMapperMock->expects(static::exactly(4))
            ->method('map')
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
                'type' => 'module',
                'vendor' => 'magento',
                'version' => '2.2.2',
                'author' => 'magento'],
            'magento/my-first-module' => [
                'name' => 'magento/my-first-module',
                'type' => 'module',
                'vendor' => 'magento',
                'version' => '2.0.0',
                'author' => 'magento'],
            'magento/last-extension' => [
                'name' => 'magento/last-extension',
                'type' => 'module',
                'vendor' => 'magento',
                'version' => '2.1.1',
                'author' => 'magento'],
            'magento/magento-second-module' => [
                'name' => 'magento/magento-second-module',
                'type' => 'module',
                'vendor' => 'magento',
                'version' => '2.0.0',
                'author' => 'magento']
        ];
        return [[$extensions]];
    }
}
