<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\User\Test\Unit\Block\Role\Tab;

use Magento\Backend\Block\Widget\Form\Element\ElementCreator;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\Form\Element\Fieldset;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\User\Block\Role;
use Magento\User\Block\Role\Tab\Info;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class InfoTest extends TestCase
{
    /**
     * @var \Magento\User\Block\Role\Tab\Info
     */
    protected $model;

    /**
     * @var FormFactory|MockObject
     */
    protected $formFactoryMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $objects = [
            [
                JsonHelper::class,
                $this->createMock(JsonHelper::class)
            ],
            [
                DirectoryHelper::class,
                $this->createMock(DirectoryHelper::class)
            ]
        ];
        $objectManager->prepareObjectManager($objects);
        $this->formFactoryMock = $this->getMockBuilder(FormFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $roleMock = $this->getMockBuilder(Role::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getData'])
            ->getMock();

        $roleMock->expects($this->any())->method('getData')->willReturn(['test_data' => 1]);

        $creatorStub = $this->createMock(ElementCreator::class);

        $this->model = $objectManager->getObject(
            Info::class,
            [
                'formFactory' => $this->formFactoryMock,
                'data' => ['role' => $roleMock],
                'creator' => $creatorStub
            ]
        );
    }

    public function testGetTabLabel()
    {
        $this->assertEquals(__('Role Info'), $this->model->getTabLabel());
    }

    public function testGetTabTitle()
    {
        $this->assertEquals(__('Role Info'), $this->model->getTabTitle());
    }

    public function testCanShowTab()
    {
        $this->assertTrue($this->model->canShowTab());
    }

    public function testIsHidden()
    {
        $this->assertFalse($this->model->isHidden());
    }

    public function testBeforeToHtml()
    {
        $formMock = $this->getMockBuilder(Form::class)
            ->disableOriginalConstructor()
            ->getMock();
        $fieldsetMock = $this->getMockBuilder(Fieldset::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->formFactoryMock->expects($this->any())->method('create')->willReturn($formMock);
        $formMock->expects($this->any())->method('addFieldSet')->willReturn($fieldsetMock);
        $fieldsetMock->expects($this->exactly(5))
            ->method('addField')
            ->willReturnCallback(
                function ($arg) {
                    static $callCount = 0;
                    $expectedArgs = [
                        'role_name',
                        'role_id',
                        'in_role_user',
                        'in_role_user_old',
                        'current_password'
                    ];
                    if ($arg == $expectedArgs[$callCount]) {
                        $callCount++;
                        return null;
                    }
                }
            );
        $this->assertInstanceOf(Info::class, $this->model->_beforeToHtml());
    }
}
