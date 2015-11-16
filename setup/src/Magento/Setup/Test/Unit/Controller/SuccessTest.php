<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Controller;

use \Magento\Setup\Controller\Success;

class SuccessTest extends \PHPUnit_Framework_TestCase
{
    public function testIndexAction()
    {
        $moduleList = $this->getMock('Magento\Framework\Module\ModuleList', [], [], '', false);
        $moduleList->expects($this->once())->method('has')->willReturn(true);
        $objectManagerProvider = $this->getMock('Magento\Setup\Model\ObjectManagerProvider', [], [], '', false);
        $objectManager = $this->getMock('Magento\Framework\App\ObjectManager', [], [], '', false);
        $objectManagerProvider->expects($this->once())->method('get')->willReturn($objectManager);
        $sampleDataState = $this->getMock('Magento\Framework\Setup\SampleData\State', ['hasError'], [], '', false);
        $objectManager->expects($this->once())->method('get')->willReturn($sampleDataState);
        /** @var $controller Success */
        $controller = new Success($moduleList, $objectManagerProvider);
        $sampleDataState->expects($this->once())->method('hasError');
        $viewModel = $controller->indexAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $viewModel);
        $this->assertTrue($viewModel->terminate());
    }
}
