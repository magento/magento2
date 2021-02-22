<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Ui\Component\Listing\Columns;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponent\Processor;

/**
 * Class AbstractColumnTest
 */
abstract class AbstractColumnTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Column
     */
    protected $model;

    /**
     * @var ContextInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $contextMock;

    /**
     * @var UiComponentFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $uiComponentFactoryMock;

    /**
     * @var Processor|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $processorMock;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->contextMock = $this->getMockBuilder(ContextInterface::class)
            ->getMockForAbstractClass();
        $this->uiComponentFactoryMock = $this->getMockBuilder(UiComponentFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->processorMock = $this->getMockBuilder(Processor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->contextMock->expects($this->never())
            ->method('getProcessor')
            ->willReturn($this->processorMock);
    }

    /**
     * @return Column
     */
    abstract protected function getModel();
}
