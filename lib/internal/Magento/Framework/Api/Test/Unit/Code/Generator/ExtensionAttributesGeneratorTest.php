<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
// @codingStandardsIgnoreFile
namespace Magento\Framework\Api\Test\Unit\Code\Generator;

class ExtensionAttributesGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Api\Config\Reader|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configReaderMock;

    /**
     * @var \Magento\Framework\Api\Code\Generator\ExtensionAttributesGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $model;

    protected function setUp()
    {
        $this->configReaderMock = $this->getMockBuilder('Magento\Framework\Api\Config\Reader')
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            'Magento\Framework\Api\Code\Generator\ExtensionAttributesGenerator',
            [
                'configReader' => $this->configReaderMock,
                'sourceClassName' => '\Magento\Catalog\Api\Data\Product',
                'resultClassName' => '\Magento\Catalog\Api\Data\ProductExtension',
                'classGenerator' => null
            ]
        );
        parent::setUp();
    }

    public function testGenerate()
    {
        $this->configReaderMock->expects($this->any())
            ->method('read')
            ->willReturn(
                [
                    'Magento\Catalog\Api\Data\ProductInterface' => [
                        'string_attribute' => 'string',
                        'complex_object_attribute' => '\Magento\Bundle\Api\Data\OptionInterface[]'
                    ],
                    'Magento\Catalog\Api\Data\Product' => [
                        'should_not_include' => 'string',
                    ],
                ]
            );
        $expectedResult = file_get_contents(__DIR__ . '/_files/SampleExtension.txt');
        $this->validateGeneratedCode($expectedResult);
    }

    public function testGenerateEmptyExtension()
    {
        $this->configReaderMock->expects($this->any())
            ->method('read')
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
        $this->assertEquals($expectedResult, $generatedCode);
    }
}
