<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
// @codingStandardsIgnoreFile
namespace Magento\Framework\Api\Test\Unit\Code\Generator;

use Magento\Framework\Api\ExtensionAttribute\Config\Converter;

class ExtensionAttributesGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Api\ExtensionAttribute\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    /**
     * @var \Magento\Framework\Reflection\TypeProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $typeProcessorMock;

    /**
     * @var \Magento\Framework\Api\Code\Generator\ExtensionAttributesGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $model;

    protected function setUp()
    {
        $this->configMock = $this->getMockBuilder('Magento\Framework\Api\ExtensionAttribute\Config')
            ->disableOriginalConstructor()
            ->getMock();

        $this->typeProcessorMock = $this->getMockBuilder('Magento\Framework\Reflection\TypeProcessor')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            'Magento\Framework\Api\Code\Generator\ExtensionAttributesGenerator',
            [
                'config' => $this->configMock,
                'typeProcessor' => $this->typeProcessorMock,
                'sourceClassName' => '\Magento\Catalog\Api\Data\Product',
                'resultClassName' => '\Magento\Catalog\Api\Data\ProductExtension',
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
                    'Magento\Catalog\Api\Data\ProductInterface' => [
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
                            Converter::DATA_TYPE => '\Magento\Bundle\Api\Data\BundleOptionInterface',
                            Converter::RESOURCE_PERMISSIONS => [],
                        ],
                    ],
                    'Magento\Catalog\Api\Data\Product' => [
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
            ->willReturn(['Magento\Catalog\Api\Data\Product' => ['should_not_include' => 'string']]);
        $expectedResult = file_get_contents(__DIR__ . '/_files/SampleEmptyExtension.txt');
        $this->validateGeneratedCode($expectedResult);
    }

    public function testValidateException()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        /** @var \Magento\Framework\Api\Code\Generator\ExtensionAttributesGenerator $model */
        $model = $objectManager->getObject(
            'Magento\Framework\Api\Code\Generator\ExtensionAttributesGenerator',
            [
                'sourceClassName' => '\Magento\Catalog\Api\Data\Product',
                'resultClassName' => '\Magento\Catalog\Api\Data\ProductInterface'
            ]
        );
        $reflectionObject = new \ReflectionObject($model);
        $reflectionMethod = $reflectionObject->getMethod('_validateData');
        $reflectionMethod->setAccessible(true);

        $expectedValidationResult = false;
        $this->assertEquals($expectedValidationResult, $reflectionMethod->invoke($model));
        $this->assertTrue(
            in_array(
                'Invalid extension name [\Magento\Catalog\Api\Data\ProductInterface].'
                . ' Use \Magento\Catalog\Api\Data\ProductExtension',
                $model->getErrors()
            ),
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
