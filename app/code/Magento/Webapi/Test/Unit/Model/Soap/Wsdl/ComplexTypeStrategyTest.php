<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Webapi\Test\Unit\Model\Soap\Wsdl;

use \Magento\Webapi\Model\Soap\Wsdl\ComplexTypeStrategy;

use Zend\Soap\Wsdl;

/**
 * Complex type strategy tests.
 */
class ComplexTypeStrategyTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Framework\Reflection\TypeProcessor|\PHPUnit\Framework\MockObject\MockObject */
    protected $_typeProcessor;

    /** @var \Magento\Webapi\Model\Soap\Wsdl|\PHPUnit\Framework\MockObject\MockObject */
    protected $_wsdl;

    /** @var ComplexTypeStrategy */
    protected $_strategy;

    /**
     * Set up strategy for test.
     */
    protected function setUp(): void
    {
        $this->_typeProcessor = $this->getMockBuilder(
            \Magento\Framework\Reflection\TypeProcessor::class
        )->setMethods(
            ['getTypeData']
        )->disableOriginalConstructor()->getMock();
        $this->_wsdl = $this->getMockBuilder(
            \Magento\Webapi\Model\Soap\Wsdl::class
        )->setMethods(
            ['toDomDocument', 'getTypes', 'getSchema']
        )->disableOriginalConstructor()->getMock();
        $this->_strategy = new ComplexTypeStrategy($this->_typeProcessor);
        $this->_strategy->setContext($this->_wsdl);
        parent::setUp();
    }

    /**
     * Clean up.
     */
    protected function tearDown(): void
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
        $includedTypes = [$testType => $testTypeWsdlName];
        $this->_wsdl->expects($this->exactly(2))->method('getTypes')->willReturn($includedTypes);

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
        $this->_wsdl->expects($this->any())->method('getTypes')->willReturn([]);

        $this->_wsdl->expects($this->any())->method('toDomDocument')->willReturn(new \DOMDocument());

        $schemaMock = $this->getMockBuilder(\DOMElement::class)
            ->setConstructorArgs(['a'])
            ->getMock();
        $schemaMock->expects($this->any())->method('appendChild');
        $this->_wsdl->expects($this->any())->method('getSchema')->willReturn($schemaMock);

        $this->_typeProcessor->expects(
            $this->at(0)
        )->method(
            'getTypeData'
        )->with(
            $type
        )->willReturn(
            $data
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
        return [
            'simple parameters' => [
                'VendorModuleADataStructure',
                [
                    'documentation' => 'test',
                    'parameters' => [
                        'string_param' => [
                            'type' => 'string',
                            'required' => true,
                            'documentation' => 'Required string param.',
                        ],
                        'int_param' => [
                            'type' => 'int',
                            'required' => true,
                            'documentation' => 'Required int param.',
                        ],
                        'bool_param' => [
                            'type' => 'boolean',
                            'required' => false,
                            'documentation' => 'Optional complex type param.{annotation:test}',
                        ],
                    ]
                ],
            ],
            'type with call info' => [
                'VendorModuleADataStructure',
                [
                    'documentation' => 'test',
                    'parameters' => [
                        'string_param' => [
                            'type' => 'string',
                            'required' => false,
                            'documentation' => '{callInfo:VendorModuleACreate:requiredInput:conditionally}',
                        ],
                    ],
                    'callInfo' => [
                        'requiredInput' => ['yes' => ['calls' => ['VendorModuleACreate']]],
                        'returned' => ['always' => ['calls' => ['VendorModuleAGet']]],
                    ]
                ],
            ],
            'parameter with call info' => [
                'VendorModuleADataStructure',
                [
                    'documentation' => 'test',
                    'parameters' => [
                        'string_param' => [
                            'type' => 'string',
                            'required' => false,
                            'documentation' => '{callInfo:VendorModuleACreate:requiredInput:conditionally}' .
                            '{callInfo:allCallsExcept(VendorModuleAGet):returned:always}',
                        ],
                    ]
                ],
            ],
            'parameter with see link' => [
                'VendorModuleADataStructure',
                [
                    'documentation' => 'test',
                    'parameters' => [
                        'string_param' => [
                            'type' => 'string',
                            'required' => false,
                            'documentation' => '{seeLink:http://google.com/:title:for}',
                        ],
                    ]
                ],
            ],
            'parameter with doc instructions' => [
                'VendorModuleADataStructure',
                [
                    'documentation' => 'test',
                    'parameters' => [
                        'string_param' => [
                            'type' => 'string',
                            'required' => false,
                            'documentation' => '{docInstructions:output:noDoc}',
                        ],
                    ]
                ],
            ]
        ];
    }

    /**
     * Test adding complex type with complex parameters and arrays.
     */
    public function testAddComplexTypeComplexParameters()
    {
        $type = 'VendorModuleADataStructure';
        $parameterType = 'ComplexType';
        $typeData = [
            'documentation' => 'test',
            'parameters' => [
                'complex_param' => [
                    'type' => $parameterType,
                    'required' => true,
                    'documentation' => 'complex type param.',
                ],
            ],
        ];
        $parameterData = [
            'documentation' => 'test',
            'parameters' => [
                'string_param' => [
                    'type' => 'ComplexTypeB[]',
                    'required' => true,
                    'documentation' => 'string param.',
                ],
            ],
        ];

        $this->_wsdl->expects($this->at(0))->method('getTypes')->willReturn([]);
        $this->_wsdl->expects(
            $this->any()
        )->method(
            'getTypes'
        )->willReturn(
            [$type => Wsdl::TYPES_NS . ':' . $type]
        );

        $this->_wsdl->expects($this->any())->method('toDomDocument')->willReturn(new \DOMDocument());
        $schemaMock = $this->getMockBuilder(\DOMElement::class)
            ->setConstructorArgs(['a'])
            ->getMock();
        $schemaMock->expects($this->any())->method('appendChild');
        $this->_wsdl->expects($this->any())->method('getSchema')->willReturn($schemaMock);
        $this->_typeProcessor->expects(
            $this->at(0)
        )->method(
            'getTypeData'
        )->with(
            $type
        )->willReturn(
            $typeData
        );
        $this->_typeProcessor->expects(
            $this->at(1)
        )->method(
            'getTypeData'
        )->with(
            $parameterType
        )->willReturn(
            $parameterData
        );

        $this->assertEquals(Wsdl::TYPES_NS . ':' . $type, $this->_strategy->addComplexType($type));
    }

    /**
     * Test to verify if annotations are added correctly
     */
    public function testAddAnnotationToComplexType()
    {
        $dom = new \DOMDocument();
        $this->_wsdl->expects($this->any())->method('toDomDocument')->willReturn($dom);
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
