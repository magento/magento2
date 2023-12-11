<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\LayeredNavigation\Test\Unit\Observer\Edit\Tab\Front;

use Magento\Config\Model\Config\Source\Yesno;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\Form\Element\Fieldset;
use Magento\Framework\Event\Observer;
use Magento\Framework\Module\Manager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\LayeredNavigation\Observer\Edit\Tab\Front\ProductAttributeFormBuildFrontTabObserver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit Test for \Magento\LayeredNavigation\Observer\Edit\Tab\Front\ProductAttributeFormBuildFrontTabObserver
 */
class ProductAttributeFormBuildFrontTabObserverTest extends TestCase
{
    /**
     * @var MockObject|Observer
     */
    private $eventObserverMock;

    /**
     * @var MockObject|Yesno
     */
    private $optionListLock;

    /**
     * @var MockObject|Manager
     */
    private $moduleManagerMock;

    /**
     * @var ProductAttributeFormBuildFrontTabObserver
     */
    private $observer;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->optionListLock = $this->createMock(Yesno::class);
        $this->moduleManagerMock = $this->createMock(Manager::class);
        $this->eventObserverMock = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->setMethods(['getForm'])
            ->getMock();

        $objectManager = new ObjectManager($this);
        $this->observer = $objectManager->getObject(
            ProductAttributeFormBuildFrontTabObserver::class,
            [
                'optionList' => $this->optionListLock,
                'moduleManager' => $this->moduleManagerMock,
            ]
        );
    }

    /**
     * Test case when module output is disabled
     */
    public function testExecuteWhenOutputDisabled(): void
    {
        $this->moduleManagerMock->expects($this->once())
            ->method('isOutputEnabled')
            ->with('Magento_LayeredNavigation')
            ->willReturn(false);

        $this->eventObserverMock->expects($this->never())->method('getForm');

        $this->observer->execute($this->eventObserverMock);
    }

    /**
     * Test case when module output is enabled
     */
    public function testExecuteWhenOutputEnabled(): void
    {
        $this->moduleManagerMock->expects($this->once())
            ->method('isOutputEnabled')
            ->with('Magento_LayeredNavigation')
            ->willReturn(true);

        $fieldsetMock = $this->createMock(Fieldset::class);
        $fieldsetMock->expects($this->exactly(3))->method('addField');
        $formMock = $this->createMock(Form::class);
        $formMock->expects($this->once())
            ->method('getElement')
            ->with('front_fieldset')
            ->willReturn($fieldsetMock);

        $this->eventObserverMock->expects($this->once())
            ->method('getForm')
            ->willReturn($formMock);

        $this->observer->execute($this->eventObserverMock);
    }
}
