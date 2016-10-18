<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\Block\System\Config\Form;

/**
 * Test how class render field html element in Stores Configuration
 *
 * @package Magento\Config\Test\Unit\Block\System\Config\Form
 */
class FieldTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Config\Block\System\Config\Form\Field
     */
    protected $_object;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_elementMock;

    /**
     * @var array
     */
    protected $_testData;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_storeManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_layoutMock;

    protected function setUp()
    {
        $this->_storeManagerMock = $this->getMock(
            'Magento\Store\Model\StoreManager',
            [],
            [],
            '',
            false,
            false
        );

        $data = [
            'storeManager' => $this->_storeManagerMock,
            'urlBuilder' => $this->getMock('Magento\Backend\Model\Url', [], [], '', false),
        ];
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_object = $helper->getObject('Magento\Config\Block\System\Config\Form\Field', $data);

        $this->_testData = [
            'htmlId' => 'test_field_id',
            'name' => 'test_name',
            'label' => 'test_label',
            'elementHTML' => 'test_html',
        ];

        $this->_elementMock = $this->getMock(
            'Magento\Framework\Data\Form\Element\Text',
            [
                'getHtmlId',
                'getName',
                'getLabel',
                'getElementHtml',
                'getComment',
                'getHint',
                'getScope',
                'getScopeLabel',
                'getInherit',
                'getIsDisableInheritance',
                'getCanUseWebsiteValue',
                'getCanUseDefaultValue',
                'setDisabled',
                'getTooltip',
            ],
            [],
            '',
            false,
            false,
            true
        );

        $this->_elementMock->expects(
            $this->any()
        )->method(
            'getHtmlId'
        )->will(
            $this->returnValue($this->_testData['htmlId'])
        );
        $this->_elementMock->expects(
            $this->any()
        )->method(
            'getName'
        )->will(
            $this->returnValue($this->_testData['name'])
        );
        $this->_elementMock->expects(
            $this->any()
        )->method(
            'getLabel'
        )->will(
            $this->returnValue($this->_testData['label'])
        );
        $this->_elementMock->expects(
            $this->any()
        )->method(
            'getElementHtml'
        )->will(
            $this->returnValue($this->_testData['elementHTML'])
        );
    }

    public function testRenderHtmlIdLabelInputElementName()
    {
        $expected = '<tr id="row_' . $this->_testData['htmlId'] . '">';
        $expected .= '<td class="label"><label for="' .
            $this->_testData['htmlId'] .
            '"><span>' .
            $this->_testData['label'] .
            '</span></label></td>';
        $expected .= '<td class="value">' . $this->_testData['elementHTML'] . '</td>';
        $expected .= '<td class=""></td></tr>';

        $actual = $this->_object->render($this->_elementMock);

        $this->assertEquals($expected, $actual);
    }

    public function testRenderValueWithCommentBlock()
    {
        $testComment = 'test_comment';
        $this->_elementMock->expects($this->any())->method('getComment')->will($this->returnValue($testComment));
        $expected = '<td class="value">' .
            $this->_testData['elementHTML'] .
            '<p class="note"><span>' .
            $testComment .
            '</span></p></td>';
        $actual = $this->_object->render($this->_elementMock);
        $this->assertContains($expected, $actual);
    }

    public function testRenderValueWithTooltipBlock()
    {
        $testTooltip = 'test_tooltip';
        $this->_elementMock->expects($this->any())->method('getTooltip')->will($this->returnValue($testTooltip));
        $expected = '<td class="value with-tooltip">' .
            $this->_testData['elementHTML'] .
            '<div class="tooltip"><span class="help"><span></span></span><div class="tooltip-content">' .
            $testTooltip .
            '</div></div></td>';
        $actual = $this->_object->render($this->_elementMock);
        $this->assertContains($expected, $actual);
    }

    public function testRenderHint()
    {
        $testHint = 'test_hint';
        $this->_elementMock->expects($this->any())->method('getHint')->will($this->returnValue($testHint));
        $expected = '<td class=""><div class="hint"><div style="display: none;">' . $testHint . '</div></div>';
        $actual = $this->_object->render($this->_elementMock);
        $this->assertContains($expected, $actual);
    }

    public function testRenderScopeLabel()
    {
        $this->_storeManagerMock->expects($this->once())->method('isSingleStoreMode')->will($this->returnValue(false));

        $testScopeLabel = 'test_scope_label';
        $this->_elementMock->expects($this->any())->method('getScope')->will($this->returnValue(true));
        $this->_elementMock->expects($this->any())->method('getScopeLabel')->will($this->returnValue($testScopeLabel));

        $expected = '<tr id="row_test_field_id">' .
            '<td class="label"><label for="test_field_id">' .
            '<span data-config-scope="' . $testScopeLabel . '">test_label</span>' .
            '</label></td><td class="value">test_html</td><td class=""></td></tr>';
        $actual = $this->_object->render($this->_elementMock);

        $this->assertContains($expected, $actual);
    }

    public function testRenderInheritCheckbox()
    {
        $this->_elementMock->expects($this->any())->method('getInherit')->will($this->returnValue(true));
        $this->_elementMock->expects($this->any())->method('getCanUseWebsiteValue')->will($this->returnValue(true));
        $this->_elementMock->expects($this->any())->method('getCanUseDefaultValue')->will($this->returnValue(true));
        $this->_elementMock->expects($this->once())->method('setDisabled')->with(true);
        $this->_elementMock->expects($this->once())->method('getIsDisableInheritance')->willReturn(true);

        $expected = '<td class="use-default">';
        $expected .= '<input id="' .
            $this->_testData['htmlId'] .
            '_inherit" name="' .
            $this->_testData['name'] .
            '[inherit]" type="checkbox" value="1"' .
            ' class="checkbox config-inherit" checked="checked"' . ' disabled="disabled"' .
            ' onclick="toggleValueElements(this, Element.previous(this.parentNode))" /> ';

        $expected .= '<label for="' . $this->_testData['htmlId'] . '_inherit" class="inherit">Use Website</label>';
        $actual = $this->_object->render($this->_elementMock);

        $this->assertContains($expected, $actual);
    }
}
