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
namespace Magento\Webapi\Model\Soap\Wsdl;

use Zend\Soap\Wsdl;

/**
 * Complex type strategy tests.
 */
class ComplexTypeStrategyTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Webapi\Model\Config\ClassReflector\TypeProcessor|\PHPUnit_Framework_MockObject_MockObject */
    protected $_typeProcessor;

    /** @var \Magento\Webapi\Model\Soap\Wsdl|\PHPUnit_Framework_MockObject_MockObject */
    protected $_wsdl;

    /** @var ComplexTypeStrategy */
    protected $_strategy;

    /**
     * Set up strategy for test.
     */
    protected function setUp()
    {
        $this->_typeProcessor = $this->getMockBuilder(
            'Magento\Webapi\Model\Config\ClassReflector\TypeProcessor'
        )->setMethods(
            array('getTypeData')
        )->disableOriginalConstructor()->getMock();
        $this->_wsdl = $this->getMockBuilder(
            'Magento\Webapi\Model\Soap\Wsdl'
        )->setMethods(
            array('toDomDocument', 'getTypes', 'getSchema')
        )->disableOriginalConstructor()->getMock();
        $this->_strategy = new ComplexTypeStrategy($this->_typeProcessor);
        $this->_strategy->setContext($this->_wsdl);
        parent::setUp();
    }

    /**
     * Clean up.
     */
    protected function tearDown()
    {
        unset($this->_typeProcessor);
        unset($this->_strategy);
        unset($this->_wsdl);

        parent::tearDown();
    }

    /**
     * Test that addComplexType returns type WSDL name
     * if it has already been processed (registered at includedTypes in WSDL)
     */
    public function testCheckTypeName()
    {
        $testType = 'testComplexTypeName';
        $testTypeWsdlName = 'tns:' . $testType;
        $includedTypes = array($testType => $testTypeWsdlName);
        $this->_wsdl->expects($this->exactly(2))->method('getTypes')->will($this->returnValue($includedTypes));

        $this->assertEquals($testTypeWsdlName, $this->_strategy->addComplexType($testType));
    }

    /**
     * Test adding complex type with simple parameters.
     *
     * @param string $type
     * @param array $data
     * @dataProvider addComplexTypeDataProvider
     */
    public function testAddComplexTypeSimpleParameters($type, $data)
    {
        $this->_wsdl->expects($this->any())->method('getTypes')->will($this->returnValue(array()));

        $this->_wsdl->expects($this->any())->method('toDomDocument')->will($this->returnValue(new \DOMDocument()));

        $schemaMock = $this->getMock('DOMElement', [], ['a']);
        $schemaMock->expects($this->any())->method('appendChild');
        $this->_wsdl->expects($this->any())->method('getSchema')->will($this->returnValue($schemaMock));

        $this->_typeProcessor->expects(
            $this->at(0)
        )->method(
            'getTypeData'
        )->with(
            $type
        )->will(
            $this->returnValue($data)
        );

        $this->assertEquals(Wsdl::TYPES_NS . ':' . $type, $this->_strategy->addComplexType($type));
    }

    /**
     * Data provider for testAddComplexTypeSimpleParameters().
     *
     * @return array
     */
    public static function addComplexTypeDataProvider()
    {
        return array(
            'simple parameters' => array(
                'VendorModuleADataStructure',
                array(
                    'documentation' => 'test',
                    'parameters' => array(
                        'string_param' => array(
                            'type' => 'string',
                            'required' => true,
                            'documentation' => 'Required string param.'
                        ),
                        'int_param' => array(
                            'type' => 'int',
                            'required' => true,
                            'documentation' => 'Required int param.'
                        ),
                        'bool_param' => array(
                            'type' => 'boolean',
                            'required' => false,
                            'documentation' => 'Optional complex type param.{annotation:test}'
                        )
                    )
                )
            ),
            'type with call info' => array(
                'VendorModuleADataStructure',
                array(
                    'documentation' => 'test',
                    'parameters' => array(
                        'string_param' => array(
                            'type' => 'string',
                            'required' => false,
                            'documentation' => '{callInfo:VendorModuleACreate:requiredInput:conditionally}'
                        )
                    ),
                    'callInfo' => array(
                        'requiredInput' => array('yes' => array('calls' => array('VendorModuleACreate'))),
                        'returned' => array('always' => array('calls' => array('VendorModuleAGet')))
                    )
                )
            ),
            'parameter with call info' => array(
                'VendorModuleADataStructure',
                array(
                    'documentation' => 'test',
                    'parameters' => array(
                        'string_param' => array(
                            'type' => 'string',
                            'required' => false,
                            'documentation' => '{callInfo:VendorModuleACreate:requiredInput:conditionally}' .
                            '{callInfo:allCallsExcept(VendorModuleAGet):returned:always}'
                        )
                    )
                )
            ),
            'parameter with see link' => array(
                'VendorModuleADataStructure',
                array(
                    'documentation' => 'test',
                    'parameters' => array(
                        'string_param' => array(
                            'type' => 'string',
                            'required' => false,
                            'documentation' => '{seeLink:http://google.com/:title:for}'
                        )
                    )
                )
            ),
            'parameter with doc instructions' => array(
                'VendorModuleADataStructure',
                array(
                    'documentation' => 'test',
                    'parameters' => array(
                        'string_param' => array(
                            'type' => 'string',
                            'required' => false,
                            'documentation' => '{docInstructions:output:noDoc}'
                        )
                    )
                )
            )
        );
    }

    /**
     * Test adding complex type with complex parameters and arrays.
     */
    public function testAddComplexTypeComplexParameters()
    {
        $type = 'VendorModuleADataStructure';
        $parameterType = 'ComplexType';
        $typeData = array(
            'documentation' => 'test',
            'parameters' => array(
                'complex_param' => array(
                    'type' => $parameterType,
                    'required' => true,
                    'documentation' => 'complex type param.'
                )
            )
        );
        $parameterData = array(
            'documentation' => 'test',
            'parameters' => array(
                'string_param' => array(
                    'type' => 'ComplexTypeB[]',
                    'required' => true,
                    'documentation' => 'string param.'
                )
            )
        );

        $this->_wsdl->expects($this->at(0))->method('getTypes')->will($this->returnValue(array()));
        $this->_wsdl->expects(
            $this->any()
        )->method(
            'getTypes'
        )->will(
            $this->returnValue(array($type => Wsdl::TYPES_NS . ':' . $type))
        );

        $this->_wsdl->expects($this->any())->method('toDomDocument')->will($this->returnValue(new \DOMDocument()));
        $schemaMock = $this->getMock('DOMElement', [], ['a']);
        $schemaMock->expects($this->any())->method('appendChild');
        $this->_wsdl->expects($this->any())->method('getSchema')->will($this->returnValue($schemaMock));
        $this->_typeProcessor->expects(
            $this->at(0)
        )->method(
            'getTypeData'
        )->with(
            $type
        )->will(
            $this->returnValue($typeData)
        );
        $this->_typeProcessor->expects(
            $this->at(1)
        )->method(
            'getTypeData'
        )->with(
            $parameterType
        )->will(
            $this->returnValue($parameterData)
        );

        $this->assertEquals(Wsdl::TYPES_NS . ':' . $type, $this->_strategy->addComplexType($type));
    }

    /**
     * Test to verify if annotations are added correctly
     */
    public function testAddAnnotationToComplexType()
    {
        $dom = new \DOMDocument();
        $this->_wsdl->expects($this->any())->method('toDomDocument')->will($this->returnValue($dom));
        $annotationDoc = "test doc";
        $complexType = $dom->createElement(Wsdl::XSD_NS . ':complexType');
        $complexType->setAttribute('name', 'testRequest');
        $this->_strategy->addAnnotation($complexType, $annotationDoc);
        $this->assertEquals(
            $annotationDoc,
            $complexType->getElementsByTagName("xsd:documentation")->item(0)->nodeValue
        );
    }
}
