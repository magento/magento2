<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Eav\Test\Unit\Model\Validator\Attribute;

/**
 * Test for \Magento\Eav\Model\Validator\Attribute\Data
 */
class DataTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Eav\Model\AttributeDataFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $attrDataFactory;

    /**
     * @var \Magento\Eav\Model\Validator\Attribute\Data
     */
    private $model;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->attrDataFactory = $this->getMockBuilder(\Magento\Eav\Model\AttributeDataFactory::class)
            ->setMethods(['create'])
            ->setConstructorArgs(
                [
                    'objectManager' => $this->createMock(\Magento\Framework\ObjectManagerInterface::class),
                    'string' => $this->createMock(\Magento\Framework\Stdlib\StringUtils::class)
                ]
            )
            ->getMock();

        $this->model = $this->objectManager->getObject(
            \Magento\Eav\Model\Validator\Attribute\Data::class,
            [
                '_attrDataFactory' => $this->attrDataFactory
            ]
        );
    }

    /**
     * Testing  \Magento\Eav\Model\Validator\Attribute\Data::isValid
     *
     * @dataProvider isValidDataProvider
     *
     * @param array $attributeData
     * @param array|bool $result
     * @param bool $expected
     * @param array $messages
     * @param array $data
     */
    public function testIsValid(
        $attributeData,
        $result,
        $expected,
        $messages,
        $data = ['attribute' => 'new_test']
    ) {
        $entity = $this->_getEntityMock();
        $attribute = $this->_getAttributeMock($attributeData);
        $attrDataFactory = $this->getMockBuilder(\Magento\Eav\Model\AttributeDataFactory::class)
            ->setMethods(['create'])
            ->setConstructorArgs(
                [
                    'objectManager' => $this->createMock(\Magento\Framework\ObjectManagerInterface::class),
                    'string' => $this->createMock(\Magento\Framework\Stdlib\StringUtils::class)
                ]
            )
            ->getMock();

        $validator = new \Magento\Eav\Model\Validator\Attribute\Data($attrDataFactory);
        $validator->setAttributes([$attribute])->setData($data);
        if ($attribute->getDataModel() || $attribute->getFrontendInput()) {
            $dataModel = $this->_getDataModelMock($result);
            $attrDataFactory->expects($this->any())
                ->method('create')
                ->with($attribute, $entity)
                ->willReturn($dataModel);
        }
        $this->assertEquals($expected, $validator->isValid($entity));
        $this->assertEquals($messages, $validator->getMessages());
    }

    /**
     * Data provider for testIsValid
     *
     * @return array
     */
    public function isValidDataProvider()
    {
        return [
            'is_valid' => [
                'attributeData' => [
                    'attribute_code' => 'attribute',
                    'data_model' => $this->_getDataModelMock(null),
                    'frontend_input' => 'text',
                    'is_visible' => true,
                ],
                'attributeReturns' => true,
                'isValid' => true,
                'messages' => [],
            ],
            'is_invalid' => [
                'attributeData' => [
                    'attribute_code' => 'attribute',
                    'data_model' => $this->_getDataModelMock(null),
                    'frontend_input' => 'text',
                    'is_visible' => true,
                ],
                'attributeReturns' => ['Error'],
                'isValid' => false,
                'messages' => ['attribute' => ['Error']],
            ],
            'no_data_models' => [
                'attributeData' => [
                    'attribute_code' => 'attribute',
                    'frontend_input' => 'text',
                    'is_visible' => true,
                ],
                'attributeReturns' => ['Error'],
                'isValid' => false,
                'messages' => ['attribute' => ['Error']],
            ],
            'no_data_models_no_frontend_input' => [
                'attributeData' => [
                    'attribute_code' => 'attribute',
                    'is_visible' => true,
                ],
                'attributeReturns' => ['Error'],
                'isValid' => true,
                'messages' => [],
            ],
            'no_data_for attribute' => [
                'attributeData' => [
                    'attribute_code' => 'attribute',
                    'data_model' => $this->_getDataModelMock(null),
                    'frontend_input' => 'text',
                    'is_visible' => true,
                ],
                'attributeReturns' => true,
                'isValid' => true,
                'messages' => [],
                'setData' => ['attribute2' => 'new_test'],
            ],
            'is_valid_data_from_entity' => [
                'attributeData' => [
                    'attribute_code' => 'attribute',
                    'data_model' => $this->_getDataModelMock(null),
                    'frontend_input' => 'text',
                    'is_visible' => true,
                ],
                'attributeReturns' => true,
                'isValid' => true,
                'messages' => [],
                'setData' => [],
            ],
            'is_invisible' => [
                'attributeData' => [
                    'attribute_code' => 'attribute',
                    'data_model' => $this->_getDataModelMock(null),
                    'frontend_input' => 'text',
                    'is_visible' => false,
                ],
                'attributeReturns' => ['Error'],
                'isValid' => true,
                'messages' => [],
            ],
        ];
    }

    /**
     * Testing \Magento\Eav\Model\Validator\Attribute\Data::isValid
     *
     * In this test entity attributes are got from attribute collection.
     */
    public function testIsValidAttributesFromCollection()
    {
        /** @var \Magento\Eav\Model\Entity\AbstractEntity $resource */
        $resource = $this->getMockForAbstractClass(\Magento\Eav\Model\Entity\AbstractEntity::class, [], '', false);
        $attribute = $this->_getAttributeMock(
            [
                'attribute_code' => 'attribute',
                'data_model' => $this->_getDataModelMock(null),
                'frontend_input' => 'text',
                'is_visible' => true,
            ]
        );
        $collection = $this->getMockBuilder(\Magento\Framework\DataObject::class)->setMethods(['getItems'])->getMock();
        $collection->expects($this->once())->method('getItems')->willReturn([$attribute]);
        $entityType = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->setMethods(['getAttributeCollection'])
            ->getMock();
        $entityType->expects($this->once())->method('getAttributeCollection')->willReturn($collection);
        $entity = $this->_getEntityMock();
        $entity->expects($this->once())->method('getResource')->willReturn($resource);
        $entity->expects($this->once())->method('getEntityType')->willReturn($entityType);
        $dataModel = $this->_getDataModelMock(true);
        $attrDataFactory = $this->getMockBuilder(\Magento\Eav\Model\AttributeDataFactory::class)
            ->setMethods(['create'])
            ->setConstructorArgs(
                [
                    'objectManager' => $this->createMock(\Magento\Framework\ObjectManagerInterface::class),
                    'string' => $this->createMock(\Magento\Framework\Stdlib\StringUtils::class)
                ]
            )
            ->getMock();
        $attrDataFactory->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            $attribute,
            $entity
        )->willReturn(
            $dataModel
        );
        $validator = new \Magento\Eav\Model\Validator\Attribute\Data($attrDataFactory);

        $validator->setData(['attribute' => 'new_test_data']);
        $this->assertTrue($validator->isValid($entity));
    }

    /**
     * @dataProvider whiteBlackListProvider
     * @param callable $callback
     */
    public function testIsValidBlackListWhiteListChecks($callback)
    {
        $attribute = $this->_getAttributeMock(
            [
                'attribute_code' => 'attribute',
                'data_model' => $this->_getDataModelMock(null),
                'frontend_input' => 'text',
                'is_visible' => true,
            ]
        );
        $secondAttribute = $this->_getAttributeMock(
            [
                'attribute_code' => 'attribute2',
                'data_model' => $this->_getDataModelMock(null),
                'frontend_input' => 'text',
                'is_visible' => true,
            ]
        );
        $data = ['attribute' => 'new_test_data', 'attribute2' => 'some data'];
        $entity = $this->_getEntityMock();
        $dataModel = $this->_getDataModelMock(true, $data['attribute']);
        $attrDataFactory = $this->getMockBuilder(\Magento\Eav\Model\AttributeDataFactory::class)
            ->setMethods(['create'])
            ->setConstructorArgs(
                [
                    'objectManager' => $this->createMock(\Magento\Framework\ObjectManagerInterface::class),
                    'string' => $this->createMock(\Magento\Framework\Stdlib\StringUtils::class)
                ]
            )
            ->getMock();

        $attrDataFactory->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            $attribute,
            $entity
        )->willReturn(
            $dataModel
        );
        $validator = new \Magento\Eav\Model\Validator\Attribute\Data($attrDataFactory);
        $validator->setAttributes([$attribute, $secondAttribute])->setData($data);
        $callback($validator);
        $this->assertTrue($validator->isValid($entity));
    }

    /**
     * @return array
     */
    public function whiteBlackListProvider()
    {
        $whiteCallback = function ($validator) {
            $validator->setAttributesWhiteList(['attribute']);
        };

        $blackCallback = function ($validator) {
            $validator->setAttributesBlackList(['attribute2']);
        };
        return ['white_list' => [$whiteCallback], 'black_list' => [$blackCallback]];
    }

    public function testSetAttributesWhiteList()
    {
        $this->markTestSkipped('Skipped in #27500 due to testing protected/private methods and properties');

        $attributes = ['attr1', 'attr2', 'attr3'];
        $attrDataFactory = $this->getMockBuilder(\Magento\Eav\Model\AttributeDataFactory::class)
            ->setConstructorArgs(
                [
                    'objectManager' => $this->createMock(\Magento\Framework\ObjectManagerInterface::class),
                    'string' => $this->createMock(\Magento\Framework\Stdlib\StringUtils::class)
                ]
            )
            ->getMock();
        $validator = new \Magento\Eav\Model\Validator\Attribute\Data($attrDataFactory);
        $result = $validator->setAttributesWhiteList($attributes);
        //$this->assertAttributeEquals($attributes, '_attributesWhiteList', $validator);
        $this->assertEquals($validator, $result);
    }

    public function testSetAttributesBlackList()
    {
        $this->markTestSkipped('Skipped in #27500 due to testing protected/private methods and properties');

        $attributes = ['attr1', 'attr2', 'attr3'];
        $attrDataFactory = $this->getMockBuilder(\Magento\Eav\Model\AttributeDataFactory::class)
            ->setConstructorArgs(
                [
                    'objectManager' => $this->createMock(\Magento\Framework\ObjectManagerInterface::class),
                    'string' => $this->createMock(\Magento\Framework\Stdlib\StringUtils::class)
                ]
            )
            ->getMock();
        $validator = new \Magento\Eav\Model\Validator\Attribute\Data($attrDataFactory);
        $result = $validator->setAttributesBlackList($attributes);
        //$this->assertAttributeEquals($attributes, '_attributesBlackList', $validator);
        $this->assertEquals($validator, $result);
    }

    public function testAddErrorMessages()
    {
        $data = ['attribute1' => 'new_test', 'attribute2' => 'some data'];
        $entity = $this->_getEntityMock();
        $firstAttribute = $this->_getAttributeMock(
            [
                'attribute_code' => 'attribute1',
                'data_model' => $firstDataModel = $this->_getDataModelMock(['Error1']),
                'frontend_input' => 'text',
                'is_visible' => true,
            ]
        );
        $secondAttribute = $this->_getAttributeMock(
            [
                'attribute_code' => 'attribute2',
                'data_model' => $secondDataModel = $this->_getDataModelMock(['Error2']),
                'frontend_input' => 'text',
                'is_visible' => true,
            ]
        );
        $expectedMessages = ['attribute1' => ['Error1'], 'attribute2' => ['Error2']];
        $expectedDouble = ['attribute1' => ['Error1', 'Error1'], 'attribute2' => ['Error2', 'Error2']];
        $factory = $this->getMockBuilder(\Magento\Eav\Model\AttributeDataFactory::class)
            ->setMethods(['create'])
            ->setConstructorArgs(
                [
                    'objectManager' => $this->createMock(\Magento\Framework\ObjectManagerInterface::class),
                    'string' => $this->createMock(\Magento\Framework\Stdlib\StringUtils::class)
                ]
            )
            ->getMock();
        $validator = new \Magento\Eav\Model\Validator\Attribute\Data($factory);
        $validator->setAttributes([$firstAttribute, $secondAttribute])->setData($data);

        $factory->expects(
            $this->at(0)
        )->method(
            'create'
        )->with(
            $firstAttribute,
            $entity
        )->willReturn(
            $firstDataModel
        );
        $factory->expects(
            $this->at(1)
        )->method(
            'create'
        )->with(
            $secondAttribute,
            $entity
        )->willReturn(
            $secondDataModel
        );
        $factory->expects(
            $this->at(2)
        )->method(
            'create'
        )->with(
            $firstAttribute,
            $entity
        )->willReturn(
            $firstDataModel
        );
        $factory->expects(
            $this->at(3)
        )->method(
            'create'
        )->with(
            $secondAttribute,
            $entity
        )->willReturn(
            $secondDataModel
        );

        $this->assertFalse($validator->isValid($entity));
        $this->assertEquals($expectedMessages, $validator->getMessages());
        $this->assertFalse($validator->isValid($entity));
        $this->assertEquals($expectedDouble, $validator->getMessages());
    }

    /**
     * @param array $attributeData
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function _getAttributeMock($attributeData)
    {
        $attribute = $this->getMockBuilder(\Magento\Eav\Model\Attribute::class)
            ->setMethods(
                [
                    'getAttributeCode',
                    'getDataModel',
                    'getFrontendInput',
                    '__wakeup',
                    'getIsVisible',
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();

        if (isset($attributeData['attribute_code'])) {
            $attribute->expects(
                $this->any()
            )->method(
                'getAttributeCode'
            )->willReturn(
                $attributeData['attribute_code']
            );
        }
        if (isset($attributeData['data_model'])) {
            $attribute->expects(
                $this->any()
            )->method(
                'getDataModel'
            )->willReturn(
                $attributeData['data_model']
            );
        }
        if (isset($attributeData['frontend_input'])) {
            $attribute->expects(
                $this->any()
            )->method(
                'getFrontendInput'
            )->willReturn(
                $attributeData['frontend_input']
            );
        }
        if (isset($attributeData['is_visible'])) {
            $attribute->expects($this->any())
                ->method('getIsVisible')
                ->willReturn($attributeData['is_visible']);
        }
        return $attribute;
    }

    /**
     * @param boolean $returnValue
     * @param string|null $argument
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function _getDataModelMock($returnValue, $argument = null)
    {
        $dataModel = $this->getMockBuilder(
            \Magento\Eav\Model\Attribute\Data\AbstractData::class
        )->disableOriginalConstructor()->setMethods(
            ['setExtractedData', 'validateValue']
        )->getMockForAbstractClass();
        if ($argument) {
            $dataModel->expects(
                $this->once()
            )->method(
                'validateValue'
            )->with(
                $argument
            )->willReturn(
                $returnValue
            );
        } else {
            $dataModel->expects($this->any())->method('validateValue')->willReturn($returnValue);
        }
        return $dataModel;
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function _getEntityMock()
    {
        $entity = $this->getMockBuilder(
            \Magento\Framework\Model\AbstractModel::class
        )->setMethods(
            ['getAttribute', 'getResource', 'getEntityType', '__wakeup']
        )->disableOriginalConstructor()->getMock();
        return $entity;
    }

    /**
     * Test for isValid() without data for attribute.
     *
     * @return void
     */
    public function testIsValidWithoutData() : void
    {
        $attributeData = ['attribute_code' => 'attribute', 'frontend_input' => 'text', 'is_visible' => true];
        $entity = $this->_getEntityMock();
        $attribute = $this->_getAttributeMock($attributeData);
        $dataModel = $this->_getDataModelMock(true, $this->logicalAnd($this->isEmpty(), $this->isType('string')));
        $dataModel->expects($this->once())->method('setExtractedData')->with([])->willReturnSelf();
        $this->attrDataFactory->expects($this->once())
            ->method('create')
            ->with($attribute, $entity)
            ->willReturn($dataModel);
        $this->model->setAttributes([$attribute])->setData([]);
        $this->assertTrue($this->model->isValid($entity));
    }
}
