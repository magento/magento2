<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Config\Test\Unit\Block\System\Config\Form;

use Magento\Backend\Model\Url;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\Text;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\StoreManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\View\Helper\SecureHtmlRenderer;

/**
 * Test how class render field html element in Stores Configuration
 */
class FieldTest extends TestCase
{
    /**
     * @var Field
     */
    protected $_object;

    /**
     * @var MockObject
     */
    protected $_elementMock;

    /**
     * @var array
     */
    protected $_testData;

    /**
     * @var MockObject
     */
    protected $_storeManagerMock;

    /**
     * @var MockObject
     */
    protected $_layoutMock;

    protected function setUp(): void
    {
        $this->_storeManagerMock = $this->createMock(StoreManager::class);
        $secureRendererMock = $this->createMock(SecureHtmlRenderer::class);
        $secureRendererMock->method('renderEventListenerAsTag')
            ->willReturnCallback(
                function (string $event, string $js, string $selector): string {
                    return "<script>document.querySelector('$selector').$event = function () { $js };</script>";
                }
            );
        $secureRendererMock->method('renderStyleAsTag')
            ->willReturnCallback(
                function (string $style, string $selector): string {
                    return "<style>$selector { $style }</style>";
                }
            );

        $data = [
            'storeManager' => $this->_storeManagerMock,
            'urlBuilder' => $this->createMock(Url::class),
            'secureRenderer' => $secureRendererMock
        ];
        $helper = new ObjectManager($this);
        $this->_object = $helper->getObject(Field::class, $data);

        $this->_testData = [
            'htmlId' => 'test_field_id',
            'name' => 'test_name',
            'label' => 'test_label',
            'elementHTML' => 'test_html',
        ];

        $this->_elementMock = $this->getMockBuilder(Text::class)
            ->addMethods([
                'getLabel',
                'getComment',
                'getHint',
                'getScope',
                'getScopeLabel',
                'getInherit',
                'getIsDisableInheritance',
                'getCanUseWebsiteValue',
                'getCanUseDefaultValue',
                'setDisabled',
                'getTooltip'
            ])
            ->onlyMethods(['getHtmlId', 'getName', 'getElementHtml', 'setReadonly'])
            ->disableOriginalConstructor()
            ->getMock();

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
        $expected = '<td class=""><div class="hint"><div id="hint_test_field_id">' . $testHint . '</div></div>';
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
            ' class="checkbox config-inherit" checked="checked"' . ' disabled="disabled"' . ' readonly="1" />' .
            '<script>document.querySelector(\'input#test_field_id_inherit\').onclick = function () '.
            '{ toggleValueElements(this, Element.previous(this.parentNode)) };</script>';

        $expected .= '<label for="' . $this->_testData['htmlId'] . '_inherit" class="inherit">Use Website</label>';
        $actual = $this->_object->render($this->_elementMock);

        $this->assertStringContainsString($expected, $actual);
    }
}
