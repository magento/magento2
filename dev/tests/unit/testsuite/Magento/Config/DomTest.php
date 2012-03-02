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
 * @category    Magento
 * @package     Framework
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Magento_Config_DomTest extends PHPUnit_Framework_TestCase
{
    /**
     * @param string $xmlFile
     * @param string $newXmlFile
     * @param array $ids
     * @param string $expectedXmlFile
     * @dataProvider mergeDataProvider
     */
    public function testMerge($xmlFile, $newXmlFile, $ids, $expectedXmlFile)
    {
        $xml = file_get_contents(__DIR__ . "/_files/dom/{$xmlFile}");
        $newXml = file_get_contents(__DIR__ . "/_files/dom/{$newXmlFile}");
        $expectedXml = file_get_contents(__DIR__ . "/_files/dom/{$expectedXmlFile}");
        $config = new Magento_Config_Dom($xml, $ids);
        $config->merge($newXml);
        $this->assertXmlStringEqualsXmlString($expectedXml, $config->getDom()->saveXML());
    }

    /**
     * @return array
     */
    public function mergeDataProvider()
    {
        // note differences of XML declaration in fixture files: sometimes encoding is specified, sometimes isn't
        return array(
            array('ids.xml', 'ids_new.xml', array(
                    '/root/node/subnode'     => 'id',
                    '/root/other_node'       => 'id',
                    '/root/other_node/child' => 'identifier',
                ),
                'ids_merged.xml'
            ),
            array('no_ids.xml', 'no_ids_new.xml', array(), 'no_ids_merged.xml'),
            array('ambiguous_one.xml', 'ambiguous_new_two.xml', array(), 'ambiguous_merged.xml'),
        );
    }

    /**
     * @param string $xmlFile
     * @param string $newXmlFile
     * @dataProvider mergeExceptionDataProvider
     * @expectedException Magento_Exception
     */
    public function testMergeException($xmlFile, $newXmlFile)
    {
        $xml = file_get_contents(__DIR__ . "/_files/dom/{$xmlFile}");
        $newXml = file_get_contents(__DIR__ . "/_files/dom/{$newXmlFile}");
        $config = new Magento_Config_Dom($xml, array());
        $config->merge($newXml);
    }

    /**
     * @return array
     */
    public function mergeExceptionDataProvider()
    {
        return array(
            array('ambiguous_two.xml', 'ambiguous_new_one.xml')
        );
    }

    /**
     * @param string $xml
     * @param bool $isExpectedValid
     * @dataProvider validateDataProvider
     */
    public function testValidate($xml, $isExpectedValid)
    {
        $config = new Magento_Config_Dom($xml);
        $schema = __DIR__ . '/_files/sample.xsd';
        if ($isExpectedValid) {
            $this->assertTrue($config->validate($schema));
        } else {
            $errors = array();
            $this->assertFalse($config->validate($schema, $errors));
            $this->assertNotEmpty($errors);
            foreach ($errors as $error) {
                $this->assertInstanceOf('libXMLError', $error);
            }
        }
    }

    /**
     * @return array
     */
    public function validateDataProvider()
    {
        $validXml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<root>
    <node id="id1"/>
    <node id="id2"/>
</root>
XML;
        $invalidXml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<root>
    <node id="id1"/>
    <unknown_node/>
</root>
XML;
        return array(
            array($validXml, true),
            array($invalidXml, false),
        );
    }
}
