<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api\Test\Unit\Code\Generator;

use Magento\Framework\Api\ExtensionAttribute\Config\Converter;

class ExtensionAttributesInterfaceGeneratorTest extends \PHPUnit_Framework_TestCase
{
    public function testGenerate()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $configMock = $this->getMockBuilder(\Magento\Framework\Api\ExtensionAttribute\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $configMock->expects($this->any())
            ->method('get')
            ->willReturn(
                [
                    \Magento\Catalog\Api\Data\ProductInterface::class => [
                        'string_attribute' => [
                            Converter::DATA_TYPE => 'string',
                            Converter::RESOURCE_PERMISSIONS => [],
                        ],
                        'complex_object_attribute' => [
                            Converter::DATA_TYPE => '\Magento\Bundle\Api\Data\OptionInterface[]',
                            Converter::RESOURCE_PERMISSIONS => [],
                        ],
                        // Ensure type declaration is added to argument of setter
                        'complex_object_attribute_with_type_declaration' => [
                            Converter::DATA_TYPE => \Magento\Bundle\Api\Data\BundleOptionInterface::class,
                            Converter::RESOURCE_PERMISSIONS => [],
                        ],
                    ],
                    \Magento\Catalog\Api\Data\Product::class => [
                        'should_not_include' => [
                            Converter::DATA_TYPE => 'string',
                            Converter::RESOURCE_PERMISSIONS => [],
                        ],
                    ],
                ]
            );
        $typeProcessorMock = $this->getMockBuilder(\Magento\Framework\Reflection\TypeProcessor::class)
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        /** @var \Magento\Framework\Api\Code\Generator\ExtensionAttributesInterfaceGenerator $model */
        $model = $objectManager->getObject(
            \Magento\Framework\Api\Code\Generator\ExtensionAttributesInterfaceGenerator::class,
            [
                'config' => $configMock,
                'typeProcessor' => $typeProcessorMock,
                'sourceClassName' => \Magento\Catalog\Api\Data\Product::class,
                'resultClassName' => \Magento\Catalog\Api\Data\ProductExtensionInterface::class,
                'classGenerator' => null
            ]
        );
        $expectedResult = file_get_contents(__DIR__ . '/_files/SampleExtensionInterface.txt');
        $reflectionObject = new \ReflectionObject($model);
        $reflectionMethod = $reflectionObject->getMethod('_generateCode');
        $reflectionMethod->setAccessible(true);
        $generatedCode = $reflectionMethod->invoke($model);
        $this->assertEquals($expectedResult, $generatedCode);
    }

    public function testValidateException()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        /** @var \Magento\Framework\Api\Code\Generator\ExtensionAttributesInterfaceGenerator $model */
        $model = $objectManager->getObject(
            \Magento\Framework\Api\Code\Generator\ExtensionAttributesInterfaceGenerator::class,
            [
                'sourceClassName' => \Magento\Catalog\Api\Data\Product::class,
                'resultClassName' => \Magento\Catalog\Api\Data\ProductInterface::class
            ]
        );
        $reflectionObject = new \ReflectionObject($model);
        $reflectionMethod = $reflectionObject->getMethod('_validateData');
        $reflectionMethod->setAccessible(true);

        $expectedValidationResult = false;
        $this->assertEquals($expectedValidationResult, $reflectionMethod->invoke($model));
        $this->assertTrue(
            in_array(
                'Invalid extension interface name [\Magento\Catalog\Api\Data\ProductInterface].'
                . ' Use \Magento\Catalog\Api\Data\ProductExtensionInterface',
                $model->getErrors()
            ),
            'Expected validation error message is missing.'
        );
    }
}
