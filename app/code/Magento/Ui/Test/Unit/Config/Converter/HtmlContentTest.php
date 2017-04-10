<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Test\Unit\Config\Converter;

use Magento\Ui\Config\Converter\HtmlContent;

/**
 * Class HtmlContentTest
 */
class HtmlContentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var HtmlContent
     */
    private $converter;

    /**
     * Set up mocks
     */
    public function setUp()
    {
        $this->converter = new HtmlContent();
    }

    public function testConvert()
    {
        $xml = trim(file_get_contents(__DIR__ . '/_files/expected.xml'));
        $expectedResult = [
            'xsi:type' => 'array',
            'item' => [
                'layout' => [
                    'xsi:type' => 'string',
                    'name' => 'layout',
                    'value' => $xml
                ],
                'name' => [
                    'xsi:type' => 'string',
                    'name' => 'block',
                    'value' => 'customer_edit_tab_view_content',
                ],
            ],
        ];

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->load(dirname(__FILE__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'testForm.xml');
        $domXpath = new \DOMXPath($dom);
        $node = $domXpath->query('//form/htmlContent')->item(0);

        $this->assertEquals($expectedResult, $this->converter->convert($node, []));
    }
}
