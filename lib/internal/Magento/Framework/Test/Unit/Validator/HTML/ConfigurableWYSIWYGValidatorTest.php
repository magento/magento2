<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Test\Unit\Validator\HTML;

use Magento\Framework\Validation\ValidationException;
use Magento\Framework\Validator\HTML\ConfigurableWYSIWYGValidator;
use Magento\Framework\Validator\HTML\AttributeValidatorInterface;
use Magento\Framework\Validator\HTML\TagValidatorInterface;
use PHPUnit\Framework\TestCase;

class ConfigurableWYSIWYGValidatorTest extends TestCase
{
    /**
     * Configurations to test.
     *
     * @return array
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getConfigurations(): array
    {
        return [
            'no-html' => [['div'], [], [], 'just text', true, [], []],
            'allowed-tag' => [['div'], [], [], 'just text and <div>a div</div>', true, [], []],
            'restricted-tag' => [
                ['div', 'p'],
                [],
                [],
                'text and <p>a p</p>, <div>a div</div>,  <tr>a tr</tr>',
                false,
                [],
                []
            ],
            'restricted-tag-wtih-attr' => [
                ['div'],
                [],
                [],
                'just text and <p class="fake-class">a p</p>',
                false,
                [],
                []
            ],
            'allowed-tag-with-attr' => [
                ['div'],
                [],
                [],
                'just text and <div class="fake-class">a div</div>',
                false,
                [],
                []
            ],
            'multiple-tags' => [['div', 'p'], [], [], 'just text and <div>a div</div> and <p>a p</p>', true, [], []],
            'tags-with-attrs' => [
                ['div', 'p'],
                ['class', 'style'],
                [],
                'text and <div class="fake-class">a div</div> and <p style="color: blue">a p</p>',
                true,
                [],
                []
            ],
            'tags-with-restricted-attrs' => [
                ['div', 'p'],
                ['class', 'align'],
                [],
                'text and <div class="fake-class">a div</div> and <p style="color: blue">a p</p>',
                false,
                [],
                []
            ],
            'tags-with-specific-attrs' => [
                ['div', 'a', 'p'],
                ['class'],
                ['a' => ['href'], 'div' => ['style']],
                '<div class="fake-class" style="color: blue">a div</div>, <a href="/some-path" class="a">an a</a>'
                .', <p class="p-class">a p</p>',
                true,
                [],
                []
            ],
            'tags-with-specific-restricted-attrs' => [
                ['div', 'a'],
                ['class'],
                ['a' => ['href']],
                'text and <div class="fake-class" href="what">a div</div> and <a href="/some-path" class="a">an a</a>',
                false,
                [],
                []
            ],
            'invalid-tag-with-full-config' => [
                ['div', 'a', 'p'],
                ['class', 'src'],
                ['a' => ['href'], 'div' => ['style']],
                '<div class="fake-class" style="color: blue">a div</div>, <a href="/some-path" class="a">an a</a>'
                .', <p class="p-class">a p</p>, <img src="test.jpg" />',
                false,
                [],
                []
            ],
            'invalid-html' => [
                ['div', 'a', 'p'],
                ['class', 'src'],
                ['a' => ['href'], 'div' => ['style']],
                'some </,none-> </html>',
                true,
                [],
                []
            ],
            'invalid-html-with-violations' => [
                ['div', 'a', 'p'],
                ['class', 'src'],
                ['a' => ['href'], 'div' => ['style']],
                'some </,none-> </html> <tr>some trs</tr>',
                false,
                [],
                []
            ],
            'invalid-html-attributes' => [
                ['div', 'a', 'p'],
                ['class', 'src'],
                [],
                'some <div class="value">DIV</div>',
                false,
                ['class' => false],
                []
            ],
            'ignored-html-attributes' => [
                ['div', 'a', 'p'],
                ['class', 'src'],
                [],
                'some <div class="value">DIV</div>',
                true,
                ['src' => false, 'class' => true],
                []
            ],
            'valid-html-attributes' => [
                ['div', 'a', 'p'],
                ['class', 'src'],
                [],
                'some <div class="value">DIV</div>',
                true,
                ['src' => true, 'class' => true],
                []
            ],
            'invalid-allowed-tag' => [
                ['div'],
                ['class', 'src'],
                [],
                '<div class="some-class" src="some-src">IS A DIV</div>',
                false,
                [],
                ['div' => ['class' => false]]
            ],
            'valid-allowed-tag' => [
                ['div'],
                ['class', 'src'],
                [],
                '<div class="some-class">IS A DIV</div>',
                true,
                [],
                ['div' => ['src' => false]]
            ]
        ];
    }

    /**
     * Test different configurations and content.
     *
     * @param string[] $allowedTags
     * @param string[] $allowedAttr
     * @param string[][] $allowedTagAttrs
     * @param string $html
     * @param bool $isValid
     * @param bool[] $attributeValidityMap
     * @param bool[][] $tagValidators
     * @return void
     *
     * @dataProvider getConfigurations
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function testConfigurations(
        array $allowedTags,
        array $allowedAttr,
        array $allowedTagAttrs,
        string $html,
        bool $isValid,
        array $attributeValidityMap,
        array $tagValidators
    ): void {
        $attributeValidator = $this->getMockForAbstractClass(AttributeValidatorInterface::class);
        $attributeValidator->method('validate')
            ->willReturnCallback(
                function (string $tag, string $attribute) use ($attributeValidityMap): void {
                    if (array_key_exists($attribute, $attributeValidityMap) && !$attributeValidityMap[$attribute]) {
                        throw new ValidationException(__('Invalid attribute for %1', $tag));
                    }
                }
            );
        $attrValidators = [];
        foreach (array_keys($attributeValidityMap) as $attr) {
            $attrValidators[$attr] = [$attributeValidator];
        }
        $tagValidatorsMocks = [];
        foreach ($tagValidators as $tag => $allowedAttributes) {
            $mock = $this->getMockForAbstractClass(TagValidatorInterface::class);
            $mock->method('validate')
                ->willReturnCallback(
                    function (string $givenTag, array $attrs) use ($tag, $allowedAttributes): void {
                        if ($givenTag !== $tag) {
                            throw new \RuntimeException();
                        }
                        foreach (array_keys($attrs) as $attr) {
                            if (array_key_exists($attr, $allowedAttributes) && !$allowedAttributes[$attr]) {
                                throw new ValidationException(__('Invalid tag'));
                            }
                        }
                    }
                );
            $tagValidatorsMocks[$tag] = [$mock];
        }
        $validator = new ConfigurableWYSIWYGValidator(
            $allowedTags,
            $allowedAttr,
            $allowedTagAttrs,
            $attrValidators,
            $tagValidatorsMocks
        );
        $valid = true;
        try {
            $validator->validate($html);
        } catch (ValidationException $exception) {
            $valid = false;
        }

        self::assertEquals($isValid, $valid);
    }
}
