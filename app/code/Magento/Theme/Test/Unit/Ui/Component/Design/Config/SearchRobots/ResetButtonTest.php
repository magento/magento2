<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Ui\Component\Design\Config\SearchRobots;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Theme\Ui\Component\Design\Config\SearchRobots\ResetButton;
use Magento\Framework\View\Element\UiComponent\Processor;
use Magento\Ui\Component\Form\Field;

class ResetButtonTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | ContextInterface
     */
    private $contextMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | UiComponentFactory
     */
    private $componentFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | ScopeConfigInterface
     */
    private $scopeConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject |
     */
    private $processorMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject |
     */
    private $wrappingComponentMock;

    /**
     * @var ResetButton
     */
    private $resetButton;

    public function setUp()
    {
        $this->contextMock = $this->getMockBuilder(ContextInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->componentFactoryMock = $this->getMockBuilder(UiComponentFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->processorMock = $this->getMockBuilder(Processor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock->expects($this->atLeastOnce())
            ->method("getProcessor")
            ->willReturn($this->processorMock);
        $this->wrappingComponentMock = $this->getMockBuilder(Field::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resetButton = new ResetButton(
            $this->contextMock,
            $this->componentFactoryMock,
            [],
            [
                'config' => [
                    'formElement' => 'button'
                ]
            ],
            $this->scopeConfigMock
        );
    }
    
    public function testPrepare()
    {
        $robotsContent = "Content";

        $this->componentFactoryMock->expects($this->once())
            ->method("create")
            ->willReturn($this->wrappingComponentMock);
        $this->wrappingComponentMock->expects($this->once())
            ->method("getContext")
            ->willReturn($this->contextMock);
        $this->scopeConfigMock->expects($this->once())
            ->method("getValue")
            ->willReturn($robotsContent);

        $this->resetButton->prepare();
        $actions = $this->resetButton->getData("config/actions");
        $this->assertEquals(json_encode($robotsContent), $actions[0]["params"][0]);
    }
}
