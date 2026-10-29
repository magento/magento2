<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Data\Test\Unit\Form\Element;

use Magento\Framework\Data\Form\AbstractForm;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Data\Form\Element\Select;
use Magento\Framework\Escaper;
use Magento\Framework\Math\Random;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Helper\SecureHtmlRenderer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Select::class)]
class SelectTest extends TestCase
{
    private const string ELEMENT_ID = 'test_select_element_id';
    private const string ELEMENT_NAME = 'test_select_element_name';

    /**
     * @var Select
     */
    private Select $element;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $randomMock = $this->createMock(Random::class);
        $randomMock->method('getRandomString')->willReturn('some-random-string');

        $secureRendererMock = $this->createMock(SecureHtmlRenderer::class);
        $secureRendererMock->method('renderStyleAsTag')
            ->willReturnCallback(
                function (string $style, string $selector): string {
                    return "<style>{$selector} { {$style} }</style>";
                }
            )
        ;

        /** @var Select $element */
        $element = $objectManager->getObject(
            Select::class,
            [
                'factoryElement' => $this->createMock(Factory::class),
                'factoryCollection' => $this->createMock(CollectionFactory::class),
                '_escaper' => new Escaper(),
                'random' => $randomMock,
                'secureRenderer' => $secureRendererMock,
            ]
        );

        $element->setForm($this->createMock(AbstractForm::class));
        $element->setId(self::ELEMENT_ID);
        $element->setName(self::ELEMENT_NAME);

        $this->element = $element;
    }

    #[DataProvider('disabledOptionDataProvider')]
    public function testOptionDisabledStateIsRenderedCorrectly(array $option, bool $shouldBeDisabled): void
    {
        $this->element->setValues([$option]);

        $optionHtml = $this->extractOptionHtmlByValue($this->element->getElementHtml(), $option['value']);

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
        $this->element->setValues([
            [
                'label' => 'Group',
                'value' => [$option],
            ],
        ]);

        $optionHtml = $this->extractOptionHtmlByValue($this->element->getElementHtml(), $option['value']);

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
