<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Controller;

use Magento\Setup\Controller\ComponentManager;

class ComponentManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ComponentReader
     */
    private $reader;

    /**
     * @var array
     */
    private $componentData = [];

    public function __construct()
    {
        $this->componentData = [
            [
                'type' => 'module',
                'version' => '1.0',
                'name' => 'module name',
            ],
        ];
        $this->reader = $this->getMock('Magento\Framework\Composer\ComponentReader', [], [], '', false);
        $this->reader->expects($this->once())
            ->method('getComponents')
            ->willReturn($this->componentData);
    }

    public function testIndexAction()
    {
        $controller = new \Magento\Setup\Controller\ComponentManager($this->reader);
        $viewModel = $controller->indexAction();
        $this->assertInstanceOf('Zend\View\Model\JsonModel', $viewModel);
        $this->assertTrue($viewModel->getVariable('success'));
        $this->assertEquals($this->componentData, $viewModel->getVariable('components'));
    }
}
