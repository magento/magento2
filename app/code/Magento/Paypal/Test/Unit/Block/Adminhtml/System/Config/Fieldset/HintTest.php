<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Test\Unit\Block\Adminhtml\System\Config\Fieldset;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Paypal\Block\Adminhtml\System\Config\Fieldset\Hint;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class HintTest
 */
class HintTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Hint
     */
    private $block;

    /**
     * @var AbstractElement|MockObject
     */
    private $element;

    protected function setUp()
    {
        $om = new ObjectManager($this);

        $this->element = $this->getMockBuilder(AbstractElement::class)
            ->setMethods(['getComment', 'getHtmlId'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->block = $om->getObject(Hint::class);
    }

    /**
     * @covers \Magento\Paypal\Block\Adminhtml\System\Config\Fieldset\Hint::render
     */
    public function testRender()
    {
        $expected = '<tr id="row_payment"><td colspan="1"><p class="note"><span>';
        $expected .= '<a href="http://test.com" target="_blank">Configuration Details</a>';
        $expected .= '</span></p></td></tr>';

        $this->element->expects(static::exactly(2))
            ->method('getComment')
            ->willReturn('http://test.com');

        $this->element->expects(static::once())
            ->method('getHtmlId')
            ->willReturn('payment');

        static::assertSame($expected, $this->block->render($this->element));
    }

    /**
     * @covers \Magento\Paypal\Block\Adminhtml\System\Config\Fieldset\Hint::render
     */
    public function testRenderEmptyComment()
    {
        $this->element->expects(static::once())
            ->method('getComment')
            ->willReturn('');

        $this->element->expects(static::never())
            ->method('getHtmlId');

        static::assertSame('', $this->block->render($this->element));
    }
}
