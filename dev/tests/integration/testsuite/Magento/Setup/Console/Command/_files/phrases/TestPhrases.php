<?php
/***
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

use Magento\Framework\Phrase;

/**
 * @SuppressWarnings(PHPMD)
 */
class TestPhrases
{
    public function awesomeFunction()
    {
        $str1 = 'str1';
        $str2 = 'str2';

        // Simple
        $simpleCases = [
            new Phrase('simple text'),
            new Phrase('simple text with 1 string literal placeholder %1', 'arg'),
            new Phrase('simple text with 1 variable placeholder %1', $str1),
            new Phrase('simple text with multiple placeholders %1 %2', $str1, $str2),
        ];

        // Phrase objects
        $phraseObjects = [
            // Single concatenation
            new Phrase('first part' . ' second part'),
            new Phrase('first part' . ' second part' . ' third part'),

            // Multiple concatenation
            new Phrase('first part' . ' second part with one string literal placeholder %1', 'arg'),
            new Phrase('first part of concat' . ' second part with one variable placeholder %1', $str1),
            new Phrase('first part of concat' . ' second part with two placeholders %1, %2', $str1, $str2),
            new Phrase('first part of concat' . ' second part' . ' third part with one placeholder %1', 'arg'),
            new Phrase('first part of concat' . ' second part' . ' third part with two placeholders %1, %2', $str1, $str2),

            // Escaped quotes
            new Phrase('string with escaped \'single quotes\''),
            new Phrase('string with placeholder in escaped single quotes \'%1\'', 'arg'),
            new Phrase('string with "double quotes"'),
            new Phrase('string with placeholder in double quotes "%1"', 'arg'),
        ];

        $singleQuoteTranslateFunctions = [
            // Single concatenation
            __('first part' . ' second part'),
            __('first part' . ' second part' . ' third part'),

            // Multiple concatenation
            __('first part' . ' second part with one string literal placeholder %1', 'arg'),
            __('first part of concat' . ' second part with one variable placeholder %1', $str1),
            __('first part of concat' . ' second part with two placeholders %1, %2', $str1, $str2),
            __('first part of concat' . ' second part' . ' third part with one placeholder %1', 'arg'),
            __('first part of concat' . ' second part' . ' third part with two placeholders %1, %2', $str1, $str2),

            // Escaped quotes
            __('string with escaped \'single quotes\''),
            __('string with placeholder in escaped single quotes \'%1\'', 'arg'),
            __('string with "double quotes"'),
            __('string with placeholder in double quotes "%1"', 'arg'),
        ];

        $doubleQuoteTranslateFunctions = [
            // Single concatenation
            __("first part" . " second part"),
            __("first part" . " second part" . " third part"),

            // Multiple concatenation
            __("first part" . " second part with one string literal placeholder %1", "arg"),
            __("first part of concat" . " second part with one variable placeholder %1", $str1),
            __("first part of concat" . " second part with two placeholders %1, %2", $str1, $str2),
            __("first part of concat" . " second part" . " third part with one placeholder %1", "arg"),
            __("first part of concat" . " second part" . " third part with two placeholders %1, %2", $str1, $str2),

            // Escaped quotes
            __("string with 'single quotes'"),
            __("string with placeholder in single quotes '%1'", "arg"),
            __("string with escaped \"double quotes\""),
            __("string with placeholder in escaped double quotes \"%1\"", "arg"),
        ];
    }
}
