<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Test\Unit\Block\Role\Tab;

class InfoTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\User\Block\Role\Tab\Info
     */
    protected $model;

    /**
     * @var \Magento\Framework\Data\FormFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $formFactoryMock;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->formFactoryMock = $this->getMockBuilder('Magento\Framework\Data\FormFactory')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $roleMock = $this->getMockBuilder('Magento\User\Block\Role')
            ->disableOriginalConstructor()
            ->setMethods(['getData'])
            ->getMock();

        $roleMock->expects($this->any())->method('getData')->willReturn(['test_data' => 1]);

        $this->model = $objectManager->getObject(
            'Magento\User\Block\Role\Tab\Info',
            [
                'formFactory' => $this->formFactoryMock,
                'data' => ['role' => $roleMock]
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
        $formMock = $this->getMockBuilder('Magento\Framework\Data\Form')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $fieldsetMock = $this->getMockBuilder('Magento\Framework\Data\Form\Element\Fieldset')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->formFactoryMock->expects($this->any())->method('create')->willReturn($formMock);
        $formMock->expects($this->any())->method('addFieldSet')->willReturn($fieldsetMock);
        $fieldsetMock->expects($this->exactly(5))
            ->method('addField')
            ->withConsecutive(
                ['role_name'],
                ['role_id'],
                ['in_role_user'],
                ['in_role_user_old'],
                ['current_password']
            );
        $this->assertInstanceOf('Magento\User\Block\Role\Tab\Info', $this->model->_beforeToHtml());
    }
}
