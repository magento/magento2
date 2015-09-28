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
        $sampleData = $this->getMock('Magento\Setup\Model\SampleData', ['isInstallationError'], [], '', false);
        $objectManager->expects($this->once())->method('get')->willReturn($sampleData);
        /** @var $controller Success */
        $controller = new Success($moduleList, $objectManagerProvider);
        $sampleData->expects($this->once())->method('isInstallationError');
        $viewModel = $controller->indexAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $viewModel);
        $this->assertTrue($viewModel->terminate());
    }
}
