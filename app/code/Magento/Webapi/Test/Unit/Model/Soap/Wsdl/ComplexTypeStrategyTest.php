<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Webapi\Test\Unit\Model\Soap\Wsdl;

use DOMDocument;
use DOMElement;
use Laminas\Soap\Wsdl;
use Magento\Framework\Reflection\TypeProcessor;
use Magento\Webapi\Model\Soap\Wsdl as WebapiWsdl;
use Magento\Webapi\Model\Soap\Wsdl\ComplexTypeStrategy;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Complex type strategy tests.
 */
class ComplexTypeStrategyTest extends TestCase
{
    /**
     * @var TypeProcessor|MockObject
     */
    protected $typeProcessor;

    /**
     * @var WebapiWsdl|MockObject
     */
    protected $wsdl;

    /**
     * @var ComplexTypeStrategy
     */
    protected $strategy;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->typeProcessor = $this->getMockBuilder(TypeProcessor::class)
            ->onlyMethods(['getTypeData'])->disableOriginalConstructor()
            ->getMock();
        $this->wsdl = $this->getMockBuilder(WebapiWsdl::class)
            ->onlyMethods(['toDomDocument', 'getTypes', 'getSchema'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->strategy = new ComplexTypeStrategy($this->typeProcessor);
        $this->strategy->setContext($this->wsdl);

        parent::setUp();
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        unset($this->typeProcessor);
        unset($this->strategy);
        unset($this->wsdl);

        parent::tearDown();
    }

    /**
     * Test that addComplexType returns type WSDL name
     * if it has already been processed (registered at includedTypes in WSDL).
     *
     * @return void
     */
    public function testCheckTypeName(): void
    {
        $testType = 'testComplexTypeName';
        $testTypeWsdlName = 'tns:' . $testType;
        $includedTypes = [$testType => $testTypeWsdlName];
        $this->wsdl->expects($this->exactly(2))->method('getTypes')->willReturn($includedTypes);

        $this->assertEquals($testTypeWsdlName, $this->strategy->addComplexType($testType));
    }

    /**
     * Test adding complex type with simple parameters.
     *
     * @param string $type
     * @param array $data
     *
     * @return void
     * @dataProvider addComplexTypeDataProvider
     */
    public function testAddComplexTypeSimpleParameters($type, $data): void
    {
        $this->wsdl->expects($this->any())->method('getTypes')->willReturn([]);
        $this->wsdl->expects($this->any())->method('toDomDocument')->willReturn(new DOMDocument());
        $schemaMock = $this->getMockBuilder(DOMElement::class)
            ->setConstructorArgs(['a'])
            ->getMock();
        $schemaMock->expects($this->any())->method('appendChild');
        $this->wsdl->expects($this->any())->method('getSchema')->willReturn($schemaMock);

        $this->typeProcessor
            ->method('getTypeData')
            ->with($type)
            ->willReturn($data);

        $this->assertEquals(Wsdl::TYPES_NS . ':' . $type, $this->strategy->addComplexType($type));
    }

    /**
     * Data provider for testAddComplexTypeSimpleParameters().
     *
     * @return array
     */
    public static function addComplexTypeDataProvider(): array
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
                            'documentation' => 'Required string param.'
                        ],
                        'int_param' => [
                            'type' => 'int',
                            'required' => true,
                            'documentation' => 'Required int param.'
                        ],
                        'bool_param' => [
                            'type' => 'boolean',
                            'required' => false,
                            'documentation' => 'Optional complex type param.{annotation:test}'
                        ]
                    ]
                ]
            ],
            'type with call info' => [
                'VendorModuleADataStructure',
                [
                    'documentation' => 'test',
                    'parameters' => [
                        'string_param' => [
                            'type' => 'string',
                            'required' => false,
                            'documentation' => '{callInfo:VendorModuleACreate:requiredInput:conditionally}'
                        ],
                    ],
                    'callInfo' => [
                        'requiredInput' => ['yes' => ['calls' => ['VendorModuleACreate']]],
                        'returned' => ['always' => ['calls' => ['VendorModuleAGet']]]
                    ]
                ]
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
                            '{callInfo:allCallsExcept(VendorModuleAGet):returned:always}'
                        ]
                    ]
                ]
            ],
            'parameter with see link' => [
                'VendorModuleADataStructure',
                [
                    'documentation' => 'test',
                    'parameters' => [
                        'string_param' => [
                            'type' => 'string',
                            'required' => false,
                            'documentation' => '{seeLink:http://google.com/:title:for}'
                        ]
                    ]
                ]
            ],
            'parameter with doc instructions' => [
                'VendorModuleADataStructure',
                [
                    'documentation' => 'test',
                    'parameters' => [
                        'string_param' => [
                            'type' => 'string',
                            'required' => false,
                            'documentation' => '{docInstructions:output:noDoc}'
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * Test adding complex type with complex parameters and arrays.
     *
     * @return void
     */
    public function testAddComplexTypeComplexParameters(): void
    {
        $type = 'VendorModuleADataStructure';
        $parameterType = 'ComplexType';
        $typeData = [
            'documentation' => 'test',
            'parameters' => [
                'complex_param' => [
                    'type' => $parameterType,
                    'required' => true,
                    'documentation' => 'complex type param.'
                ]
            ]
        ];
        $parameterData = [
            'documentation' => 'test',
            'parameters' => [
                'string_param' => [
                    'type' => 'ComplexTypeB[]',
                    'required' => true,
                    'documentation' => 'string param.'
                ]
            ]
        ];

        $this->wsdl
            ->method('getTypes')
            ->willReturn([]);
        $this->wsdl->expects($this->any())
            ->method('getTypes')
            ->willReturn([$type => Wsdl::TYPES_NS . ':' . $type]);

        $this->wsdl->expects($this->any())->method('toDomDocument')->willReturn(new DOMDocument());
        $schemaMock = $this->getMockBuilder(DOMElement::class)
            ->setConstructorArgs(['a'])
            ->getMock();
        $schemaMock->expects($this->any())->method('appendChild');
        $this->wsdl->expects($this->any())->method('getSchema')->willReturn($schemaMock);
        $this->typeProcessor
            ->method('getTypeData')
            ->withConsecutive([$type], [$parameterType])
            ->willReturnOnConsecutiveCalls($typeData, $parameterData);

        $this->assertEquals(Wsdl::TYPES_NS . ':' . $type, $this->strategy->addComplexType($type));
    }

    /**
     * Test to verify if annotations are added correctly.
     *
     * @return void
     */
    public function testAddAnnotationToComplexType(): void
    {
        $dom = new DOMDocument();
        $this->wsdl->expects($this->any())->method('toDomDocument')->willReturn($dom);
        $annotationDoc = "test doc";
        $complexType = $dom->createElement(Wsdl::XSD_NS . ':complexType');
        $complexType->setAttribute('name', 'testRequest');
        $this->strategy->addAnnotation($complexType, $annotationDoc);
        $this->assertEquals(
            $annotationDoc,
            $complexType->getElementsByTagName("xsd:documentation")->item(0)->nodeValue
        );
    }
}
