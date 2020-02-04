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
class HtmlContentTest extends \PHPUnit\Framework\TestCase
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
        $xml = '<?xml version="1.0"?>' .
                '<layout xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">' .
                        '<block class="Magento\Customer\Block\Adminhtml\Edit\Tab\View" name="customer_edit_tab_view" ' .
                                'template="Magento_Customer::tab/view.phtml">' .
                            '<block class="Magento\Customer\Block\Adminhtml\Edit\Tab\View\PersonalInfo" '.
                                    'name="personal_info" template="Magento_Customer::tab/view/personal_info.phtml"/>' .
                        '</block>' .
                '</layout>';
        $expectedResult = [
            'xsi:type' => 'array',
            'item' => [
                'layout' => [
                    'xsi:type' => 'string',
                    'name' => 'layout',
                    'value' => ''
                ],
                'name' => [
                    'xsi:type' => 'string',
                    'name' => 'block',
                    'value' => 'customer_edit_tab_view',
                ],
            ],
        ];

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->load(dirname(__FILE__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'testForm.xml');
        $domXpath = new \DOMXPath($dom);
        $node = $domXpath->query('//form/htmlContent/block')->item(0);

        $actualResult = $this->converter->convert($node, []);
        $this->assertTrue(isset($actualResult['item']['layout']['value']));

        // assert xml structures
        $this->assertXmlStringEqualsXmlString($xml, $actualResult['item']['layout']['value']);
        $actualResult['item']['layout']['value'] = '';

        // assert that all expected keys in array are exists
        $this->assertEquals($expectedResult, $actualResult);
    }
}
