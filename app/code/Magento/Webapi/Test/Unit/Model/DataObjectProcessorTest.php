<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Webapi\Test\Unit\Model;

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
            'Magento\Framework\Reflection\MethodsMap',
            [
                'fieldNamer' => $objectManager->getObject('Magento\Framework\Reflection\FieldNamer'),
                'typeProcessor' => $objectManager->getObject('Magento\Framework\Reflection\TypeProcessor'),
            ]
        );
        $this->dataObjectProcessor = $objectManager->getObject(
            'Magento\Framework\Reflection\DataObjectProcessor',
            [
                'methodsMapProcessor' => $methodsMapProcessor,
                'typeCaster' => $objectManager->getObject('Magento\Framework\Reflection\TypeCaster'),
                'fieldNamer' => $objectManager->getObject('Magento\Framework\Reflection\FieldNamer'),
            ]
        );
        parent::setUp();
    }

    public function testDataObjectProcessor()
    {
        $objectManager =  new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        /** @var \Magento\Webapi\Test\Unit\Model\Files\TestDataObject $testDataObject */
        $testDataObject = $objectManager->getObject('Magento\Webapi\Test\Unit\Model\Files\TestDataObject');

        $expectedOutputDataArray = [
            'id' => '1',
            'address' => 'someAddress',
            'default_shipping' => 'true',
            'required_billing' => 'false',
        ];

        $testDataObjectType = 'Magento\Webapi\Test\Unit\Model\Files\TestDataInterface';
        $outputData = $this->dataObjectProcessor->buildOutputDataArray($testDataObject, $testDataObjectType);
        $this->assertEquals($expectedOutputDataArray, $outputData);
    }
}
