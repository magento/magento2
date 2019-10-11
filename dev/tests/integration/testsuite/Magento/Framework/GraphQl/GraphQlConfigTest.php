<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl;

use Magento\Framework\App\Cache;
use Magento\Framework\GraphQl\Config\Config;
use Magento\Framework\GraphQl\Config\Data\Argument;
use Magento\Framework\GraphQl\Config\Data\Enum;
use Magento\Framework\GraphQl\Config\Data\Field;
use Magento\Framework\GraphQl\Config\Data\StructureInterface;
use Magento\Framework\GraphQl\Config\Data\Type;
use Magento\Framework\GraphQl\Config\Element\EnumValue;
use Magento\Framework\GraphQl\Config\Element\InterfaceType;
use Magento\Framework\ObjectManagerInterface;

/**
 * Test of schema configuration reading and parsing
 */
class GraphQlConfigTest extends \PHPUnit\Framework\TestCase
{
   /** @var \Magento\Framework\GraphQl\Config  */
    private $model;

    protected function setUp()
    {
        /** @var ObjectManagerInterface $objectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
       /** @var Cache $cache */
        $cache = $objectManager->get(Cache::class);
        $cache->clean();
        $fileResolverMock = $this->getMockBuilder(
            \Magento\Framework\Config\FileResolverInterface::class
        )->disableOriginalConstructor()->getMock();
        $fileList = [
            file_get_contents(__DIR__ . '/_files/schemaC.graphqls'),
            file_get_contents(__DIR__ . '/_files/schemaD.graphqls')
        ];
        $fileResolverMock->expects($this->any())->method('get')->will($this->returnValue($fileList));
        $graphQlReader = $objectManager->create(
            \Magento\Framework\GraphQlSchemaStitching\GraphQlReader::class,
            ['fileResolver' => $fileResolverMock]
        );
        $reader = $objectManager->create(
            \Magento\Framework\GraphQlSchemaStitching\Reader::class,
            ['readers' => ['graphql_reader' => $graphQlReader]]
        );
        $data = $objectManager->create(
            \Magento\Framework\GraphQl\Config\Data ::class,
            ['reader' => $reader]
        );
         $this->model = $objectManager->create(\Magento\Framework\GraphQl\Config::class, ['data' =>$data]);
    }

    /**
     * tests GraphQl type's structure object
     */
    public function testGraphQlTypeAndFieldConfigStructure()
    {
        $query = 'Query';
        /** @var \Magento\Framework\GraphQl\Config\Element\Type $output */
        $output = $this->model->getConfigElement($query);
        $expectedOutputArray = require __DIR__ . '/_files/query_array_output.php';
        $this->assertEquals($output->getName(), $query);
        /** @var \Magento\Framework\GraphQl\Config\Element\Field $queryFields */
        $queryFields = $output->getFields();
        foreach (array_keys($queryFields) as $fieldKey) {
            $fieldAssertionMap = [
                ['response_field' => 'name', 'expected_value' => $queryFields[$fieldKey]->getName()],
                ['response_field' => 'type', 'expected_value' => $queryFields[$fieldKey]->getTypeName()],
                ['response_field' => 'required', 'expected_value' => $queryFields[$fieldKey]->isRequired()],
                ['response_field' => 'isList', 'expected_value' => $queryFields[$fieldKey]->isList()],
                ['response_field' => 'resolver', 'expected_value' => $queryFields[$fieldKey]->getResolver()],
                ['response_field' => 'description', 'expected_value' => $queryFields[$fieldKey]->getDescription()],
                [
                    'response_field' => 'cache',
                    'expected_value' => $queryFields[$fieldKey]->getCache(),
                    'optional' => true
                ]
            ];
            $this->assertResponseFields($expectedOutputArray['Query']['fields'][$fieldKey], $fieldAssertionMap);
            /** @var \Magento\Framework\GraphQl\Config\Element\Argument $queryFieldArguments */
            $queryFieldArguments = $queryFields[$fieldKey]->getArguments();
            foreach (array_keys($queryFieldArguments) as $argumentKey) {
                $argumentAssertionMap = [
                   ['response_field' => 'name', 'expected_value' => $queryFieldArguments[$argumentKey]->getName()],
                   ['response_field' => 'type', 'expected_value' => $queryFieldArguments[$argumentKey]->getTypeName()],
                   ['response_field' => 'description', 'expected_value' => $queryFieldArguments[$argumentKey]
                       ->getDescription()],
                   ['response_field' => 'required', 'expected_value' => $queryFieldArguments[$argumentKey]
                       ->isRequired()],
                   ['response_field' => 'isList', 'expected_value' => $queryFieldArguments[$argumentKey]->isList()],
                   ['response_field' => 'itemsRequired', 'expected_value' => $queryFieldArguments[$argumentKey]
                       ->areItemsRequired()]
                ];
                $this->assertResponseFields(
                    $expectedOutputArray['Query']['fields'][$fieldKey]['arguments'][$argumentKey],
                    $argumentAssertionMap
                );
                $this->assertEquals(
                    $expectedOutputArray['Query']['fields'][$fieldKey]['arguments'][$argumentKey]['defaultValue'],
                    $queryFieldArguments[$argumentKey]->getDefaultValue()
                );
            }
        }
    }

    /**
     * Tests Structured data object for configured GraphQL enum type.
     */
    public function testGraphQlEnumTypeConfigStructure()
    {
        $queryEnum = 'PriceAdjustmentDescriptionEnum';
        /** @var \Magento\Framework\GraphQl\Config\Element\Enum $outputEnum */
        $outputEnum = $this->model->getConfigElement($queryEnum);
        /** @var EnumValue $outputEnumValues */
        $outputEnumValues = $outputEnum->getValues();
        $expectedOutputArray = require __DIR__ . '/_files/query_array_output.php';
        $this->assertEquals($outputEnum->getName(), $queryEnum);
        $this->assertEquals($outputEnum->getDescription(), 'Description for enumType PriceAdjustmentDescriptionEnum');

        foreach (array_keys($outputEnumValues) as $outputEnumValue) {
            $this->assertEquals(
                $expectedOutputArray['PriceAdjustmentDescriptionEnum']['values'][$outputEnumValue]['name'],
                $outputEnumValues[$outputEnumValue]->getName()
            );
            $this->assertEquals(
                $expectedOutputArray['PriceAdjustmentDescriptionEnum']['values'][$outputEnumValue]['value'],
                $outputEnumValues[$outputEnumValue]->getValue()
            );
            $this->assertEquals(
                $expectedOutputArray['PriceAdjustmentDescriptionEnum']['values'][$outputEnumValue]['description'],
                $outputEnumValues[$outputEnumValue]->getDescription()
            );
        }
    }

    /**
     * Tests Structured data object for configured GraphQL type that implements an interface.
     */
    public function testGraphQlTypeThatImplementsInterface()
    {
        $typeThatImplements = 'ProductLinks';
        /** @var \Magento\Framework\GraphQl\Config\Element\Type $outputInterface */
        $outputInterface = $this->model->getConfigElement($typeThatImplements);
        $expectedOutputArray = require __DIR__ . '/_files/query_array_output.php';
        $this->assertEquals($outputInterface->getName(), $typeThatImplements);
        $outputInterfaceValues = $outputInterface->getInterfaces();
        /** @var \Magento\Framework\GraphQl\Config\Element\Field $outputInterfaceFields */
        $outputInterfaceFields =$outputInterface->getFields();
        foreach (array_keys($outputInterfaceValues) as $outputInterfaceValue) {
            $this->assertEquals(
                $expectedOutputArray['ProductLinks']['interfaces'][$outputInterfaceValue]['interface'],
                $outputInterfaceValues[$outputInterfaceValue]['interface']
            );
            $this->assertEquals(
                $expectedOutputArray['ProductLinks']['interfaces'][$outputInterfaceValue]['copyFields'],
                $outputInterfaceValues[$outputInterfaceValue]['copyFields']
            );
        }
        foreach (array_keys($outputInterfaceFields) as $outputInterfaceField) {
            $this->assertEquals(
                $expectedOutputArray['ProductLinks']['fields'][$outputInterfaceField]['name'],
                $outputInterfaceFields[$outputInterfaceField]->getName()
            );
            $this->assertEquals(
                $expectedOutputArray['ProductLinks']['fields'][$outputInterfaceField]['type'],
                $outputInterfaceFields[$outputInterfaceField]->getTypeName()
            );
            $this->assertEquals(
                $expectedOutputArray['ProductLinks']['fields'][$outputInterfaceField]['required'],
                $outputInterfaceFields[$outputInterfaceField]->isRequired()
            );
            $this->assertEquals(
                $expectedOutputArray['ProductLinks']['fields'][$outputInterfaceField]['description'],
                $outputInterfaceFields[$outputInterfaceField]->getDescription()
            );
            $this->assertEmpty($outputInterfaceFields[$outputInterfaceField]->getArguments());
        }
    }

    public function testGraphQlInterfaceConfigElement()
    {
        $interfaceType ='ProductLinksInterface';
        /** @var InterfaceType $outputConfigElement */
        $outputConfigElement = $this->model->getConfigElement($interfaceType);
        $expectedOutput = require __DIR__ . '/_files/query_array_output.php';
        $this->assertEquals($outputConfigElement->getName(), $expectedOutput['ProductLinksInterface']['name']);
        $this->assertEquals(
            $outputConfigElement->getTypeResolver(),
            $expectedOutput['ProductLinksInterface']['typeResolver']
        );
        $this->assertEquals(
            $outputConfigElement->getDescription(),
            $expectedOutput['ProductLinksInterface']['description']
        );
    }

    /**
     * @param array $actualResponse
     * @param array $assertionMap ['response_field_name' => 'response_field_value', ...]
     *                         OR [['response_field' => $field, 'expected_value' => $value], ...]
     */
    private function assertResponseFields($actualResponse, $assertionMap)
    {
        foreach ($assertionMap as $key => $assertionData) {
            $expectedValue = isset($assertionData['expected_value'])
                ? $assertionData['expected_value']
                : $assertionData;
            $responseField = isset($assertionData['response_field']) ? $assertionData['response_field'] : $key;
            $this->assertNotNull(
                $expectedValue,
                "Value of '{$responseField}' field must not be NULL"
            );
            $optionalField = isset($assertionData['optional']) ? $assertionData['optional'] : false;
            if (!$optionalField || isset($actualResponse[$responseField])) {
                $this->assertEquals(
                    $expectedValue,
                    $actualResponse[$responseField],
                    "Value of '{$responseField}' field in response does not match expected value: "
                    . var_export($expectedValue, true)
                );
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        /** @var ObjectManagerInterface $objectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var Cache $cache */
        $cache = $objectManager->get(Cache::class);
        $cache->clean();
    }
}
