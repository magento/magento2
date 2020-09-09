<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Ui\Component\Form;

use Magento\Customer\Ui\Component\Form\AddressFieldset;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for class \Magento\Customer\Ui\Component\Form\AddressFieldset
 */
class AddressFieldsetTest extends TestCase
{
    /**
     * @var AddressFieldset
     */
    protected $fieldset;

    /**
     * @var ContextInterface|MockObject
     */
    private $context;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->context = $this->getMockForAbstractClass(
            ContextInterface::class
        );
        $this->fieldset = new AddressFieldset(
            $this->context,
            [],
            []
        );
    }

    /**
     * Run test for canShow() method
     *
     * @return void
     *
     */
    public function testCanShow()
    {
        $this->context->expects($this->atLeastOnce())->method('getRequestParam')->with('id')
            ->willReturn(1);
        $this->assertTrue($this->fieldset->isComponentVisible());
    }

    /**
     * Run test for canShow() method without customer id in context
     *
     * @return void
     *
     */
    public function testCanShowWithoutId()
    {
        $this->context->expects($this->atLeastOnce())->method('getRequestParam')->with('id')
            ->willReturn(null);
        $this->assertFalse($this->fieldset->isComponentVisible());
    }
}
