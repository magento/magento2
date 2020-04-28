<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Controller;

use Laminas\View\Model\ViewModel;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Module\ModuleList;
use Magento\Framework\Setup\SampleData\State;
use Magento\Setup\Controller\Success;
use Magento\Setup\Model\ObjectManagerProvider;
use PHPUnit\Framework\TestCase;

class SuccessTest extends TestCase
{
    public function testIndexAction()
    {
        $moduleList = $this->createMock(ModuleList::class);
        $moduleList->expects($this->once())->method('has')->willReturn(true);
        $objectManagerProvider = $this->createMock(ObjectManagerProvider::class);
        $objectManager = $this->createMock(ObjectManager::class);
        $objectManagerProvider->expects($this->once())->method('get')->willReturn($objectManager);
        $sampleDataState =
            $this->createPartialMock(State::class, ['hasError']);
        $objectManager->expects($this->once())->method('get')->willReturn($sampleDataState);
        /** @var Success $controller */
        $controller = new Success($moduleList, $objectManagerProvider);
        $sampleDataState->expects($this->once())->method('hasError');
        $viewModel = $controller->indexAction();
        $this->assertInstanceOf(ViewModel::class, $viewModel);
        $this->assertTrue($viewModel->terminate());
    }
}
