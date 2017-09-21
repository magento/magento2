<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Filter\Test\Unit\Input;

use \Magento\Framework\Filter\Input\MaliciousCode;

class MaliciousCodeTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Framework\Filter\Input\MaliciousCode */
    protected $filter;

    protected function setUp()
    {
        $this->filter = new MaliciousCode();
        parent::setUp();
    }

    /**
     * @param string|string[] $input
     * @param string|string[] $expectedOutput
     * @dataProvider filterDataProvider
     */
    public function testFilter($input, $expectedOutput)
    {
        $this->assertEquals(
            $expectedOutput,
            $this->filter->filter($input),
            'Malicious code is not filtered out correctly.'
        );
    }

    public function filterDataProvider()
    {
        return [
            'Comments' => ['Comment /** This is omitted */ is removed', 'Comment  is removed'],
            'Tabs' => ["Tabs \t\t are removed", 'Tabs  are removed'],
            'JS' => ['JS JavaScript    : is removed', 'JS  is removed'],
            'Import' => ['Import @import directive is removed', 'Import  directive is removed'],
            'JS in styles (array of strings to be filtered)' => [
                [
                    '<element style="behavior:url(malicious.example.com)"></element>',
                    '<img src="test.gif" style="height: expression(compatMode==\'CSS1Compat\'? 200px : 300px")/>',
                ],
                [
                    '<element ></element>',
                    '<img src="test.gif" />'
                ],
            ],
            'JS attributes (array of strings to be filtered)' => [
                [
                    '<element ondblclick="SomeJavaScriptCode">',
                    '<element onclick="SomeJavaScriptCode">',
                    '<element onkeydown="SomeJavaScriptCode">',
                    '<element onkeypress="SomeJavaScriptCode">',
                    '<element onkeyup="SomeJavaScriptCode">',
                    '<element onmousedown="SomeJavaScriptCode">',
                    '<element onmousemove="SomeJavaScriptCode">',
                    '<element onmouseout="SomeJavaScriptCode">',
                    '<element onmouseover="SomeJavaScriptCode">',
                    '<element onmouseup="SomeJavaScriptCode">',
                    '<element onload="SomeJavaScriptCode">',
                    '<element onunload="SomeJavaScriptCode">',
                    '<element onerror="SomeJavaScriptCode" />',
                ],
                [
                    '<element >',
                    '<element >',
                    '<element >',
                    '<element >',
                    '<element >',
                    '<element >',
                    '<element >',
                    '<element >',
                    '<element >',
                    '<element >',
                    '<element >',
                    '<element >',
                    '<element />',
                ],
            ],
            'Prohibited tags (array of strings to be filtered)' => [
                [
                    'Tag is removed <script>SomeScript</script>',
                    'Tag is removed <meta>SomeMeta</meta>',
                    'Tag is removed <link>SomeLink</link>',
                    'Tag is removed <frame>SomeFrame</frame>',
                    'Tag is removed <iframe>SomeIFrame</iframe>',
                    'Tag is removed <object>SomeObject</object>',
                ],
                [
                    'Tag is removed SomeScript',
                    'Tag is removed SomeMeta',
                    'Tag is removed SomeLink',
                    'Tag is removed SomeFrame',
                    'Tag is removed SomeIFrame',
                    'Tag is removed SomeObject',
                ],
            ],
            'Base64' => [
                '<img alt="Embedded Image" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIA..." />',
                '<img alt="Embedded Image" />',
            ],
            'Nested malicious tags' => [
                '<scri<script>pt>alert(1);</scri<script>pt>',
                'alert(1);',
            ]
        ];
    }

    /**
     * Ensure that custom filtration regular expressions are applied.
     */
    public function testAddExpression()
    {
        $customExpression = '/<\/?(customMalicious).*>/Uis';
        $this->filter->addExpression($customExpression);
        $this->assertEquals(
            /** Tabs should be filtered out along with custom malicious code */
            'Custom malicious tag is removed customMalicious',
            $this->filter->filter(
                "Custom \tmalicious tag\t\t is removed <customMalicious>customMalicious</customMalicious>"
            ),
            'Custom filters are not applied correctly.'
        );
    }

    /**
     * Ensure that custom filtration regular expressions replace existing ones.
     */
    public function testSetExpression()
    {
        $customExpression = '/<\/?(customMalicious).*>/Uis';
        $this->filter->setExpressions([$customExpression]);
        $this->assertEquals(
            /** Tabs should not be filtered out along with custom malicious code */
            "Custom \tmalicious tag\t\t is removed customMalicious",
            $this->filter->filter(
                "Custom \tmalicious tag\t\t is removed <customMalicious>customMalicious</customMalicious>"
            ),
            'Native filters should have been replaced with custom ones.'
        );
    }
}
