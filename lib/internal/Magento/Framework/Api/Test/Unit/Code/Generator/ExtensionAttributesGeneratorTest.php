<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Api\Test\Unit\Code\Generator;

use Magento\Bundle\Api\Data\BundleOptionInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Api\Code\Generator\ExtensionAttributesGenerator;
use Magento\Framework\Api\ExtensionAttribute\Config;
use Magento\Framework\Api\ExtensionAttribute\Config\Converter;
use Magento\Framework\Reflection\TypeProcessor;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ExtensionAttributesGeneratorTest extends TestCase
{
    /**
     * @var Config|MockObject
     */
    protected $configMock;

    /**
     * @var TypeProcessor|MockObject
     */
    protected $typeProcessorMock;

    /**
     * @var ExtensionAttributesGenerator|MockObject
     */
    protected $model;

    protected function setUp(): void
    {
        $this->configMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->typeProcessorMock = $this->getMockBuilder(TypeProcessor::class)
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            ExtensionAttributesGenerator::class,
            [
                'config' => $this->configMock,
                'typeProcessor' => $this->typeProcessorMock,
                'sourceClassName' => \Magento\Catalog\Api\Data\Product::class,
                'resultClassName' => \Magento\Catalog\Api\Data\ProductExtension::class,
                'classGenerator' => null
            ]
        );
        parent::setUp();
    }

    public function testGenerate()
    {
        $this->configMock->expects($this->any())
            ->method('get')
            ->willReturn(
                [
                    ProductInterface::class => [
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
                            Converter::DATA_TYPE => BundleOptionInterface::class,
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
        $expectedResult = file_get_contents(__DIR__ . '/_files/SampleExtension.txt');
        $this->validateGeneratedCode($expectedResult);
    }

    public function testGenerateEmptyExtension()
    {
        $this->configMock->expects($this->any())
            ->method('get')
            ->willReturn([\Magento\Catalog\Api\Data\Product::class => ['should_not_include' => 'string']]);
        $expectedResult = file_get_contents(__DIR__ . '/_files/SampleEmptyExtension.txt');
        $this->validateGeneratedCode($expectedResult);
    }

    public function testValidateException()
    {
        $objectManager = new ObjectManager($this);
        /** @var ExtensionAttributesGenerator $model */
        $model = $objectManager->getObject(
            ExtensionAttributesGenerator::class,
            [
                'sourceClassName' => \Magento\Catalog\Api\Data\Product::class,
                'resultClassName' => ProductInterface::class
            ]
        );
        $reflectionObject = new \ReflectionObject($model);
        $reflectionMethod = $reflectionObject->getMethod('_validateData');
        $reflectionMethod->setAccessible(true);

        $expectedValidationResult = false;
        $this->assertEquals($expectedValidationResult, $reflectionMethod->invoke($model));
        $this->assertContains(
            'Invalid extension name [\Magento\Catalog\Api\Data\ProductInterface].'
            . ' Use \Magento\Catalog\Api\Data\ProductExtension',
            $model->getErrors(),
            'Expected validation error message is missing.'
        );
    }

    /**
     * Check if generated code matches provided expected result.
     *
     * @param string $expectedResult
     * @return void
     */
    protected function validateGeneratedCode($expectedResult)
    {
        $reflectionObject = new \ReflectionObject($this->model);
        $reflectionMethod = $reflectionObject->getMethod('_generateCode');
        $reflectionMethod->setAccessible(true);
        $generatedCode = $reflectionMethod->invoke($this->model);
        $expectedResult = preg_replace('/\s+/', ' ', $expectedResult);
        $generatedCode = preg_replace('/\s+/', ' ', $generatedCode);
        $this->assertEquals($expectedResult, $generatedCode);
    }
}
