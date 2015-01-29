<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Controller;

class ModulesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param array $expected
     *
     * @dataProvider indexActionDataProvider
     */
    public function testIndexAction($expected)
    {
        $modules = $this->getMock('\Magento\Setup\Model\ModuleStatus', [], [], '', false);
        $controller = new Modules($modules);

        $modules->expects($this->once())->method('getAllModules')->willReturn($expected['modules']);

        $viewModel = $controller->indexAction();

        $this->assertInstanceOf('Zend\View\Model\ViewModel', $viewModel);
        $this->assertTrue($viewModel->terminate());
        $variables = $viewModel->getVariables();
        $this->assertArrayHasKey('modules', $variables);
    }

    /**
     * @return array
     */
    public function indexActionDataProvider()
    {
        return [
            'with_modules' => [['modules' => ['module1', 'module2']]],
            'no_modules' => [['modules' => []]],
            'null_modules' => [null],
        ];
    }
}
