<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\Element;

use PHPUnit\Framework\TestCase;
use Magento\Framework\View\Element\FormKey;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class FormKeyTest extends TestCase
{
    /**
     * @var FormKey
     */
    protected $formKeyElement;

    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManager($this);

        $formKeyMock = $this->getMockBuilder(\Magento\Framework\Data\Form\FormKey::class)
            ->setMethods(['getFormKey'])->disableOriginalConstructor()->getMock();

        $formKeyMock->expects($this->any())
            ->method('getFormKey')
            ->will($this->returnValue('form_key'));

        $this->formKeyElement = $objectManagerHelper->getObject(
            FormKey::class,
            ['formKey' => $formKeyMock]
        );
    }

    public function testGetFormKey()
    {
        $this->assertEquals('form_key', $this->formKeyElement->getFormKey());
    }

    public function testToHtml()
    {
        $this->assertEquals(
            '<input name="form_key" type="hidden" value="form_key" />',
            $this->formKeyElement->toHtml()
        );
    }
}
