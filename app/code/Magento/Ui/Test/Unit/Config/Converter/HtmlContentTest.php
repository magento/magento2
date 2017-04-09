<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Test\Unit\Config\Converter;

use Magento\Ui\Config\Converter\HtmlContent;

class HtmlContentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var HtmlContent
     */
    private $converter;

    public function setUp()
    {
        $this->converter = new HtmlContent();
    }

    public function testConvert()
    {
        //@codingStandardsIgnoreStart
        $xml = <<<XML
<?xml version="1.0"?><layout xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"><htmlContent name="customer_edit_tab_view_content">
        <block class="Magento\\Customer\\Block\\Adminhtml\\Edit\\Tab\\View" name="customer_edit_tab_view" template="Magento_Customer::tab/view.phtml">
            <block class="Magento\\Customer\\Block\\Adminhtml\\Edit\\Tab\\View\\PersonalInfo" name="personal_info" template="Magento_Customer::tab/view/personal_info.phtml"/>
        </block>
    </htmlContent></layout>
XML;
        //@codingStandardsIgnoreEnd
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
