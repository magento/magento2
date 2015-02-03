<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Controller;

class ModuleCheckTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\ObjectManagerInterface
     */
    private $status;

    /**
     * Controller
     *
     * @var \Magento\Setup\Controller\ModuleCheck
     */
    private $controller;

    public function setUp()
    {
        $this->objectManager = $this->getMockForAbstractClass('Magento\Framework\ObjectManagerInterface');
        $this->status = $this->getMock('Magento\Framework\Module\Status', [], [], '', false);
        $objectManagerFactory = $this->getMock('Magento\Setup\Model\ObjectManagerFactory', [], [], '', false);
        $objectManagerFactory->expects($this->once())->method('create')->willReturn($this->objectManager);
        $this->controller = new ModuleCheck($objectManagerFactory);
    }

    public function testIndexAction()
    {
        $this->objectManager->expects($this->once())->method('create')->will($this->returnValue($this->status));
        $this->status->expects($this->once())->method('checkConstraints')->willReturn([]);
        $jsonModel = $this->controller->indexAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $jsonModel);
        $variables = $jsonModel->getVariables();
        $this->assertArrayHasKey('success', $variables);
        $this->assertTrue($variables['success']);
    }

    public function testIndexActionWithError()
    {
        $this->objectManager->expects($this->once())->method('create')->will($this->returnValue($this->status));
        $this->status->expects($this->once())
            ->method('checkConstraints')
            ->willReturn(['ModuleA', 'ModuleB']);
        $jsonModel = $this->controller->indexAction();
        $this->assertInstanceOf('\Zend\View\Model\JsonModel', $jsonModel);
        $variables = $jsonModel->getVariables();
        $this->assertArrayHasKey('success', $variables);
        $this->assertArrayHasKey('error', $variables);
        $this->assertFalse($variables['success']);
    }
}
