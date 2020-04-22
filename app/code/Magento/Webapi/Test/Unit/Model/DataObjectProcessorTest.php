<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Webapi\Test\Unit\Model;

use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Reflection\FieldNamer;
use Magento\Framework\Reflection\MethodsMap;
use Magento\Framework\Reflection\Test\Unit\TestDataInterface;
use Magento\Framework\Reflection\Test\Unit\TestDataObject;
use Magento\Framework\Reflection\TypeCaster;
use Magento\Framework\Reflection\TypeProcessor;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Webapi\Model\Config as ModelConfig;
use PHPUnit\Framework\TestCase;

class DataObjectProcessorTest extends TestCase
{
    /**
     * @var DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * @var ModelConfig
     */
    protected $config;

    protected function setup(): void
    {
        $objectManager = new ObjectManager($this);
        $methodsMapProcessor = $objectManager->getObject(
            MethodsMap::class,
            [
                'fieldNamer' => $objectManager->getObject(FieldNamer::class),
                'typeProcessor' => $objectManager->getObject(TypeProcessor::class),
            ]
        );
        $serializerMock = $this->getMockForAbstractClass(SerializerInterface::class);
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
            DataObjectProcessor::class,
            [
                'methodsMapProcessor' => $methodsMapProcessor,
                'typeCaster' => $objectManager->getObject(TypeCaster::class),
                'fieldNamer' => $objectManager->getObject(FieldNamer::class),
            ]
        );
        parent::setUp();
    }

    public function testDataObjectProcessor()
    {
        $objectManager =  new ObjectManager($this);
        /** @var TestDataObject $testDataObject */
        $testDataObject = $objectManager->getObject(TestDataObject::class);

        $expectedOutputDataArray = [
            'id' => '1',
            'address' => 'someAddress',
            'default_shipping' => 'true',
            'required_billing' => 'false',
        ];

        $testDataObjectType = TestDataInterface::class;
        $outputData = $this->dataObjectProcessor->buildOutputDataArray($testDataObject, $testDataObjectType);
        $this->assertEquals($expectedOutputDataArray, $outputData);
    }
}
