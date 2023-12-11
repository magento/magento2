<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Attribute;

use Magento\Catalog\Model\Attribute\Backend\Startdate;
use Magento\Catalog\Model\Attribute\LockValidatorComposite;
use Magento\Catalog\Model\Attribute\LockValidatorInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LockValidatorCompositeTest extends TestCase
{
    /**
     * @var LockValidatorComposite
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $objectManagerMock;

    protected function setUp(): void
    {
        $this->objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);
    }

    public function testCompositionsWithInvalidValidatorInstance()
    {
        $this->expectException('InvalidArgumentException');
        $validators = [Startdate::class];
        $this->model = new LockValidatorComposite(
            $this->objectManagerMock,
            $validators
        );
    }

    public function testValidateWithValidValidatorInstance()
    {
        $validators = [LockValidatorComposite::class];
        $lockValidatorMock = $this->getMockForAbstractClass(LockValidatorInterface::class);
        $this->objectManagerMock->expects(
            $this->any()
        )->method(
            'get'
        )->with(
            LockValidatorComposite::class
        )->willReturn(
            $lockValidatorMock
        );

        $this->model = new LockValidatorComposite(
            $this->objectManagerMock,
            $validators
        );
        $abstractModelHelper = $this->createMock(Product::class);
        $lockValidatorMock->expects($this->once())->method('validate')->with($abstractModelHelper);
        $this->model->validate($abstractModelHelper);
    }
}
