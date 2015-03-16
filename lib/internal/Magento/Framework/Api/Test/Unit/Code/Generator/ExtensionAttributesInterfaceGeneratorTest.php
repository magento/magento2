<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
// @codingStandardsIgnoreFile
namespace Magento\Framework\Api\Test\Unit\Code\Generator;

class ExtensionAttributesInterfaceGeneratorTest extends \PHPUnit_Framework_TestCase
{
    public function testGenerate()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $configReaderMock = $this->getMockBuilder('Magento\Framework\Api\Config\Reader')
            ->disableOriginalConstructor()
            ->getMock();
        $configReaderMock->expects($this->any())
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

        /** @var \Magento\Framework\Api\Code\Generator\ExtensionAttributesInterfaceGenerator $model */
        $model = $objectManager->getObject(
            'Magento\Framework\Api\Code\Generator\ExtensionAttributesInterfaceGenerator',
            [
                'configReader' => $configReaderMock,
                'sourceClassName' => '\Magento\Catalog\Api\Data\Product',
                'resultClassName' => '\Magento\Catalog\Api\Data\ProductExtensionInterface',
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
            'Magento\Framework\Api\Code\Generator\ExtensionAttributesInterfaceGenerator',
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
                'Invalid extension interface name [\Magento\Catalog\Api\Data\ProductInterface].'
                . ' Use \Magento\Catalog\Api\Data\ProductExtensionInterface',
                $model->getErrors()
            ),
            'Expected validation error message is missing.'
        );
    }
}
