<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\LayeredNavigation\Test\Unit\Observer\Edit\Tab\Front;

use Magento\Backend\Block\Widget\Form\Element\Dependence;
use Magento\Framework\Event\Observer;
use Magento\Framework\Module\Manager;
use Magento\LayeredNavigation\Observer\Edit\Tab\Front\ProductAttributeFormBuildFormFieldDependenciesObserver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProductAttributeFormBuildFormFieldDependenciesObserverTest extends TestCase
{
    /**
     * @var MockObject|Manager
     */
    private Manager $moduleManager;
    /**
     * @var MockObject|Observer
     */
    private Observer $event;

    /**
     * @var MockObject|ProductAttributeFormBuildFormFieldDependenciesObserver
     */
    private ProductAttributeFormBuildFormFieldDependenciesObserver $observer;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->moduleManager = $this->createMock(Manager::class);
        $this->event = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->addMethods(['getDependencies'])
            ->getMock();
        $this->observer = new ProductAttributeFormBuildFormFieldDependenciesObserver($this->moduleManager);

        parent::setUp();
    }

    /**
     * Test case when module output is disabled
     */
    public function testExecuteDisabled(): void
    {
        $this->moduleManager->expects($this->once())
            ->method('isOutputEnabled')
            ->with('Magento_LayeredNavigation')
            ->willReturn(false);

        $this->event->expects($this->never())->method('getDependencies');

        $this->observer->execute($this->event);
    }

    /**
     * Test case when module output is enabled
     */
    public function testExecuteEnabled(): void
    {
        $this->moduleManager->expects($this->once())
            ->method('isOutputEnabled')
            ->with('Magento_LayeredNavigation')
            ->willReturn(true);

        $dependencies = $this->createMock(Dependence::class);
        $dependencies->expects($this->once())
            ->method('addFieldMap')
            ->with('is_filterable_in_search', 'filterable_in_search');
        $dependencies->expects($this->once())
            ->method('addFieldDependence')
            ->with('filterable_in_search', 'searchable', '1');
        $this->event->expects($this->once())
            ->method('getDependencies')
            ->willReturn($dependencies);

        $this->observer->execute($this->event);
    }
}
