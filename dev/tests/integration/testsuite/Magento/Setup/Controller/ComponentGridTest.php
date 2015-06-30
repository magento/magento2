<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Controller;

class ComponentGridTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Magento\Framework\Composer\ComposerInformation
     */
    private $composerInformationMock;

    /**
     * @var array
     */
    private $componentData = [];

    public function __construct()
    {
        $this->componentData = [
            [
                'name' => 'module name',
                'type' => 'module',
                'version' => '1.0',
            ],
        ];
        $this->composerInformationMock = $this->getMock(
            'Magento\Framework\Composer\ComposerInformation',
            [],
            [],
            '',
            false
        );
        $this->composerInformationMock->expects($this->once())
            ->method('getRootRequiredPackageTypesByNameVersion')
            ->willReturn($this->componentData);
    }

    public function testIndexAction()
    {
        $controller = new \Magento\Setup\Controller\ComponentGrid($this->composerInformationMock);
        $viewModel = $controller->componentsAction();
        $this->assertInstanceOf('Zend\View\Model\JsonModel', $viewModel);
        $this->assertTrue($viewModel->getVariable('success'));
        $this->assertEquals($this->componentData, $viewModel->getVariable('components'));
    }
}
