<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LayeredNavigation\Test\Unit\Observer\Grid;

use Magento\Catalog\Block\Adminhtml\Product\Attribute\Grid;
use Magento\Framework\Event\Observer;
use Magento\Framework\Module\Manager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\LayeredNavigation\Observer\Grid\ProductAttributeGridBuildObserver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class ProductAttributeGridBuildObserverTest
 *
 * Testing adding new grid column for Layered Navigation
 */
class ProductAttributeGridBuildObserverTest extends TestCase
{
    /**
     * @var ProductAttributeGridBuildObserver
     */
    private $observer;

    /**
     * @var Manager|MockObject
     */
    private $moduleManagerMock;

    /**
     * @var Observer|MockObject
     */
    private $observerMock;

    /**
     * @var Grid|MockObject
     */
    private $gridMock;

    /**
     * Set Up
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->moduleManagerMock = $this->createMock(Manager::class);
        $this->gridMock = $this->createMock(Grid::class);
        $this->observerMock = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->setMethods(['getGrid'])
            ->getMock();

        $this->observer = $objectManager->getObject(
            ProductAttributeGridBuildObserver::class,
            [
                'moduleManager' => $this->moduleManagerMock,
            ]
        );
    }

    /**
     * Testing the column adding if the output is not enabled
     */
    public function testColumnAddingOnDisabledOutput()
    {
        $enabledOutput = false;

        $this->moduleManagerMock->expects($this->once())
            ->method('isOutputEnabled')
            ->with('Magento_LayeredNavigation')
            ->willReturn($enabledOutput);

        $this->observerMock->expects($this->never())
            ->method('getGrid');

        $this->observer->execute($this->observerMock);
    }

    /**
     * Testing the column adding if the output is enabled
     */
    public function testColumnAddingOnEnabledOutput()
    {
        $enabledOutput = true;

        $this->moduleManagerMock->expects($this->once())
            ->method('isOutputEnabled')
            ->with('Magento_LayeredNavigation')
            ->willReturn($enabledOutput);

        $this->observerMock->expects($this->once())
            ->method('getGrid')
            ->willReturn($this->gridMock);

        $this->observer->execute($this->observerMock);
    }
}
