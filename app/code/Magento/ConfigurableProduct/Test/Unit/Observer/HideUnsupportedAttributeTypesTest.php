<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Observer;

use Magento\ConfigurableProduct\Observer\HideUnsupportedAttributeTypes;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\Form\Element\Select;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for Magento\ConfigurableProduct\Observer\HideUnsupportedAttributeTypes
 */
class HideUnsupportedAttributeTypesTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
    }

    /**
     * @return void
     */
    public function testExecuteWhenBlockNotPassed()
    {
        $target = $this->createTarget($this->createRequestMock(false));
        $event = $this->createEventMock();
        $this->assertNull($target->execute($event));
    }

    /**
     * @param RequestInterface|\PHPUnit\Framework\MockObject\MockObject $request
     * @param array $supportedTypes
     * @return HideUnsupportedAttributeTypes
     */
    private function createTarget(MockObject $request, array $supportedTypes = [])
    {
        return $this->objectManager->getObject(
            HideUnsupportedAttributeTypes::class,
            [
                'request' => $request,
                'supportedTypes' => $supportedTypes
            ]
        );
    }

    /**
     * @param $popup
     * @param string $productTab
     * @return MockObject
     */
    private function createRequestMock($popup, $productTab = 'variations')
    {
        $request = $this->getMockBuilder(RequestInterface::class)
            ->onlyMethods(['getParam'])
            ->getMockForAbstractClass();
        $request->method('getParam')
            ->willReturnCallback(
                function ($name) use ($popup, $productTab) {
                    switch ($name) {
                        case 'popup':
                            return $popup;
                        case 'product_tab':
                            return $productTab;
                        default:
                            return null;
                    }
                }
            );
        return $request;
    }

    /**
     * @param \PHPUnit\Framework\MockObject\MockObject|null $form
     * @return EventObserver|\PHPUnit\Framework\MockObject\MockObject
     * @internal param null|MockObject $block
     */
    private function createEventMock(?MockObject $form = null)
    {
        $event = $this->getMockBuilder(EventObserver::class)
            ->addMethods(['getForm', 'getBlock'])
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->any())
            ->method('getForm')
            ->willReturn($form);
        return $event;
    }

    /**
     * @dataProvider executeDataProvider
     */
    public function testExecuteWithDefaultTypes(array $supportedTypes, array $originalValues, array $expectedValues)
    {
        $target = $this->createTarget($this->createRequestMock(true), $supportedTypes);
        $event = $this->createEventMock($this->createForm($originalValues, $expectedValues));
        $this->assertNull($target->execute($event));
    }

    /**
     * @return array
     */
    public static function executeDataProvider()
    {
        return [
            'testWithDefaultTypes' => [
                'supportedTypes' => ['select'],
                'originalValues' => [
                    self::createFrontendInputValue('text2', 'Text2'),
                    self::createFrontendInputValue('select', 'Select'),
                    self::createFrontendInputValue('text', 'Text'),
                    self::createFrontendInputValue('multiselect', 'Multiselect'),
                    self::createFrontendInputValue('text3', 'Text3'),
                ],
                'expectedValues' => [
                    self::createFrontendInputValue('select', 'Select'),
                ],
            ],
            'testWithCustomTypes' => [
                'supportedTypes' => ['select', 'custom_type', 'second_custom_type'],
                'originalValues' => [
                    self::createFrontendInputValue('custom_type', 'CustomType'),
                    self::createFrontendInputValue('text2', 'Text2'),
                    self::createFrontendInputValue('select', 'Select'),
                    self::createFrontendInputValue('text', 'Text'),
                    self::createFrontendInputValue('second_custom_type', 'SecondCustomType'),
                    self::createFrontendInputValue('multiselect', 'Multiselect'),
                    self::createFrontendInputValue('text3', 'Text3'),
                ],
                'expectedValues' => [
                    self::createFrontendInputValue('custom_type', 'CustomType'),
                    self::createFrontendInputValue('select', 'Select'),
                    self::createFrontendInputValue('second_custom_type', 'SecondCustomType'),
                ],
            ]
        ];
    }

    /**
     * @param $value
     * @param $label
     * @return array
     */
    private static function createFrontendInputValue($value, $label)
    {
        return ['value' => $value, 'label' => $label];
    }

    /**
     * @param array $originalValues
     * @param array $expectedValues
     * @return MockObject
     */
    private function createForm(array $originalValues = [], array $expectedValues = [])
    {
        $form = $this->getMockBuilder(Form::class)
            ->onlyMethods(['getElement'])
            ->disableOriginalConstructor()
            ->getMock();
        $frontendInput = $this->getMockBuilder(Select::class)
            ->addMethods(['getValues', 'setValues'])
            ->disableOriginalConstructor()
            ->getMock();
        $frontendInput->expects($this->once())
            ->method('getValues')
            ->willReturn($originalValues);
        $frontendInput->expects($this->once())
            ->method('setValues')
            ->with($expectedValues)
            ->willReturnSelf();
        $form->expects($this->once())
            ->method('getElement')
            ->with('frontend_input')
            ->willReturn($frontendInput);
        return $form;
    }
}
