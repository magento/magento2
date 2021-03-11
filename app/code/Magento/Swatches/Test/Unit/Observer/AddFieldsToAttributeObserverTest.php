<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Swatches\Test\Unit\Observer;

/**
 * Observer test
 */
class AddFieldsToAttributeObserverTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Framework\Module\Manager|\PHPUnit\Framework\MockObject\MockObject */
    protected $moduleManagerMock;

    /** @var \Magento\Config\Model\Config\Source\Yesno|\PHPUnit\Framework\MockObject\MockObject */
    protected $yesNoMock;

    /** @var \Magento\Framework\Data\Form|\PHPUnit\Framework\MockObject\MockObject */
    protected $formMock;

    /** @var \Magento\Framework\Event\Observer|\PHPUnit\Framework\MockObject\MockObject */
    protected $eventObserverMock;

    /** @var \Magento\Swatches\Observer\AddFieldsToAttributeObserver|\PHPUnit\Framework\MockObject\MockObject */
    protected $observerMock;

    protected function setUp(): void
    {
        $this->moduleManagerMock = $this->createMock(\Magento\Framework\Module\Manager::class);

        $this->yesNoMock = $this->createMock(\Magento\Config\Model\Config\Source\Yesno::class);
        $this->eventObserverMock = $this->createPartialMock(
            \Magento\Framework\Event\Observer::class,
            ['getForm', 'getEvent', 'getAttribute']
        );
        $this->formMock = $this->createPartialMock(\Magento\Framework\Data\Form::class, ['getElement']);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->observerMock = $objectManager->getObject(
            \Magento\Swatches\Observer\AddFieldsToAttributeObserver::class,
            [
                'moduleManager' => $this->moduleManagerMock,
                'yesNo' => $this->yesNoMock,
            ]
        );
    }

    /**
     * @dataProvider dataAddFields
     */
    public function testAddFields($expected)
    {
        $this->moduleManagerMock
            ->expects($this->once())
            ->method('isOutputEnabled')
            ->willReturn($expected['isOutputEnabled']);

        $this->eventObserverMock
            ->expects($this->exactly($expected['methods_count']))
            ->method('getForm')
            ->willReturn($this->formMock);

        $element = $this->createMock(\Magento\Framework\Data\Form\Element\AbstractElement::class);
        $this->formMock
            ->expects($this->exactly($expected['methods_count']))
            ->method('getElement')
            ->with('base_fieldset')
            ->willReturn($element);

        $element->expects($this->exactly($expected['addField_count']))->method('addField');
        $this->yesNoMock->expects($this->exactly($expected['yesno_count']))->method('toOptionArray');
        $this->observerMock->execute($this->eventObserverMock);
    }

    /**
     * @return array
     */
    public function dataAddFields()
    {
        return [
            [
                [
                    'isOutputEnabled' => true,
                    'methods_count' => 1,
                    'addField_count' => 2,
                    'yesno_count' => 1,
                ],
            ],
            [
                [
                    'isOutputEnabled' => false,
                    'methods_count' => 0,
                    'addField_count' => 0,
                    'yesno_count' => 0,
                ],
            ],
        ];
    }
}
