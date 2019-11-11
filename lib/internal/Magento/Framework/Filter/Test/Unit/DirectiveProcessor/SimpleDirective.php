<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Filter\Test\Unit\DirectiveProcessor;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 *
 */
class SimpleDirective extends TestCase
{
    private $regex;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $directive = $objectManager->getObject(\Magento\Framework\Filter\DirectiveProcessor\SimpleDirective::class);
        $this->regex = $directive->getRegularExpression();
    }

    /**
     * @dataProvider matchesProvider
     */
    public function testMatches($input, $expected)
    {
        preg_match($this->regex, $input, $matches);

        $toAssert = [];
        foreach ($matches as $key => $value) {
            if (!is_numeric($key)) {
                $toAssert[$key] = $value;
            }
        }
        $this->assertEquals($expected, $toAssert);
    }

    public function matchesProvider()
    {
        return [
            [
                '{{dir param}}',
                [
                    'directiveName' => 'dir',
                    'quoteType' => '',
                    'value' => '',
                    'parameters' => 'param',
                ]
            ],
            [
                '{{dir param|foo}}',
                [
                    'directiveName' => 'dir',
                    'quoteType' => '',
                    'value' => '',
                    'parameters' => 'param',
                    'filters' => '|foo',
                ]
            ],
            [
                '{{dir foo bar baz}}',
                [
                    'directiveName' => 'dir',
                    'quoteType' => '',
                    'value' => '',
                    'parameters' => 'foo bar baz',
                ]
            ],
            [
                '{{dir \'foo %var "is" my name\' bar baz="bash" bash=\'bash\'}}',
                [
                    'directiveName' => 'dir',
                    'quoteType' => '\'',
                    'value' => 'foo %var "is" my name',
                    'parameters' => ' bar baz="bash" bash=\'bash\'',
                ]
            ],
            [
                '{{dir "foo %var is m\'name. a + b=\\\'c\\\'" some nonsense !@#$%^&*()_+abc.,;\'}}',
                [
                    'directiveName' => 'dir',
                    'quoteType' => '"',
                    'value' => 'foo %var is m\'name. a + b=\\\'c\\\'',
                    'parameters' => ' some nonsense !@#$%^&*()_+abc.,;\'',
                ]
            ],
            [
                '{{dir "blah" some nonsense !=@#$%^&*()_+abc.,;\'|foo|bar:_123|ridiculous-filter}}' . "\n\t"
                . '<content>' . "\n\t\t"
                . '{{foo bar}}</content>{{/dir}}',
                [
                    'directiveName' => 'dir',
                    'quoteType' => '"',
                    'value' => 'blah',
                    'parameters' => ' some nonsense !=@#$%^&*()_+abc.,;\'',
                    'content' => "\n\t" . '<content>' . "\n\t\t" . '{{foo bar}}</content>',
                    'filters' => '|foo|bar:_123|ridiculous-filter',
                ]
            ],
        ];
    }
}
