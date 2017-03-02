<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\Element;

class FormKeyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\Element\FormKey
     */
    protected $formKeyElement;

    protected function setUp()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $formKeyMock = $this->getMockBuilder(\Magento\Framework\Data\Form\FormKey::class)
            ->setMethods(['getFormKey'])->disableOriginalConstructor()->getMock();

        $formKeyMock->expects($this->any())
            ->method('getFormKey')
            ->will($this->returnValue('form_key'));

        $this->formKeyElement = $objectManagerHelper->getObject(
            \Magento\Framework\View\Element\FormKey::class,
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
