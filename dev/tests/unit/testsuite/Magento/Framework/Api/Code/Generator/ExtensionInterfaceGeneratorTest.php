<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api\Code\Generator;

class ExtensionInterfaceGeneratorTest extends \PHPUnit_Framework_TestCase
{
    public function testGenerate()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
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

        /** @var \Magento\Framework\Api\Code\Generator\ObjectExtensionInterface $model */
        $model = $objectManager->getObject(
            'Magento\Framework\Api\Code\Generator\ObjectExtensionInterface',
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
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        /** @var \Magento\Framework\Api\Code\Generator\ObjectExtensionInterface $model */
        $model = $objectManager->getObject(
            'Magento\Framework\Api\Code\Generator\ObjectExtensionInterface',
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
