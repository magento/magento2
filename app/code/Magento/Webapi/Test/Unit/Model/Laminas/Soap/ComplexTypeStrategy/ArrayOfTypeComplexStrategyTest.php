<?php
/**
 * @see       https://github.com/laminas/laminas-soap for the canonical source repository
 * @copyright https://github.com/laminas/laminas-soap/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-soap/blob/master/LICENSE.md New BSD License
 */

namespace Magento\Webapi\Test\Unit\Model\Laminas\Soap\ComplexTypeStrategy;

use Magento\Webapi\Model\Laminas\Soap\ComplexTypeStrategy\ArrayOfTypeComplex;
use Magento\Webapi\Model\Laminas\Soap\Exception\InvalidArgumentException;
use Magento\Webapi\Model\Laminas\Soap\Wsdl;
use Magento\Webapi\Test\Unit\Model\Laminas\Soap\TestAsset;
use Magento\Webapi\Test\Unit\Model\Laminas\Soap\WsdlTestHelper;

class ArrayOfTypeComplexStrategyTest extends WsdlTestHelper
{
    public function setUp(): void
    {
        $this->strategy = new ArrayOfTypeComplex();

        parent::setUp();
    }

    public function testNestingObjectsDeepMakesNoSenseThrowingException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('ArrayOfTypeComplex cannot return nested ArrayOfObject deeper than one level');
        $this->wsdl->addComplexType(TestAsset\ComplexTest::class . '[][]');
    }

    public function testAddComplexTypeOfNonExistingClassThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Cannot add a complex type \Magento\Webapi\Test\Unit\Model\Laminas\Soap\TestAsset\UnknownClass that is not an object or where class'
        );
        $this->wsdl->addComplexType('\Magento\Webapi\Test\Unit\Model\Laminas\Soap\TestAsset\UnknownClass[]');
    }

    /**
     * @group Laminas-5046
     */
    public function testArrayOfSimpleObject()
    {
        $return = $this->wsdl->addComplexType(TestAsset\ComplexTest::class . '[]');
        $return = $this->wsdl->addComplexType(TestAsset\ComplexTest::class . '[]');
        $this->assertEquals("tns:ArrayOfComplexTest", $return);

        // single element
        $nodes = $this->xpath->query('//wsdl:types/*/xsd:complexType[@name="ComplexTest"]/xsd:all/xsd:element');
        $this->assertEquals(1, $nodes->length, 'Unable to find complex type in wsdl.');

        $this->assertEquals('var', $nodes->item(0)->getAttribute('name'), 'Invalid attribute name');
        $this->assertEquals('xsd:int', $nodes->item(0)->getAttribute('type'), 'Invalid type name');

        // array of elements
        $nodes = $this->xpath->query(
            '//wsdl:types/*/xsd:complexType[@name="ArrayOfComplexTest"]/xsd:complexContent/xsd:restriction'
        );
        $this->assertEquals(1, $nodes->length, 'Unable to find complex type array definition in wsdl.');
        $this->assertEquals(
            'soap-enc:Array',
            $nodes->item(0)->getAttribute('base'),
            'Invalid base encoding in complex type.'
        );

        $nodes = $this->xpath->query('xsd:attribute', $nodes->item(0));

        $this->assertEquals(
            'soap-enc:arrayType',
            $nodes->item(0)->getAttribute('ref'),
            'Invalid attribute reference value in complex type.'
        );
        $this->assertEquals(
            'tns:ComplexTest[]',
            $nodes->item(0)->getAttributeNS(Wsdl::WSDL_NS_URI, 'arrayType'),
            'Invalid array type reference.'
        );

        $this->documentNodesTest();
    }

    public function testThatOverridingStrategyIsReset()
    {
        $return = $this->wsdl->addComplexType(TestAsset\ComplexTest::class . '[]');
        $this->assertEquals("tns:ArrayOfComplexTest", $return);
    }

    /**
     * @group Laminas-5046
     */
    public function testArrayOfComplexObjects()
    {
        $return = $this->wsdl->addComplexType(TestAsset\ComplexObjectStructure::class . '[]');
        $this->assertEquals("tns:ArrayOfComplexObjectStructure", $return);

        $nodes = $this->xpath->query(
            '//wsdl:types/xsd:schema/xsd:complexType[@name="ComplexObjectStructure"]/xsd:all'
        );
        $this->assertEquals(4, $nodes->item(0)->childNodes->length, 'Invalid complex object definition.');

        foreach ([
                     'boolean'       => 'xsd:boolean',
                     'string'        => 'xsd:string',
                     'int'           => 'xsd:int',
                     'array'         => 'soap-enc:Array'
                 ] as $name => $type) {
            $node = $this->xpath->query('xsd:element[@name="'.$name.'"]', $nodes->item(0));
            $this->assertEquals(
                $name,
                $node->item(0)->getAttribute('name'),
                'Invalid name attribute value in complex object definition'
            );
            $this->assertEquals(
                $type,
                $node->item(0)->getAttribute('type'),
                'Invalid type name in complex object definition'
            );
        }

        // array of elements
        $nodes = $this->xpath->query(
            '//wsdl:types/*/xsd:complexType[@name="ArrayOfComplexObjectStructure"]/xsd:complexContent/xsd:restriction'
        );
        $this->assertEquals(1, $nodes->length, 'Unable to find complex type array definition in wsdl.');
        $this->assertEquals(
            'soap-enc:Array',
            $nodes->item(0)->getAttribute('base'),
            'Invalid base encoding in complex type.'
        );

        $nodes = $this->xpath->query('xsd:attribute', $nodes->item(0));

        $this->assertEquals(
            'soap-enc:arrayType',
            $nodes->item(0)->getAttribute('ref'),
            'Invalid attribute reference value in complex type.'
        );
        $this->assertEquals(
            'tns:ComplexObjectStructure[]',
            $nodes->item(0)->getAttributeNS(Wsdl::WSDL_NS_URI, 'arrayType'),
            'Invalid array type reference.'
        );


        $this->documentNodesTest();
    }

    public function testArrayOfObjectWithObject()
    {
        $return = $this->wsdl->addComplexType(TestAsset\ComplexObjectWithObjectStructure::class . '[]');
        $this->assertEquals("tns:ArrayOfComplexObjectWithObjectStructure", $return);

        // single element
        $nodes = $this->xpath->query('//wsdl:types/*/xsd:complexType[@name="ComplexTest"]/xsd:all/xsd:element');
        $this->assertEquals(1, $nodes->length, 'Unable to find complex type in wsdl.');

        $this->assertEquals('var', $nodes->item(0)->getAttribute('name'), 'Invalid attribute name');
        $this->assertEquals('xsd:int', $nodes->item(0)->getAttribute('type'), 'Invalid type name');

        // single object element
        $nodes = $this->xpath->query(
            '//wsdl:types/*/xsd:complexType[@name="ComplexObjectWithObjectStructure"]/xsd:all/xsd:element'
        );
        $this->assertEquals(1, $nodes->length, 'Unable to find complex object in wsdl.');

        $this->assertEquals(
            'object',
            $nodes->item(0)->getAttribute('name'),
            'Invalid attribute name'
        );
        $this->assertEquals(
            'tns:ComplexTest',
            $nodes->item(0)->getAttribute('type'),
            'Invalid type name'
        );
        $this->assertEquals(
            'true',
            $nodes->item(0)->getAttribute('nillable'),
            'Invalid nillable attribute value'
        );

        // array of elements
        $nodes = $this->xpath->query(
            '//wsdl:types/*/xsd:complexType[@name="ArrayOfComplexObjectWithObjectStructure"]/'
            .'xsd:complexContent/xsd:restriction'
        );
        $this->assertEquals(1, $nodes->length, 'Unable to find complex type array definition in wsdl.');
        $this->assertEquals(
            'soap-enc:Array',
            $nodes->item(0)->getAttribute('base'),
            'Invalid base encoding in complex type.'
        );

        $nodes = $this->xpath->query('xsd:attribute', $nodes->item(0));

        $this->assertEquals(
            'soap-enc:arrayType',
            $nodes->item(0)->getAttribute('ref'),
            'Invalid attribute reference value in complex type.'
        );
        $this->assertEquals(
            'tns:ComplexObjectWithObjectStructure[]',
            $nodes->item(0)->getAttributeNS(Wsdl::WSDL_NS_URI, 'arrayType'),
            'Invalid array type reference.'
        );

        $this->documentNodesTest();
    }

    /**
     * @group Laminas-4937
     */
    public function testAddingTypesMultipleTimesIsSavedOnlyOnce()
    {
        $this->wsdl->addComplexType(TestAsset\ComplexObjectWithObjectStructure::class . '[]');
        $this->wsdl->addComplexType(TestAsset\ComplexObjectWithObjectStructure::class . '[]');

        // this xpath is proper version of simpler:
        //     //*[wsdl:arrayType="tns:ComplexObjectWithObjectStructure[]"]
        // (namespaces in attributes and xpath)
        $nodes = $this->xpath->query('//*[@*[namespace-uri()="'.Wsdl::WSDL_NS_URI
            .'" and local-name()="arrayType"]="tns:ComplexObjectWithObjectStructure[]"]');
        $this->assertEquals(
            1,
            $nodes->length,
            'Invalid array of complex type array type reference detected'
        );

        $nodes = $this->xpath->query(
            '//xsd:complexType[@name="ArrayOfComplexObjectWithObjectStructure"]'
        );
        $this->assertEquals(1, $nodes->length, 'Invalid array complex type detected');

        $nodes = $this->xpath->query('//xsd:complexType[@name="ComplexTest"]');
        $this->assertEquals(1, $nodes->length, 'Invalid complex type detected');

        $this->documentNodesTest();
    }

    /**
     * @group Laminas-4937
     */
    public function testAddingSingularThenArrayTypeIsRecognizedCorretly()
    {
        $this->wsdl->addComplexType(
            TestAsset\ComplexObjectWithObjectStructure::class
        );
        $this->wsdl->addComplexType(
            TestAsset\ComplexObjectWithObjectStructure::class . '[]'
        );

        // this xpath is proper version of simpler:
        //     //*[wsdl:arrayType="tns:ComplexObjectWithObjectStructure[]"]
        // (namespaces in attributes and xpath)
        $nodes = $this->xpath->query('//*[@*[namespace-uri()="'.Wsdl::WSDL_NS_URI.
            '" and local-name()="arrayType"]="tns:ComplexObjectWithObjectStructure[]"]');
        $this->assertEquals(
            1,
            $nodes->length,
            'Invalid array of complex type array type reference detected'
        );

        $nodes = $this->xpath->query(
            '//xsd:complexType[@name="ArrayOfComplexObjectWithObjectStructure"]'
        );
        $this->assertEquals(1, $nodes->length, 'Invalid array complex type detected');

        $nodes = $this->xpath->query('//xsd:complexType[@name="ComplexTest"]');
        $this->assertEquals(1, $nodes->length, 'Invalid complex type detected');

        $this->documentNodesTest();
    }

    /**
     * @group Laminas-5149
     */
    public function testArrayOfComplexNestedObjectsIsCoveredByStrategyAndAddsAllTypesRecursivly()
    {
        $return = $this->wsdl->addComplexType(TestAsset\ComplexTypeA::class);
        $this->assertEquals("tns:ComplexTypeA", $return);


        $nodes = $this->xpath->query('//wsdl:types/xsd:schema/xsd:complexType[@name="ComplexTypeB"]/xsd:all');
        $this->assertEquals(2, $nodes->item(0)->childNodes->length, 'Invalid complex object definition.');

        foreach ([
                     'bar'  => 'xsd:string',
                     'foo'  => 'xsd:string',
                 ] as $name => $type) {
            $node = $this->xpath->query('xsd:element[@name="'.$name.'"]', $nodes->item(0));
            $this->assertEquals(
                $name,
                $node->item(0)->getAttribute('name'),
                'Invalid name attribute value in complex object definition'
            );
            $this->assertEquals(
                $type,
                $node->item(0)->getAttribute('type'),
                'Invalid type name in complex object definition'
            );
            $this->assertEquals(
                'true',
                $node->item(0)->getAttribute('nillable'),
                'Invalid nillable attribute value'
            );
        }

        // single object element
        $nodes = $this->xpath->query(
            '//wsdl:types/*/xsd:complexType[@name="ComplexTypeA"]/xsd:all/xsd:element'
        );
        $this->assertEquals(1, $nodes->length, 'Unable to find complex object in wsdl.');

        $this->assertEquals(
            'baz',
            $nodes->item(0)->getAttribute('name'),
            'Invalid attribute name'
        );
        $this->assertEquals(
            'tns:ArrayOfComplexTypeB',
            $nodes->item(0)->getAttribute('type'),
            'Invalid type name'
        );

        // array of elements
        $nodes = $this->xpath->query(
            '//wsdl:types/*/xsd:complexType[@name="ArrayOfComplexTypeB"]/xsd:complexContent/xsd:restriction'
        );
        $this->assertEquals(
            1,
            $nodes->length,
            'Unable to find complex type array definition in wsdl.'
        );
        $this->assertEquals(
            'soap-enc:Array',
            $nodes->item(0)->getAttribute('base'),
            'Invalid base encoding in complex type.'
        );

        $nodes = $this->xpath->query('xsd:attribute', $nodes->item(0));

        $this->assertEquals(
            'soap-enc:arrayType',
            $nodes->item(0)->getAttribute('ref'),
            'Invalid attribute reference value in complex type.'
        );
        $this->assertEquals(
            'tns:ComplexTypeB[]',
            $nodes->item(0)->getAttributeNS(Wsdl::WSDL_NS_URI, 'arrayType'),
            'Invalid array type reference.'
        );

        $this->documentNodesTest();
    }
}
