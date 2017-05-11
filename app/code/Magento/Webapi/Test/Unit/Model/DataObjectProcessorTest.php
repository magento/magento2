<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Webapi\Test\Unit\Model;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Webapi\Model\Config as ModelConfig;

class DataObjectProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * @var ModelConfig
     */
    protected $config;

    protected function setup()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $methodsMapProcessor = $objectManager->getObject(
            \Magento\Framework\Reflection\MethodsMap::class,
            [
                'fieldNamer' => $objectManager->getObject(\Magento\Framework\Reflection\FieldNamer::class),
                'typeProcessor' => $objectManager->getObject(\Magento\Framework\Reflection\TypeProcessor::class),
            ]
        );
        $serializerMock = $this->getMock(SerializerInterface::class);
        $serializerMock->method('serialize')
            ->willReturn('serializedData');
        $serializerMock->method('unserialize')
            ->willReturn(['unserializedData']);

        $objectManager->setBackwardCompatibleProperty(
            $methodsMapProcessor,
            'serializer',
            $serializerMock
        );
        $this->dataObjectProcessor = $objectManager->getObject(
            \Magento\Framework\Reflection\DataObjectProcessor::class,
            [
                'methodsMapProcessor' => $methodsMapProcessor,
                'typeCaster' => $objectManager->getObject(\Magento\Framework\Reflection\TypeCaster::class),
                'fieldNamer' => $objectManager->getObject(\Magento\Framework\Reflection\FieldNamer::class),
            ]
        );
        parent::setUp();
    }

    public function testDataObjectProcessor()
    {
        $objectManager =  new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        /** @var \Magento\Webapi\Test\Unit\Model\Files\TestDataObject $testDataObject */
        $testDataObject = $objectManager->getObject(\Magento\Webapi\Test\Unit\Model\Files\TestDataObject::class);

        $expectedOutputDataArray = [
            'id' => '1',
            'address' => 'someAddress',
            'default_shipping' => 'true',
            'required_billing' => 'false',
        ];

        $testDataObjectType = \Magento\Webapi\Test\Unit\Model\Files\TestDataInterface::class;
        $outputData = $this->dataObjectProcessor->buildOutputDataArray($testDataObject, $testDataObjectType);
        $this->assertEquals($expectedOutputDataArray, $outputData);
    }
}
