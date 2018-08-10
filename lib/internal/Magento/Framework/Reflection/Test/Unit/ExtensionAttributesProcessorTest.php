<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Reflection\Test\Unit;

use Magento\Framework\Api\ExtensionAttribute\Config\Converter;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Reflection\ExtensionAttributesProcessor;
use Magento\Framework\Reflection\FieldNamer;
use Magento\Framework\Reflection\MethodsMap;
use Magento\Framework\Reflection\TypeCaster;

/**
 * ExtensionAttributesProcessor test
 */
class ExtensionAttributesProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ExtensionAttributesProcessor
     */
    private $model;

    /**
     * @var DataObjectProcessor
     */
    private $dataObjectProcessorMock;

    /**
     * @var MethodsMap
     */
    private $methodsMapProcessorMock;

    /**
     * @var FieldNamer
     */
    private $fieldNamerMock;

    /**
     * @var TypeCaster
     */
    private $typeCasterMock;

    /**
     * @var \Magento\Framework\Api\ExtensionAttribute\Config
     */
    private $configMock;

    /**
     * @var AuthorizationInterface
     */
    private $authorizationMock;

    /**
     * Set up helper.
     */
    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->dataObjectProcessorMock = $this->getMockBuilder('Magento\Framework\Reflection\DataObjectProcessor')
            ->disableOriginalConstructor()
            ->getMock();
        $this->methodsMapProcessorMock = $this->getMockBuilder('Magento\Framework\Reflection\MethodsMap')
            ->disableOriginalConstructor()
            ->getMock();
        $this->typeCasterMock = $this->getMockBuilder('Magento\Framework\Reflection\TypeCaster')
            ->disableOriginalConstructor()
            ->getMock();
        $this->fieldNamerMock = $this->getMockBuilder('Magento\Framework\Reflection\FieldNamer')
            ->disableOriginalConstructor()
            ->getMock();
        $this->configMock = $this->getMockBuilder('Magento\Framework\Api\ExtensionAttribute\Config')
            ->disableOriginalConstructor()
            ->getMock();
        $this->authorizationMock = $this->getMockBuilder('Magento\Framework\AuthorizationInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $objectManager->getObject(
            'Magento\Framework\Reflection\ExtensionAttributesProcessor',
            [
                'dataObjectProcessor' => $this->dataObjectProcessorMock,
                'methodsMapProcessor' => $this->methodsMapProcessorMock,
                'typeCaster' => $this->typeCasterMock,
                'fieldNamer' => $this->fieldNamerMock,
                'authorization' => $this->authorizationMock,
                'config' => $this->configMock,
                'isPermissionChecked' => true,
            ]
        );
    }

    /**
     * @param bool $isPermissionAllowed
     * @param array $expectedValue
     * @dataProvider buildOutputDataArrayWithPermissionProvider
     */
    public function testBuildOutputDataArrayWithPermission($isPermissionAllowed, $expectedValue)
    {
        $dataObject = new \Magento\Framework\Reflection\Test\Unit\ExtensionAttributesObject();
        $dataObjectType = 'Magento\Framework\Reflection\Test\Unit\ExtensionAttributesObject';
        $methodName = 'getAttrName';
        $attributeName = 'attr_name';
        $attributeValue = 'attrName';

        $this->methodsMapProcessorMock->expects($this->once())
            ->method('getMethodsMap')
            ->with($dataObjectType)
            ->will($this->returnValue([$methodName => []]));
        $this->methodsMapProcessorMock->expects($this->once())
            ->method('isMethodValidForDataField')
            ->with($dataObjectType, $methodName)
            ->will($this->returnValue(true));
        $this->fieldNamerMock->expects($this->once())
            ->method('getFieldNameForMethodName')
            ->with($methodName)
            ->will($this->returnValue($attributeName));
        $permissionName = 'Magento_Permission';
        $this->configMock->expects($this->once())
            ->method('get')
            ->will($this->returnValue([
                $dataObjectType => [
                    $attributeName => [ Converter::RESOURCE_PERMISSIONS => [ $permissionName ] ]
                ]
              ]));
        $this->authorizationMock->expects($this->once())
            ->method('isAllowed')
            ->with($permissionName)
            ->will($this->returnValue($isPermissionAllowed));

        if ($isPermissionAllowed) {
            $this->methodsMapProcessorMock->expects($this->once())
                ->method('getMethodReturnType')
                ->with($dataObjectType, $methodName)
                ->will($this->returnValue('string'));
            $this->typeCasterMock->expects($this->once())
                ->method('castValueToType')
                ->with($attributeValue, 'string')
                ->will($this->returnValue($attributeValue));
        }

        $value = $this->model->buildOutputDataArray(
            $dataObject,
            $dataObjectType
        );

        $this->assertEquals(
            $value,
            $expectedValue
        );
    }

    /**
     * @return array
     */
    public function buildOutputDataArrayWithPermissionProvider()
    {
        return [
            'permission allowed' => [
                true,
                [
                    'attr_name' => 'attrName',
                ],
            ],
            'permission not allowed' => [
                false,
                [],
            ],
        ];
    }
}
