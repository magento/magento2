<?php
/**
 * Test for validation rules implemented by XSD schema for catalog attributes configuration
 *
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
namespace Magento\Catalog\Model\Attribute\Config;

class XsdTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $_schemaFile;

    protected function setUp()
    {
        $this->_schemaFile = BP . '/app/code/Magento/Catalog/etc/catalog_attributes.xsd';
    }

    /**
     * @param string $fixtureXml
     * @param array $expectedErrors
     * @dataProvider exemplarXmlDataProvider
     */
    public function testExemplarXml($fixtureXml, array $expectedErrors)
    {
        $dom = new \Magento\Framework\Config\Dom($fixtureXml, array(), null, null, '%message%');
        $actualResult = $dom->validate($this->_schemaFile, $actualErrors);
        $this->assertEquals(empty($expectedErrors), $actualResult);
        $this->assertEquals($expectedErrors, $actualErrors);
    }

    public function exemplarXmlDataProvider()
    {
        return array(
            'valid' => array('<config><group name="test"><attribute name="attr"/></group></config>', array()),
            'empty root node' => array(
                '<config/>',
                array("Element 'config': Missing child element(s). Expected is ( group ).")
            ),
            'irrelevant root node' => array(
                '<attribute name="attr"/>',
                array("Element 'attribute': No matching global declaration available for the validation root.")
            ),
            'empty node "group"' => array(
                '<config><group name="test"/></config>',
                array("Element 'group': Missing child element(s). Expected is ( attribute ).")
            ),
            'node "group" without attribute "name"' => array(
                '<config><group><attribute name="attr"/></group></config>',
                array("Element 'group': The attribute 'name' is required but missing.")
            ),
            'node "group" with invalid attribute' => array(
                '<config><group name="test" invalid="true"><attribute name="attr"/></group></config>',
                array("Element 'group', attribute 'invalid': The attribute 'invalid' is not allowed.")
            ),
            'node "attribute" with value' => array(
                '<config><group name="test"><attribute name="attr">Invalid</attribute></group></config>',
                array("Element 'attribute': Character content is not allowed, because the content type is empty.")
            ),
            'node "attribute" with children' => array(
                '<config><group name="test"><attribute name="attr"><invalid/></attribute></group></config>',
                array("Element 'attribute': Element content is not allowed, because the content type is empty.")
            ),
            'node "attribute" without attribute "name"' => array(
                '<config><group name="test"><attribute/></group></config>',
                array("Element 'attribute': The attribute 'name' is required but missing.")
            ),
            'node "attribute" with invalid attribute' => array(
                '<config><group name="test"><attribute name="attr" invalid="true"/></group></config>',
                array("Element 'attribute', attribute 'invalid': The attribute 'invalid' is not allowed.")
            )
        );
    }
}
