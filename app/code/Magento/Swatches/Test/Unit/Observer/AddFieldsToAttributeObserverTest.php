<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Swatches\Test\Unit\Observer;

use Magento\Config\Model\Config\Source\Yesno;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Event\Observer;
use Magento\Framework\Module\Manager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Swatches\Observer\AddFieldsToAttributeObserver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Observer test
 */
class AddFieldsToAttributeObserverTest extends TestCase
{
    /** @var Manager|MockObject */
    protected $moduleManagerMock;

    /** @var Yesno|MockObject */
    protected $yesNoMock;

    /** @var Form|MockObject */
    protected $formMock;

    /** @var Observer|MockObject */
    protected $eventObserverMock;

    /** @var AddFieldsToAttributeObserver|MockObject */
    protected $observerMock;

    protected function setUp(): void
    {
        $this->moduleManagerMock = $this->createMock(Manager::class);

        $this->yesNoMock = $this->createMock(Yesno::class);
        $this->eventObserverMock = $this->getMockBuilder(Observer::class)
            ->addMethods(['getForm', 'getAttribute'])
            ->onlyMethods(['getEvent'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->formMock = $this->createPartialMock(Form::class, ['getElement']);

        $objectManager = new ObjectManager($this);
        $this->observerMock = $objectManager->getObject(
            AddFieldsToAttributeObserver::class,
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

        $element = $this->createMock(AbstractElement::class);
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
