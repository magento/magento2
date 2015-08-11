<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Swatches\Test\Unit\Model;

/**
 * Observer model
 */
class ObserverTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\Module\Manager|\PHPUnit_Framework_MockObject_MockObject */
    protected $moduleManagerMock;

    /** @var \Magento\Config\Model\Config\Source\Yesno|\PHPUnit_Framework_MockObject_MockObject */
    protected $yesNoMock;

    /** @var \Magento\Framework\Data\Form|\PHPUnit_Framework_MockObject_MockObject */
    protected $formMock;

    /** @var \Magento\Framework\Event\Observer|\PHPUnit_Framework_MockObject_MockObject */
    protected $eventObserverMock;

    /** @var \Magento\Swatches\Model\Observer|\PHPUnit_Framework_MockObject_MockObject */
    protected $observerMock;

    public function setUp()
    {
        $this->moduleManagerMock = $this->getMock(
            '\Magento\Framework\Module\Manager',
            [],
            [],
            '',
            false
        );

        $this->yesNoMock = $this->getMock('\Magento\Config\Model\Config\Source\Yesno', [], [], '', false);
        $this->eventObserverMock = $this->getMock(
            '\Magento\Framework\Event\Observer',
            ['getForm', 'getEvent', 'getAttribute'],
            [],
            '',
            false
        );
        $this->formMock = $this->getMock('\Magento\Framework\Data\Form', ['getElement'], [], '', false);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->observerMock = $objectManager->getObject(
            '\Magento\Swatches\Model\Observer',
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

        $element = $this->getMock('Magento\Framework\Data\Form\Element\AbstractElement', [], [], '', false);
        $this->formMock
            ->expects($this->exactly($expected['methods_count']))
            ->method('getElement')
            ->with('base_fieldset')
            ->willReturn($element);

        $element->expects($this->exactly($expected['addField_count']))->method('addField');
        $this->yesNoMock->expects($this->exactly($expected['yesno_count']))->method('toOptionArray');
        $this->observerMock->addFieldsToAttribute($this->eventObserverMock);
    }


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

    /**
     * @dataProvider dataAddSwatch
     */
    public function testAddSwatchAttributeType($exp)
    {
        $this->moduleManagerMock
            ->expects($this->once())
            ->method('isOutputEnabled')
            ->willReturn($exp['isOutputEnabled']);

        $eventMock = $this->getMock('\Magento\Framework\Event', ['getResponse'], [], '', false);
        $this->eventObserverMock
            ->expects($this->exactly($exp['methods_count']))
            ->method('getEvent')
            ->willReturn($eventMock);

        $response = $this->getMock('\Magento\Framework\DataObject', ['getTypes'], [], '', false);
        $eventMock
            ->expects($this->exactly($exp['methods_count']))
            ->method('getResponse')
            ->willReturn($response);

        $response
            ->expects($this->exactly($exp['methods_count']))
            ->method('getTypes')
            ->willReturn($exp['outputArray']);

        $this->observerMock->addSwatchAttributeType($this->eventObserverMock);
    }

    public function dataAddSwatch()
    {
        return [
            [
                [
                    'isOutputEnabled' => true,
                    'methods_count' => 1,
                    'outputArray' => []
                ]
            ],
            [
                [
                    'isOutputEnabled' => false,
                    'methods_count' => 0,
                    'outputArray' => []
                ]
            ],
        ];
    }
}
