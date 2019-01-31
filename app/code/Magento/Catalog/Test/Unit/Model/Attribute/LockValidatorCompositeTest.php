<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Attribute;

class LockValidatorCompositeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\Attribute\LockValidatorComposite
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    protected function setUp()
    {
        $this->objectManagerMock = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCompositionsWithInvalidValidatorInstance()
    {
        $validators = [\Magento\Catalog\Model\Attribute\Backend\Startdate::class];
        $this->model = new \Magento\Catalog\Model\Attribute\LockValidatorComposite(
            $this->objectManagerMock,
            $validators
        );
    }

    public function testValidateWithValidValidatorInstance()
    {
        $validators = [\Magento\Catalog\Model\Attribute\LockValidatorComposite::class];
        $lockValidatorMock = $this->createMock(\Magento\Catalog\Model\Attribute\LockValidatorInterface::class);
        $this->objectManagerMock->expects(
            $this->any()
        )->method(
            'get'
        )->with(
            \Magento\Catalog\Model\Attribute\LockValidatorComposite::class
        )->will(
            $this->returnValue($lockValidatorMock)
        );

        $this->model = new \Magento\Catalog\Model\Attribute\LockValidatorComposite(
            $this->objectManagerMock,
            $validators
        );
        $abstractModelHelper = $this->createMock(\Magento\Catalog\Model\Product::class);
        $lockValidatorMock->expects($this->once())->method('validate')->with($abstractModelHelper);
        $this->model->validate($abstractModelHelper);
    }
}
