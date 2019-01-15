<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Controller;

use \Magento\Setup\Controller\Success;

class SuccessTest extends \PHPUnit\Framework\TestCase
{
    public function testIndexAction()
    {
        $moduleList = $this->createMock(\Magento\Framework\Module\ModuleList::class);
        $moduleList->expects($this->once())->method('has')->willReturn(true);
        $objectManagerProvider = $this->createMock(\Magento\Setup\Model\ObjectManagerProvider::class);
        $objectManager = $this->createMock(\Magento\Framework\App\ObjectManager::class);
        $objectManagerProvider->expects($this->once())->method('get')->willReturn($objectManager);
        $sampleDataState =
            $this->createPartialMock(\Magento\Framework\Setup\SampleData\State::class, ['hasError']);
        $objectManager->expects($this->once())->method('get')->willReturn($sampleDataState);
        /** @var $controller Success */
        $controller = new Success($moduleList, $objectManagerProvider);
        $sampleDataState->expects($this->once())->method('hasError');
        $viewModel = $controller->indexAction();
        $this->assertInstanceOf(\Zend\View\Model\ViewModel::class, $viewModel);
        $this->assertTrue($viewModel->terminate());
    }
}
