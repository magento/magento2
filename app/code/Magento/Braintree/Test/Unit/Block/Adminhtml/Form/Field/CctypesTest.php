<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Unit\Block\Adminhtml\Form\Field;

class CctypesTest extends \PHPUnit_Framework_TestCase
{
    public function testToHtml()
    {
        $ccTypeSourceMock = $this->getMockBuilder('\Magento\Braintree\Model\Source\CcType')
            ->disableOriginalConstructor()
            ->setMethods(['toOptionArray'])
            ->getMock();

        $ccTypeSourceMock->expects($this->once())
            ->method('toOptionArray')
            ->willReturn([['value' => 'US', 'label' => 'US']]);

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $ccTypes = $objectManagerHelper->getObject(
            'Magento\Braintree\Block\Adminhtml\Form\Field\Cctypes',
            [
                'ccTypeSource' => $ccTypeSourceMock
            ]
        );

        $result = $ccTypes->_toHtml();
        $expected = '<select name="" id="" class="cc-type-select" title="" multiple="multiple">'
            . '<option value="" ></option></select>';
        $this->assertSame($expected, $result);
    }
}
