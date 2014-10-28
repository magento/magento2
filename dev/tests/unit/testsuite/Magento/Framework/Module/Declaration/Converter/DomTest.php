<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\Module\Declaration\Converter;

class DomTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Module\Declaration\Converter\Dom
     */
    protected $_converter;

    protected function setUp()
    {
        $this->_converter = new \Magento\Framework\Module\Declaration\Converter\Dom();
    }

    public function testConvertWithValidDom()
    {
        $xmlFilePath = __DIR__ . '/_files/valid_module.xml';
        $dom = new \DOMDocument();
        $dom->loadXML(file_get_contents($xmlFilePath));
        $expectedResult = include __DIR__ . '/_files/converted_valid_module.php';
        $this->assertEquals($expectedResult, $this->_converter->convert($dom));
    }

    /**
     * @param string $xmlString
     * @dataProvider testConvertWithInvalidDomDataProvider
     * @expectedException \Exception
     */
    public function testConvertWithInvalidDom($xmlString)
    {
        $dom = new \DOMDocument();
        $dom->loadXML($xmlString);
        $this->_converter->convert($dom);
    }

    public function testConvertWithInvalidDomDataProvider()
    {
        return array(
            'Module node without "name" attribute' => array('<?xml version="1.0"?><config><module /></config>'),
            'Module node without "version" attribute' => array(
                '<?xml version="1.0"?><config><module name="Module_One" /></config>'
            ),
            'Module node without "active" attribute' => array(
                '<?xml version="1.0"?><config><module name="Module_One" schema_version="1.0.0.0" /></config>'
            ),
            'Dependency module node without "name" attribute' => array(
                '<?xml dbversion="1.0"?><config><module name="Module_One" schema_version="1.0.0.0" active="true">' .
                '<sequence><module/></sequence></module></config>'
            ),
            'Dependency extension node without "name" attribute' => array(
                '<?xml dbversion="1.0"?><config><module name="Module_One" schema_version="1.0.0.0" active="true">' .
                '<depends><extension/></depends></module></config>'
            ),
            'Empty choice node' => array(
                '<?xml dbversion="1.0"?><config><module name="Module_One" schema_version="1.0.0.0" active="true">' .
                '<depends><choice/></depends></module></config>'
            )
        );
    }
}
