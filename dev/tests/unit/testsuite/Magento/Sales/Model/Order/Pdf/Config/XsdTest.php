<?php
/**
 * Test for validation rules implemented by XSD schema for sales PDF rendering configuration
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
namespace Magento\Sales\Model\Order\Pdf\Config;

class XsdTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected static $_schemaPath;

    /**
     * @var string
     */
    protected static $_schemaFilePath;

    public static function setUpBeforeClass()
    {
        self::$_schemaPath = BP . '/app/code/Magento/Sales/etc/pdf.xsd';
        self::$_schemaFilePath = BP . '/app/code/Magento/Sales/etc/pdf_file.xsd';
    }

    /**
     * @param string $fixtureXml
     * @param array $expectedErrors
     * @dataProvider schemaByExemplarDataProvider
     */
    public function testSchemaByExemplar($fixtureXml, array $expectedErrors)
    {
        $this->_testSchema(self::$_schemaPath, $fixtureXml, $expectedErrors);
    }

    /**
     * @param string $fixtureXml
     * @param array $expectedErrors
     * @dataProvider fileSchemaByExemplarDataProvider
     */
    public function testFileSchemaByExemplar($fixtureXml, array $expectedErrors)
    {
        $this->_testSchema(self::$_schemaFilePath, $fixtureXml, $expectedErrors);
    }

    /**
     * Test schema against exemplar data
     *
     * @param string $schema
     * @param string $fixtureXml
     * @param array $expectedErrors
     */
    protected function _testSchema($schema, $fixtureXml, array $expectedErrors)
    {
        $dom = new \Magento\Framework\Config\Dom($fixtureXml, array(), null, null, '%message%');
        $actualResult = $dom->validate($schema, $actualErrors);
        $this->assertEquals(empty($expectedErrors), $actualResult);
        $this->assertEquals($expectedErrors, $actualErrors);
    }

    /**
     * @return array
     */
    public function schemaByExemplarDataProvider()
    {
        $result = $this->_getExemplarTestData();

        $result['non-valid totals missing title'] = array(
            '<config><totals><total name="i1"><source_field>foo</source_field></total></totals></config>',
            array(
                'Element \'total\': Missing child element(s). Expected is one of ( title, title_source_field, ' .
                'font_size, display_zero, sort_order, model, amount_prefix ).'
            )
        );
        $result['non-valid totals missing source_field'] = array(
            '<config><totals><total name="i1"><title>Title</title></total></totals></config>',
            array(
                'Element \'total\': Missing child element(s). Expected is one of ( source_field, ' .
                'title_source_field, font_size, display_zero, sort_order, model, amount_prefix ).'
            )
        );

        return $result;
    }

    /**
     * @return array
     */
    public function fileSchemaByExemplarDataProvider()
    {
        $result = $this->_getExemplarTestData();

        $result['valid totals missing title'] = array(
            '<config><totals><total name="i1"><source_field>foo</source_field></total></totals></config>',
            array()
        );
        $result['valid totals missing source_field'] = array(
            '<config><totals><total name="i1"><title>Title</title></total></totals></config>',
            array()
        );

        return $result;
    }

    /**
     * Return use cases, common for both merged configuration and individual files.
     * Reused by appropriate data providers.
     *
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _getExemplarTestData()
    {
        return array(
            'valid empty' => array('<config/>', array()),
            'valid empty renderers' => array('<config><renderers/></config>', array()),
            'valid empty totals' => array('<config><totals/></config>', array()),
            'valid empty renderers and totals' => array('<config><renderers/><totals/></config>', array()),
            'non-valid unknown node in <config>' => array(
                '<config><unknown/></config>',
                array('Element \'unknown\': This element is not expected.')
            ),
            'valid pages' => array(
                '<config><renderers><page type="p1"/><page type="p2"/></renderers></config>',
                array()
            ),
            'non-valid non-unique pages' => array(
                '<config><renderers><page type="p1"/><page type="p1"/></renderers></config>',
                array(
                    'Element \'page\': Duplicate key-sequence [\'p1\'] ' .
                    'in unique identity-constraint \'uniquePageRenderer\'.'
                )
            ),
            'non-valid unknown node in renderers' => array(
                '<config><renderers><unknown/></renderers></config>',
                array('Element \'unknown\': This element is not expected. Expected is ( page ).')
            ),
            'valid page renderers' => array(
                '<config><renderers><page type="p1"><renderer product_type="prt1">Class\A</renderer>' .
                '<renderer product_type="prt2">Class\B</renderer></page></renderers></config>',
                array()
            ),
            'non-valid non-unique page renderers' => array(
                '<config><renderers><page type="p1"><renderer product_type="prt1">Class\A</renderer>' .
                '<renderer product_type="prt1">Class\B</renderer></page></renderers></config>',
                array(
                    'Element \'renderer\': Duplicate key-sequence [\'prt1\'] ' .
                    'in unique identity-constraint \'uniqueProductTypeRenderer\'.'
                )
            ),
            'non-valid empty renderer class name' => array(
                '<config><renderers><page type="p1"><renderer product_type="prt1"/></page></renderers></config>',
                array(
                    'Element \'renderer\': [facet \'pattern\'] The value \'\' is not accepted ' .
                    'by the pattern \'[A-Z][a-zA-Z\d]*(\\\\[A-Z][a-zA-Z\d]*)*\'.',
                    'Element \'renderer\': \'\' is not a valid value of the atomic type \'classNameType\'.'
                )
            ),
            'non-valid unknown node in page' => array(
                '<config><renderers><page type="p1"><unknown/></page></renderers></config>',
                array('Element \'unknown\': This element is not expected. Expected is ( renderer ).')
            ),
            'valid totals' => array(
                '<config><totals><total name="i1"><title>Title1</title><source_field>src_fld1</source_field></total>' .
                '<total name="i2"><title>Title2</title><source_field>src_fld2</source_field></total>' .
                '</totals></config>',
                array()
            ),
            'non-valid non-unique total items' => array(
                '<config><totals><total name="i1"><title>Title1</title><source_field>src_fld1</source_field></total>' .
                '<total name="i1"><title>Title2</title><source_field>src_fld2</source_field></total>' .
                '</totals></config>',
                array(
                    'Element \'total\': Duplicate key-sequence [\'i1\'] ' .
                    'in unique identity-constraint \'uniqueTotalItem\'.'
                )
            ),
            'non-valid unknown node in total items' => array(
                '<config><totals><unknown/></totals></config>',
                array('Element \'unknown\': This element is not expected. Expected is ( total ).')
            ),
            'non-valid totals empty title' => array(
                '<config><totals><total name="i1"><title/><source_field>foo</source_field></total></totals></config>',
                array(
                    'Element \'title\': [facet \'minLength\'] The value has a length of \'0\'; ' .
                    'this underruns the allowed minimum length of \'1\'.',
                    'Element \'title\': \'\' is not a valid value of the atomic type \'nonEmptyString\'.'
                )
            ),
            'non-valid totals empty source_field' => array(
                '<config><totals><total name="i1"><title>Title</title><source_field/></total></totals></config>',
                array(
                    'Element \'source_field\': [facet \'pattern\'] The value \'\' is not accepted ' .
                    'by the pattern \'[a-z0-9_]+\'.',
                    'Element \'source_field\': \'\' is not a valid value of the atomic type \'fieldType\'.'
                )
            ),
            'non-valid totals empty title_source_field' => array(
                '<config><totals><total name="i1"><title>Title</title><source_field>foo</source_field>' .
                '<title_source_field/></total></totals></config>',
                array(
                    'Element \'title_source_field\': [facet \'pattern\'] The value \'\' is not accepted ' .
                    'by the pattern \'[a-z0-9_]+\'.',
                    'Element \'title_source_field\': \'\' is not a valid value of the atomic type \'fieldType\'.'
                )
            ),
            'non-valid totals bad model' => array(
                '<config><totals><total name="i1"><title>Title</title><source_field>foo</source_field>' .
                '<model>a model</model></total></totals></config>',
                array(
                    'Element \'model\': [facet \'pattern\'] The value \'a model\' is not accepted ' .
                    'by the pattern \'[A-Z][a-zA-Z\d]*(\\\\[A-Z][a-zA-Z\d]*)*\'.',
                    'Element \'model\': \'a model\' is not a valid value of the atomic type \'classNameType\'.'
                )
            ),
            'valid totals title_source_field' => array(
                '<config><totals><total name="i1"><title>Title</title><source_field>foo</source_field>' .
                '<title_source_field>bar</title_source_field></total></totals></config>',
                array()
            ),
            'valid totals model' => array(
                '<config><totals><total name="i1"><title>Title</title><source_field>foo</source_field>' .
                '<model>Class\A</model></total></totals></config>',
                array()
            ),
            'valid totals font_size' => array(
                '<config><totals><total name="i1"><title>Title</title><source_field>foo</source_field>' .
                '<font_size>9</font_size></total></totals></config>',
                array()
            ),
            'non-valid totals font_size 0' => array(
                '<config><totals><total name="i1"><title>Title</title><source_field>foo</source_field>' .
                '<font_size>0</font_size></total></totals></config>',
                array('Element \'font_size\': \'0\' is not a valid value of the atomic type \'xs:positiveInteger\'.')
            ),
            'non-valid totals font_size' => array(
                '<config><totals><total name="i1"><title>Title</title><source_field>foo</source_field>' .
                '<font_size>A</font_size></total></totals></config>',
                array('Element \'font_size\': \'A\' is not a valid value of the atomic type \'xs:positiveInteger\'.')
            ),
            'valid totals display_zero' => array(
                '<config><totals><total name="i1"><title>Title</title><source_field>foo</source_field>' .
                '<display_zero>1</display_zero></total></totals></config>',
                array()
            ),
            'valid totals display_zero true' => array(
                '<config><totals><total name="i1"><title>Title</title><source_field>foo</source_field>' .
                '<display_zero>true</display_zero></total></totals></config>',
                array()
            ),
            'non-valid totals display_zero' => array(
                '<config><totals><total name="i1"><title>Title</title><source_field>foo</source_field>' .
                '<display_zero>A</display_zero></total></totals></config>',
                array('Element \'display_zero\': \'A\' is not a valid value of the atomic type \'xs:boolean\'.')
            ),
            'valid totals sort_order' => array(
                '<config><totals><total name="i1"><title>Title</title><source_field>foo</source_field>' .
                '<sort_order>100</sort_order></total></totals></config>',
                array()
            ),
            'valid totals sort_order 0' => array(
                '<config><totals><total name="i1"><title>Title</title><source_field>foo</source_field>' .
                '<sort_order>0</sort_order></total></totals></config>',
                array()
            ),
            'non-valid totals sort_order' => array(
                '<config><totals><total name="i1"><title>Title</title><source_field>foo</source_field>' .
                '<sort_order>A</sort_order></total></totals></config>',
                array(
                    'Element \'sort_order\': \'A\' is not a valid value ' .
                    'of the atomic type \'xs:nonNegativeInteger\'.'
                )
            ),
            'valid totals title with translate attribute' => array(
                '<config><totals><total name="i1"><title translate="true">Title</title>' .
                '<source_field>foo</source_field></total></totals></config>',
                array()
            ),
            'non-valid totals title with bad translate attribute' => array(
                '<config><totals><total name="i1"><title translate="unknown">Title</title>' .
                '<source_field>foo</source_field></total></totals></config>',
                array(
                    'Element \'title\', attribute \'translate\': \'unknown\' is not a valid value ' .
                    'of the atomic type \'xs:boolean\'.'
                )
            )
        );
    }
}
