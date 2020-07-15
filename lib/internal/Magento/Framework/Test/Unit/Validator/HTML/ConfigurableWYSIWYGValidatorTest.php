<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Test\Unit\Validator\HTML;

use Magento\Framework\Validation\ValidationException;
use Magento\Framework\Validator\HTML\ConfigurableWYSIWYGValidator;
use PHPUnit\Framework\TestCase;

class ConfigurableWYSIWYGValidatorTest extends TestCase
{
    /**
     * Configurations to test.
     *
     * @return array
     */
    public function getConfigurations(): array
    {
        return [
            'no-html' => [['div'], [], [], 'just text', true],
            'allowed-tag' => [['div'], [], [], 'just text and <div>a div</div>', true],
            'restricted-tag' => [['div', 'p'], [], [], 'text and <p>a p</p>, <div>a div</div>,  <tr>a tr</tr>', false],
            'restricted-tag-wtih-attr' => [['div'], [], [], 'just text and <p class="fake-class">a p</p>', false],
            'allowed-tag-with-attr' => [['div'], [], [], 'just text and <div class="fake-class">a div</div>', false],
            'multiple-tags' => [['div', 'p'], [], [], 'just text and <div>a div</div> and <p>a p</p>', true],
            'tags-with-attrs' => [
                ['div', 'p'],
                ['class', 'style'],
                [],
                'text and <div class="fake-class">a div</div> and <p style="color: blue">a p</p>',
                true
            ],
            'tags-with-restricted-attrs' => [
                ['div', 'p'],
                ['class', 'align'],
                [],
                'text and <div class="fake-class">a div</div> and <p style="color: blue">a p</p>',
                false
            ],
            'tags-with-specific-attrs' => [
                ['div', 'a', 'p'],
                ['class'],
                ['a' => ['href'], 'div' => ['style']],
                '<div class="fake-class" style="color: blue">a div</div>, <a href="/some-path" class="a">an a</a>'
                .', <p class="p-class">a p</p>',
                true
            ],
            'tags-with-specific-restricted-attrs' => [
                ['div', 'a'],
                ['class'],
                ['a' => ['href']],
                'text and <div class="fake-class" href="what">a div</div> and <a href="/some-path" class="a">an a</a>',
                false
            ],
            'invalid-tag-with-full-config' => [
                ['div', 'a', 'p'],
                ['class', 'src'],
                ['a' => ['href'], 'div' => ['style']],
                '<div class="fake-class" style="color: blue">a div</div>, <a href="/some-path" class="a">an a</a>'
                .', <p class="p-class">a p</p>, <img src="test.jpg" />',
                false
            ],
            'invalid-html' => [
                ['div', 'a', 'p'],
                ['class', 'src'],
                ['a' => ['href'], 'div' => ['style']],
                'some </,none-> </html>',
                true
            ],
            'invalid-html-with-violations' => [
                ['div', 'a', 'p'],
                ['class', 'src'],
                ['a' => ['href'], 'div' => ['style']],
                'some </,none-> </html> <tr>some trs</tr>',
                false
            ]
        ];
    }

    /**
     * Test different configurations and content.
     *
     * @param array $allowedTags
     * @param array $allowedAttr
     * @param array $allowedTagAttrs
     * @param string $html
     * @param bool $isValid
     * @return void
     * @dataProvider getConfigurations
     */
    public function testConfigurations(
        array $allowedTags,
        array $allowedAttr,
        array $allowedTagAttrs,
        string $html,
        bool $isValid
    ): void {
        $validator = new ConfigurableWYSIWYGValidator($allowedTags, $allowedAttr, $allowedTagAttrs);
        $valid = true;
        try {
            $validator->validate($html);
        } catch (ValidationException $exception) {
            $valid = false;
        }

        self::assertEquals($isValid, $valid);
    }
}
