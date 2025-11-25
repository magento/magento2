<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\Element;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\FormKey;
use PHPUnit\Framework\TestCase;

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
            ->onlyMethods(['getFormKey'])->disableOriginalConstructor()
            ->getMock();

        $formKeyMock->expects($this->any())
            ->method('getFormKey')
            ->willReturn('form_key');

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
