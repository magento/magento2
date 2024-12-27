<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Eav\Test\Unit\Model\Validator\Attribute;

use Magento\Eav\Model\Attribute;
use Magento\Eav\Model\Attribute\Data\AbstractData;
use Magento\Eav\Model\AttributeDataFactory;
use Magento\Eav\Model\Entity\AbstractEntity;
use Magento\Eav\Model\Validator\Attribute\Data;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Stdlib\StringUtils;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Magento\Eav\Model\Validator\Attribute\Data
 */
class DataTest extends TestCase
{
    /**
     * @var AttributeDataFactory|MockObject
     */
    private $attrDataFactory;

    /**
     * @var Data
     */
    private $model;

    /**
     * @var \Magento\Eav\Model\Config|MockObject
     */
    private $eavConfigMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->attrDataFactory = $this->getMockBuilder(AttributeDataFactory::class)
            ->onlyMethods(['create'])
            ->setConstructorArgs(
                [
                    'objectManager' => $this->getMockForAbstractClass(ObjectManagerInterface::class),
                    'string' => $this->createMock(StringUtils::class)
                ]
            )
            ->getMock();
        $this->createMock(ObjectManagerInterface::class);
        ObjectManager::setInstance($this->createMock(ObjectManagerInterface::class));
        $this->eavConfigMock = $this->getMockBuilder(\Magento\Eav\Model\Config::class)
            ->onlyMethods(['getEntityType'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = new Data($this->attrDataFactory);
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
     *
     * @return void
     */
    public function testIsValid(
        $attributeData,
        $result,
        $expected,
        $messages,
        $data = ['attribute' => 'new_test']
    ): void {
        if(!empty($attributeData['data_model']))
        {
            $attributeData['data_model'] = $attributeData['data_model']($this);
        }
        $entity = $this->_getEntityMock();
        $attribute = $this->_getAttributeMock($attributeData);
        $attrDataFactory = $this->getMockBuilder(AttributeDataFactory::class)
            ->onlyMethods(['create'])
            ->setConstructorArgs(
                [
                    'objectManager' => $this->getMockForAbstractClass(ObjectManagerInterface::class),
                    'string' => $this->createMock(StringUtils::class)
                ]
            )
            ->getMock();

        $validator = new Data($attrDataFactory);
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
    public static function isValidDataProvider(): array
    {
        return [
            'is_valid' => [
                'attributeData' => [
                    'attribute_code' => 'attribute',
                    'data_model' => static fn (self $testCase) => $testCase->_getDataModelMock(null),
                    'frontend_input' => 'text',
                    'is_visible' => true
                ],
                'result' => true,
                'expected' => true,
                'messages' => []
            ],
            'is_invalid' => [
                'attributeData' => [
                    'attribute_code' => 'attribute',
                    'data_model' => static fn (self $testCase) => $testCase->_getDataModelMock(null),
                    'frontend_input' => 'text',
                    'is_visible' => true
                ],
                'result' => ['Error'],
                'expected' => false,
                'messages' => ['attribute' => ['Error']]
            ],
            'no_data_models' => [
                'attributeData' => [
                    'attribute_code' => 'attribute',
                    'frontend_input' => 'text',
                    'is_visible' => true
                ],
                'result' => ['Error'],
                'expected' => false,
                'messages' => ['attribute' => ['Error']]
            ],
            'no_data_models_no_frontend_input' => [
                'attributeData' => [
                    'attribute_code' => 'attribute',
                    'is_visible' => true
                ],
                'result' => ['Error'],
                'expected' => true,
                'messages' => []
            ],
            'no_data_for attribute' => [
                'attributeData' => [
                    'attribute_code' => 'attribute',
                    'data_model' => static fn (self $testCase) => $testCase->_getDataModelMock(null),
                    'frontend_input' => 'text',
                    'is_visible' => true
                ],
                'result' => true,
                'expected' => true,
                'messages' => [],
                'data' => ['attribute2' => 'new_test']
            ],
            'is_valid_data_from_entity' => [
                'attributeData' => [
                    'attribute_code' => 'attribute',
                    'data_model' => static fn (self $testCase) => $testCase->_getDataModelMock(null),
                    'frontend_input' => 'text',
                    'is_visible' => true
                ],
                'result' => true,
                'expected' => true,
                'messages' => [],
                'data' => []
            ],
            'is_invisible' => [
                'attributeData' => [
                    'attribute_code' => 'attribute',
                    'data_model' => static fn (self $testCase) => $testCase->_getDataModelMock(null),
                    'frontend_input' => 'text',
                    'is_visible' => false,
                ],
                'result' => ['Error'],
                'expected' => true,
                'messages' => [],
                'data' => [],
            ],
        ];
    }

    /**
     * Testing \Magento\Eav\Model\Validator\Attribute\Data::isValid
     *
     * In this test entity attributes are got from attribute collection.
     *
     * @return void
     */
    public function testIsValidAttributesFromCollection(): void
    {
        /** @var AbstractEntity $resource */
        $resource = $this->getMockForAbstractClass(AbstractEntity::class, [], '', false);
        $attribute = $this->_getAttributeMock(
            [
                'attribute_code' => 'attribute',
                'data_model' => $this->_getDataModelMock(null),
                'frontend_input' => 'text',
                'is_visible' => true,
            ]
        );
        $entityTypeCode = 'entity_type_code';
        $collection = $this->getMockBuilder(DataObject::class)
            ->addMethods(['getItems'])->getMock();
        $collection->expects($this->once())->method('getItems')->willReturn([$attribute]);
        $entityType = $this->getMockBuilder(DataObject::class)
            ->addMethods(['getAttributeCollection','getEntityTypeCode'])
            ->getMock();
        $entityType->expects($this->atMost(2))->method('getEntityTypeCode')->willReturn($entityTypeCode);
        $entityType->expects($this->once())->method('getAttributeCollection')->willReturn($collection);
        $this->eavConfigMock->expects($this->once())->method('getEntityType')
            ->with($entityTypeCode)->willReturn($entityType);
        $entity = $this->_getEntityMock();
        $entity->expects($this->once())->method('getResource')->willReturn($resource);
        $entity->expects($this->once())->method('getEntityType')->willReturn($entityType);
        $dataModel = $this->_getDataModelMock(true);
        $attrDataFactory = $this->getMockBuilder(AttributeDataFactory::class)
            ->onlyMethods(['create'])
            ->setConstructorArgs(
                [
                    'objectManager' => $this->getMockForAbstractClass(ObjectManagerInterface::class),
                    'string' => $this->createMock(StringUtils::class)
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
        $validator = new Data($attrDataFactory, $this->eavConfigMock);

        $validator->setData(['attribute' => 'new_test_data']);
        $this->assertTrue($validator->isValid($entity));
    }

    /**
     * @param callable $callback
     *
     * @return void
     * @dataProvider allowDenyListProvider
     */
    public function testIsValidExclusionInclusionListChecks($callback): void
    {
        $attribute = $this->_getAttributeMock(
            [
                'attribute_code' => 'attribute',
                'data_model' => $this->_getDataModelMock(null),
                'frontend_input' => 'text',
                'is_visible' => true
            ]
        );
        $secondAttribute = $this->_getAttributeMock(
            [
                'attribute_code' => 'attribute2',
                'data_model' => $this->_getDataModelMock(null),
                'frontend_input' => 'text',
                'is_visible' => true
            ]
        );
        $data = ['attribute' => 'new_test_data', 'attribute2' => 'some data'];
        $entity = $this->_getEntityMock();
        $dataModel = $this->_getDataModelMock(true, $data['attribute']);
        $attrDataFactory = $this->getMockBuilder(AttributeDataFactory::class)
            ->onlyMethods(['create'])
            ->setConstructorArgs(
                [
                    'objectManager' => $this->getMockForAbstractClass(ObjectManagerInterface::class),
                    'string' => $this->createMock(StringUtils::class)
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
        $validator = new Data($attrDataFactory);
        $validator->setAttributes([$attribute, $secondAttribute])->setData($data);
        $callback($validator);
        $this->assertTrue($validator->isValid($entity));
    }

    /**
     * @return array
     */
    public static function allowDenyListProvider(): array
    {
        $allowedCallbackList = function ($validator) {
            $validator->setAllowedAttributesList(['attribute']);
        };

        $deniedCallbackList = function ($validator) {
            $validator->setDeniedAttributesList(['attribute2']);
        };
        return ['allowed' => [$allowedCallbackList], 'denied' => [$deniedCallbackList]];
    }

    /**
     * @return void
     */
    public function testSetAttributesAllowedList(): void
    {
        $this->markTestSkipped('Skipped in #27500 due to testing protected/private methods and properties');

        $attributes = ['attr1', 'attr2', 'attr3'];
        $attrDataFactory = $this->getMockBuilder(AttributeDataFactory::class)
            ->setConstructorArgs(
                [
                    'objectManager' => $this->getMockForAbstractClass(ObjectManagerInterface::class),
                    'string' => $this->createMock(StringUtils::class)
                ]
            )
            ->getMock();
        $validator = new Data($attrDataFactory);
        $result = $validator->setIncludedAttributesList($attributes);

        // phpstan:ignore
        $this->assertAttributeEquals($attributes, '_attributesAllowed', $validator);
        $this->assertEquals($validator, $result);
    }

    /**
     * @return void
     */
    public function testSetAttributesDeniedList(): void
    {
        $this->markTestSkipped('Skipped in #27500 due to testing protected/private methods and properties');

        $attributes = ['attr1', 'attr2', 'attr3'];
        $attrDataFactory = $this->getMockBuilder(AttributeDataFactory::class)
            ->setConstructorArgs(
                [
                    'objectManager' => $this->getMockForAbstractClass(ObjectManagerInterface::class),
                    'string' => $this->createMock(StringUtils::class)
                ]
            )
            ->getMock();
        $validator = new Data($attrDataFactory);
        $result = $validator->setDeniedAttributesList($attributes);
        // phpstan:ignore
        $this->assertAttributeEquals($attributes, '_attributesDenied', $validator);
        $this->assertEquals($validator, $result);
    }

    /**
     * @return void
     */
    public function testAddErrorMessages(): void
    {
        $data = ['attribute1' => 'new_test', 'attribute2' => 'some data'];
        $entity = $this->_getEntityMock();
        $firstAttribute = $this->_getAttributeMock(
            [
                'attribute_code' => 'attribute1',
                'data_model' => $firstDataModel = $this->_getDataModelMock(['Error1']),
                'frontend_input' => 'text',
                'is_visible' => true
            ]
        );
        $secondAttribute = $this->_getAttributeMock(
            [
                'attribute_code' => 'attribute2',
                'data_model' => $secondDataModel = $this->_getDataModelMock(['Error2']),
                'frontend_input' => 'text',
                'is_visible' => true
            ]
        );
        $expectedMessages = ['attribute1' => ['Error1'], 'attribute2' => ['Error2']];
        $expectedDouble = ['attribute1' => ['Error1', 'Error1'], 'attribute2' => ['Error2', 'Error2']];
        $factory = $this->getMockBuilder(AttributeDataFactory::class)
            ->onlyMethods(['create'])
            ->setConstructorArgs(
                [
                    'objectManager' => $this->getMockForAbstractClass(ObjectManagerInterface::class),
                    'string' => $this->createMock(StringUtils::class)
                ]
            )
            ->getMock();
        $validator = new Data($factory);
        $validator->setAttributes([$firstAttribute, $secondAttribute])->setData($data);

        $factory
            ->method('create')
            ->willReturnCallback(
                function (
                    $arg1,
                    $arg2
                ) use (
                    $firstAttribute,
                    $entity,
                    $firstDataModel,
                    $secondDataModel,
                    $secondAttribute
                ) {
                    if ($arg1 === $firstAttribute && $arg2 === $entity) {
                        return $firstDataModel;
                    } elseif ($arg1 === $secondAttribute && $arg2 === $entity) {
                        return $secondDataModel;
                    }
                }
            );

        $this->assertFalse($validator->isValid($entity));
        $this->assertEquals($expectedMessages, $validator->getMessages());
        $this->assertFalse($validator->isValid($entity));
        $this->assertEquals($expectedDouble, $validator->getMessages());
    }

    /**
     * @param array $attributeData
     *
     * @return MockObject
     */
    protected function _getAttributeMock($attributeData): MockObject
    {
        $attribute = $this->getMockBuilder(Attribute::class)
            ->onlyMethods(
                [
                    'getAttributeCode',
                    'getFrontendInput',
                    '__wakeup',
                    'getIsVisible'
                ]
            )
            ->addMethods(['getDataModel'])
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
     *
     * @return MockObject
     */
    protected function _getDataModelMock($returnValue, $argument = null): MockObject
    {
        $dataModel = $this->getMockBuilder(AbstractData::class)->disableOriginalConstructor()
            ->onlyMethods(['setExtractedData', 'validateValue'])->getMockForAbstractClass();
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
     * @return MockObject
     */
    protected function _getEntityMock(): MockObject
    {
        $entity = $this->getMockBuilder(AbstractModel::class)
            ->onlyMethods(['getResource', '__wakeup'])
            ->addMethods(['getAttribute', 'getEntityType'])
            ->disableOriginalConstructor()
            ->getMock();

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
