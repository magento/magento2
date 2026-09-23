<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Data\Test\Unit\Form\Element;

use Magento\Framework\Data\Form\Element\Editablemultiselect;
use Magento\Framework\DataObject;
use Magento\Framework\Escaper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Magento\Framework\Math\Random;
use Magento\Framework\View\Helper\SecureHtmlRenderer;

class EditablemultiselectTest extends TestCase
{
    /**
     * @var Editablemultiselect
     */
    protected $_model;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $testHelper = new ObjectManager($this);
        $randomMock = $this->createMock(Random::class);
        $randomMock->method('getRandomString')->willReturn('some-rando-string');
        $secureRendererMock = $this->createMock(SecureHtmlRenderer::class);
        $secureRendererMock->method('renderEventListenerAsTag')
            ->willReturnCallback(
                function (string $event, string $listener, string $selector): string {
                    return "<script>document.querySelector('{$selector}').{$event} = () => { {$listener} };</script>";
                }
            );
        $secureRendererMock->method('renderTag')
            ->willReturnCallback(
                function (string $tag, array $attrs, ?string $content): string {
                    $attrs = new DataObject($attrs);

                    return "<$tag {$attrs->serialize()}>$content</$tag>";
                }
            );
        $this->_model = $testHelper->getObject(
            Editablemultiselect::class,
            [
                'random' => $randomMock,
                'secureRenderer' => $secureRendererMock,
                'escaper' => new Escaper()
            ]
        );
        $values = [
            ['value' => 1, 'label' => 'Value1'],
            ['value' => 2, 'label' => 'Value2'],
            ['value' => 3, 'label' => 'Value3'],
        ];
        $value = [1, 3];
        $this->_model->setForm(new DataObject());
        $this->_model->setData(['values' => $values, 'value' => $value]);
    }

    public function testGetElementHtmlRendersDataAttributesWhenDisabled()
    {
        $this->_model->setDisabled(true);
        $elementHtml = $this->_model->getElementHtml();
        $this->assertStringContainsString('disabled="disabled"', $elementHtml);
        $this->assertStringContainsString('data-is-removable="no"', $elementHtml);
        $this->assertStringContainsString('data-is-editable="no"', $elementHtml);
    }

    public function testGetElementHtmlRendersRelatedJsClassInitialization()
    {
        $this->_model->setElementJsClass('CustomSelect');
        $elementHtml = $this->_model->getElementHtml();
        $this->assertStringContainsString('ElementControl = new CustomSelect(', $elementHtml);
        $this->assertStringContainsString('ElementControl.init();', $elementHtml);
    }

    #[DataProvider('disabledOptionDataProvider')]
    public function testOptionDisabledStateIsRenderedCorrectly(array $option, bool $shouldBeDisabled): void
    {
        $this->_model->setValues([$option]);

        $optionHtml = $this->extractOptionHtmlByValue($this->_model->getElementHtml(), $option['value']);

        $this->assertNotEmpty($optionHtml);

        if ($shouldBeDisabled) {
            $this->assertStringContainsString('disabled="disabled"', $optionHtml);
        } else {
            $this->assertStringNotContainsString('disabled="disabled"', $optionHtml);
        }
    }

    #[DataProvider('disabledOptionDataProvider')]
    public function testOptgroupOptionDisabledStateIsRenderedCorrectly(array $option, bool $shouldBeDisabled): void
    {
        $this->_model->setValues([
            [
                'label' => 'Group',
                'value' => [$option],
            ],
        ]);

        $optionHtml = $this->extractOptionHtmlByValue($this->_model->getElementHtml(), $option['value']);

        $this->assertNotEmpty($optionHtml);

        if ($shouldBeDisabled) {
            $this->assertStringContainsString('disabled="disabled"', $optionHtml);
        } else {
            $this->assertStringNotContainsString('disabled="disabled"', $optionHtml);
        }
    }

    public static function disabledOptionDataProvider(): iterable
    {
        yield 'explicitly_disabled' => [['value' => '1', 'label' => 'Option', 'disabled' => true], true];
        yield 'disabled_flag_as_int_1' => [['value' => '1', 'label' => 'Option', 'disabled' => 1], true];
        yield 'disabled_flag_as_string_foo' => [['value' => '1', 'label' => 'Option', 'disabled' => 'foo'], true];
        yield 'disabled_flag_as_string_1' => [['value' => '1', 'label' => 'Option', 'disabled' => '1'], true];

        yield 'explicitly_enabled' => [['value' => '1', 'label' => 'Option', 'disabled' => false], false];
        yield 'disabled_flag_as_int_0' => [['value' => '1', 'label' => 'Option', 'disabled' => 0], false];
        yield 'disabled_flag_as_string_0' => [['value' => '1', 'label' => 'Option', 'disabled' => '0'], false];
        yield 'disabled_flag_as_empty_string' => [['value' => '1', 'label' => 'Option', 'disabled' => ''], false];
        yield 'disabled_flag_as_null' => [['value' => '1', 'label' => 'Option', 'disabled' => null], false];
        yield 'disabled_flag_not_provided' => [['value' => '1', 'label' => 'Option'], false];
    }

    private function extractOptionHtmlByValue(string $elementHtml, string $value): string
    {
        $pattern = '/<option[^>]*value="' . preg_quote($value, '/') . '"[^>]*>.*?<\/option>/s';

        preg_match($pattern, $elementHtml, $matches);

        return $matches[0] ?? '';
    }
}
