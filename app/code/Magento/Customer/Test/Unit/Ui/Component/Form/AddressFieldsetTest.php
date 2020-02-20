<?php
declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Ui\Component\Form;

use Magento\Customer\Ui\Component\Form\AddressFieldset;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

/**
 * Test for class \Magento\Customer\Ui\Component\Form\AddressFieldset
 */
class AddressFieldsetTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var AddressFieldset
     */
    protected $fieldset;

    /**
     * @var ContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $context;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp()
    {
        $this->context = $this->getMockForAbstractClass(
            \Magento\Framework\View\Element\UiComponent\ContextInterface::class
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
        $this->assertEquals(false, $this->fieldset->isComponentVisible());
    }
}
