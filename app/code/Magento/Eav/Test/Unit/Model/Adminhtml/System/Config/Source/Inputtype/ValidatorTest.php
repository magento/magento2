<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Test\Unit\Model\Adminhtml\System\Config\Source\Inputtype;

class ValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Eav\Model\Adminhtml\System\Config\Source\Inputtype\Validator
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $helperMock;

    protected function setUp()
    {
        $validatorData = ['type'];
        $this->helperMock = $this->getMock('\Magento\Eav\Helper\Data', [], [], '', false);
        $this->helperMock->expects($this->once())->method('getInputTypesValidatorData')->willReturn($validatorData);
        $this->model = new \Magento\Eav\Model\Adminhtml\System\Config\Source\Inputtype\Validator($this->helperMock);
    }

    public function testAddInputType()
    {
        $this->model->addInputType('new_type');
        $this->assertEquals(['type', 'new_type'], $this->model->getHaystack());
    }
}
