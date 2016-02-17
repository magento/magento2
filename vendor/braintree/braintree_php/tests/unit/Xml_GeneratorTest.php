<?php
require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';

class Braintree_Xml_GeneratorTest extends PHPUnit_Framework_TestCase
{
    function testSetsTypeAttributeForBooleans()
    {
        $expected = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<root>
 <yes type="boolean">true</yes>
 <no type="boolean">false</no>
</root>

XML;
        $xml = Braintree_Xml::buildXmlFromArray(array(
            'root' => array('yes' => true, 'no' => false)
        ));
        $this->assertEquals($expected, $xml);
    }

    function testCreatesArrays()
    {
        $expected = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<root>
 <stuff type="array">
  <item>foo</item>
  <item>bar</item>
 </stuff>
</root>

XML;
        $xml = Braintree_Xml::buildXmlFromArray(array(
            'root' => array('stuff' => array('foo', 'bar'))
        ));
        $this->assertEquals($expected, $xml);
    }

    function testCreatesWithDashes()
    {
        $expected = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<root>
 <some-stuff>
  <inner-foo type="integer">42</inner-foo>
  <bar-bar-bar type="integer">3</bar-bar-bar>
 </some-stuff>
</root>

XML;
        $xml = Braintree_Xml::buildXmlFromArray(array(
            'root' => array('someStuff' => array('innerFoo' => 42, 'barBarBar' => 3))
        ));
        $this->assertEquals($expected, $xml);
    }

    function testCreatesArraysWithBooleans()
    {
        $expected = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<root>
 <stuff type="array">
  <item>true</item>
  <item>false</item>
 </stuff>
</root>

XML;
        $xml = Braintree_Xml::buildXmlFromArray(array(
            'root' => array('stuff' => array(true, false))
        ));
        $this->assertEquals($expected, $xml);
    }

    function testHandlesEmptyArrays()
    {
        $expected = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<root>
 <stuff type="array"/>
</root>

XML;
        $xml = Braintree_Xml::buildXmlFromArray(array(
            'root' => array('stuff' => array())
        ));
        $this->assertEquals($expected, $xml);
    }

    function testEscapingSpecialChars()
    {
        $expected = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<root>
 <stuff>&lt;&gt;&amp;'&quot;</stuff>
</root>

XML;
        $xml = Braintree_Xml::buildXmlFromArray(array(
            'root' => array('stuff' => '<>&\'"')
        ));
        $this->assertEquals($expected, $xml);
    }
}
