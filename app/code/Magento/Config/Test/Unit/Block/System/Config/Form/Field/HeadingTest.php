<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Tests for \Magento\Framework\Data\Form\Field\Heading
 */
namespace Magento\Config\Test\Unit\Block\System\Config\Form\Field;

class HeadingTest extends \PHPUnit_Framework_TestCase
{
    public function testRender()
    {
        $htmlId = 'test_HTML_id';
        $label  = 'test_label';

        $elementMock = $this->getMockBuilder('Magento\Framework\Data\Form\Element\AbstractElement')
            ->disableOriginalConstructor()
            ->setMethods(['getHtmlId', 'getLabel'])
            ->getMock();
        $elementMock->expects($this->any())->method('getHtmlId')->willReturn($htmlId);
        $elementMock->expects($this->any())->method('getLabel')->willReturn($label);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $heading = $objectManager->getObject('Magento\Config\Block\System\Config\Form\Field\Heading', []);

        $html = $heading->render($elementMock);

        $this->assertEquals(
            '<tr class="system-fieldset-sub-head" id="row_' . $htmlId . '">' .
            '<td colspan="5">' .
            '<h4 id="' . $htmlId . '">' . $label . '</h4>' .
            '</td>' .
            '</tr>',
            $html
        );
    }
}
