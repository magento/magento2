<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\GraphQl;

use Magento\Framework\App\Cache;
use Magento\Framework\Config\FileResolverInterface;
use Magento\Framework\GraphQl\Config\Element\Argument;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\ObjectManagerInterface;

class GraphQlConfigTest extends \PHPUnit\Framework\TestCase
{
   /** @var Config  */
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
       // $fileList = file_get_contents(__DIR__ . '/_files/graphql_config1.xml');
        $fileList = [
            file_get_contents(__DIR__ . '/_files/graphql_config1.xml'),
            file_get_contents(__DIR__ . '/_files/graphql_config2.xml')
        ];

        $fileResolverMock->expects($this->any())->method('get')->will($this->returnValue($fileList));
        /** @var FileResolverInterface $fileResolver */
       // $fileResolver = $objectManager->get(FileResolverInterface::class);
       // $fileList = file_get_contents(__DIR__ . '/_files/graphql_config1.xml');
      //  $fileResolver->get($fileList,'global');
        $xmlReader = $objectManager->create(\Magento\Framework\GraphQl\Config\XmlReader::class,
            ['fileResolver' => $fileResolverMock]
            );

        $reader = $objectManager->create(
            \Magento\Framework\GraphQl\Config\Reader::class,
            ['readers' => ['xmlReader' =>$xmlReader]]
        );
        $data = $objectManager->create(\Magento\Framework\GraphQl\Config\Data ::class,
            ['reader' => $reader]
            );
         $this->model = $objectManager->create(\Magento\Framework\GraphQl\Config::class, ['data' =>$data]);
    }

    /**
     * Testing if GraphQl Xml reader is able to merge xmls based on graphql/di.xml and transforms to a normalized structure
     */
    public function testGraphQlTypeAndFieldConfigStructure()
    {
        $query = 'Query';

        /** @var \Magento\Framework\GraphQl\Config\ConfigElementInterface $outputType */
        $output = $this->model->getConfigElement($query);
        $expectedOutputArray = require __DIR__ . '/_files/query_array_output.php';
        $this->assertEquals($output->getName(), $query);

        /** @var Field $queryFields */
        $queryFields = $output->getFields();

        foreach (array_keys($queryFields) as $fieldKey) {
            $this->assertEquals($expectedOutputArray['Query']['fields'][$fieldKey]['name'], $queryFields[$fieldKey]->getName());
            $this->assertEquals($expectedOutputArray['Query']['fields'][$fieldKey]['type'],$queryFields[$fieldKey]->getType());
            $this->assertEquals($expectedOutputArray['Query']['fields'][$fieldKey]['resolver'],$queryFields[$fieldKey]->getResolver());
            /** @var Argument $queryFieldArguments */
            $queryFieldArguments = $queryFields[$fieldKey]->getArguments();
            foreach(array_keys($queryFieldArguments) as $argumentKey){
                $this->assertEquals($expectedOutputArray['Query']['fields'][$fieldKey]['arguments'][$argumentKey]['type'],$queryFieldArguments[$argumentKey]->getType());
                $this->assertEquals($expectedOutputArray['Query']['fields'][$fieldKey]['arguments'][$argumentKey]['name'],$queryFieldArguments[$argumentKey]->getName());
                $this->assertEquals($expectedOutputArray['Query']['fields'][$fieldKey]['arguments'][$argumentKey]['description'],$queryFieldArguments[$argumentKey]->getDescription());
            }
        }
    }

    public function testGraphQlEnumTypeConfigStructure()
    {
        $queryEnum = 'PriceAdjustmentDescriptionEnum';
        /** @var \Magento\Framework\GraphQl\Config\Element\Enum $outputEnum */
        $outputEnum = $this->model->getConfigElement($queryEnum);
        /** @var \Magento\Framework\GraphQl\Config\Element\EnumValue $outputEnumValues */
        $outputEnumValues = $outputEnum->getValues();
        $expectedOutputArray = require __DIR__ . '/_files/query_array_output.php';
        $this->assertEquals($outputEnum->getName(), $queryEnum);
        foreach(array_keys($outputEnumValues) as $outputEnumValue) {
            $this->assertEquals($expectedOutputArray['PriceAdjustmentDescriptionEnum']['items'][$outputEnumValue]['name'], $outputEnumValues[$outputEnumValue]->getName());
            $this->assertEquals($expectedOutputArray['PriceAdjustmentDescriptionEnum']['items'][$outputEnumValue]['_value'], $outputEnumValues[$outputEnumValue]->getValue());
        }
    }

    public function testGraphQlInterfaceStructure()
    {
        $inputInterfaceType = 'ProductLinks';
        /** @var \Magento\Framework\GraphQl\Config\Element\Type $outputInterface */
        $outputInterface = $this->model->getConfigElement($inputInterfaceType);
        $expectedOutputArray = require __DIR__ . '/_files/query_array_output.php';
        $this->assertEquals($outputInterface->getName(), $inputInterfaceType);

        $outputInterfaceValues = $outputInterface->getInterfaces();
        /** @var Field $outputInterfaceFields */
        $outputInterfaceFields =$outputInterface->getFields();
        foreach(array_keys($outputInterfaceValues) as $outputInterfaceValue){
            $this->assertEquals($expectedOutputArray['ProductLinks']['implements'][$outputInterfaceValue]['interface'], $outputInterfaceValues[$outputInterfaceValue]['interface']);
            $this->assertEquals($expectedOutputArray['ProductLinks']['implements'][$outputInterfaceValue]['copyFields'], $outputInterfaceValues[$outputInterfaceValue]['copyFields']);
        }
        foreach(array_keys($outputInterfaceFields) as $outputInterfaceField) {
            $this->assertEquals($expectedOutputArray['ProductLinks']['fields'][$outputInterfaceField]['name'], $outputInterfaceFields[$outputInterfaceField]->getName());
            $this->assertEquals($expectedOutputArray['ProductLinks']['fields'][$outputInterfaceField]['type'], $outputInterfaceFields[$outputInterfaceField]->getType());
            $this->assertEquals($expectedOutputArray['ProductLinks']['fields'][$outputInterfaceField]['required'], $outputInterfaceFields[$outputInterfaceField]->isRequired());
            $this->assertEquals($expectedOutputArray['ProductLinks']['fields'][$outputInterfaceField]['description'], $outputInterfaceFields[$outputInterfaceField]->getDescription());
            $this->assertEmpty($outputInterfaceFields[$outputInterfaceField]->getArguments());
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
