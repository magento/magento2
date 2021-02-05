<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\Block\System\Config\Form;

/**
 * Test how class render field html element in Stores Configuration
 */
class FieldTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Config\Block\System\Config\Form\Field
     */
    protected $_object;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_elementMock;

    /**
     * @var array
     */
    protected $_testData;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_storeManagerMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_layoutMock;

    protected function setUp(): void
    {
        $this->_storeManagerMock = $this->createMock(\Magento\Store\Model\StoreManager::class);

        $data = [
            'storeManager' => $this->_storeManagerMock,
            'urlBuilder' => $this->createMock(\Magento\Backend\Model\Url::class),
        ];
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_object = $helper->getObject(\Magento\Config\Block\System\Config\Form\Field::class, $data);

        $this->_testData = [
            'htmlId' => 'test_field_id',
            'name' => 'test_name',
            'label' => 'test_label',
            'elementHTML' => 'test_html',
        ];

        $this->_elementMock = $this->createPartialMock(
            \Magento\Framework\Data\Form\Element\Text::class,
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
                'setReadonly'
            ]
        );

        $this->_elementMock->expects(
            $this->any()
        )->method(
            'getHtmlId'
        )->willReturn(
            $this->_testData['htmlId']
        );
        $this->_elementMock->expects(
            $this->any()
        )->method(
            'getName'
        )->willReturn(
            $this->_testData['name']
        );
        $this->_elementMock->expects(
            $this->any()
        )->method(
            'getLabel'
        )->willReturn(
            $this->_testData['label']
        );
        $this->_elementMock->expects(
            $this->any()
        )->method(
            'getElementHtml'
        )->willReturn(
            $this->_testData['elementHTML']
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
        $this->_elementMock->expects($this->any())->method('getComment')->willReturn($testComment);
        $expected = '<td class="value">' .
            $this->_testData['elementHTML'] .
            '<p class="note"><span>' .
            $testComment .
            '</span></p></td>';
        $actual = $this->_object->render($this->_elementMock);
        $this->assertStringContainsString($expected, $actual);
    }

    public function testRenderValueWithTooltipBlock()
    {
        $testTooltip = 'test_tooltip';
        $this->_elementMock->expects($this->any())->method('getTooltip')->willReturn($testTooltip);
        $expected = '<td class="value with-tooltip">' .
            $this->_testData['elementHTML'] .
            '<div class="tooltip"><span class="help"><span></span></span><div class="tooltip-content">' .
            $testTooltip .
            '</div></div></td>';
        $actual = $this->_object->render($this->_elementMock);
        $this->assertStringContainsString($expected, $actual);
    }

    public function testRenderHint()
    {
        $testHint = 'test_hint';
        $this->_elementMock->expects($this->any())->method('getHint')->willReturn($testHint);
        $expected = '<td class=""><div class="hint"><div style="display: none;">' . $testHint . '</div></div>';
        $actual = $this->_object->render($this->_elementMock);
        $this->assertStringContainsString($expected, $actual);
    }

    public function testRenderScopeLabel()
    {
        $this->_storeManagerMock->expects($this->once())->method('isSingleStoreMode')->willReturn(false);

        $testScopeLabel = 'test_scope_label';
        $this->_elementMock->expects($this->any())->method('getScope')->willReturn(true);
        $this->_elementMock->expects($this->any())->method('getScopeLabel')->willReturn($testScopeLabel);

        $expected = '<tr id="row_test_field_id">' .
            '<td class="label"><label for="test_field_id">' .
            '<span data-config-scope="' . $testScopeLabel . '">test_label</span>' .
            '</label></td><td class="value">test_html</td><td class=""></td></tr>';
        $actual = $this->_object->render($this->_elementMock);

        $this->assertStringContainsString($expected, $actual);
    }

    public function testRenderInheritCheckbox()
    {
        $this->_elementMock->expects($this->any())->method('getInherit')->willReturn(true);
        $this->_elementMock->expects($this->any())->method('getCanUseWebsiteValue')->willReturn(true);
        $this->_elementMock->expects($this->any())->method('getCanUseDefaultValue')->willReturn(true);
        $this->_elementMock->expects($this->once())->method('setDisabled')->with(true);
        $this->_elementMock->method('getIsDisableInheritance')->willReturn(true);
        $this->_elementMock->method('setReadonly')->with(true);

        $expected = '<td class="use-default">';
        $expected .= '<input id="' .
            $this->_testData['htmlId'] .
            '_inherit" name="' .
            $this->_testData['name'] .
            '[inherit]" type="checkbox" value="1"' .
            ' class="checkbox config-inherit" checked="checked"' . ' disabled="disabled"' . ' readonly="1"' .
            ' onclick="toggleValueElements(this, Element.previous(this.parentNode))" /> ';

        $expected .= '<label for="' . $this->_testData['htmlId'] . '_inherit" class="inherit">Use Website</label>';
        $actual = $this->_object->render($this->_elementMock);

        $this->assertStringContainsString($expected, $actual);
    }
}
