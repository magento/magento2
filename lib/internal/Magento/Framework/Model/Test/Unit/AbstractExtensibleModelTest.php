<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Model\Test\Unit;

use Magento\Framework\Api\AttributeInterface;
use Magento\Framework\Api\AttributeValue;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\CustomAttributesDataInterface;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Api\MetadataServiceInterface;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\State;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Framework\Model\ActionValidator\RemoveAction;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Registry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AbstractExtensibleModelTest extends TestCase
{
    /**
     * @var AbstractExtensibleModel
     */
    protected $model;

    /**
     * @var Context|MockObject
     */
    protected $contextMock;

    /**
     * @var Registry|MockObject
     */
    protected $registryMock;

    /**
     * @var AbstractDb|MockObject
     */
    protected $resourceMock;

    /**
     * @var \Magento\Framework\Data\Collection\AbstractDb|MockObject
     */
    protected $resourceCollectionMock;

    /** @var MetadataServiceInterface|MockObject */
    protected $metadataServiceMock;

    /** @var AttributeValueFactory|MockObject */
    protected $attributeValueFactoryMock;

    /**
     * @var MockObject
     */
    protected $actionValidatorMock;

    /**
     * @var AttributeValue
     */
    protected $customAttribute;

    protected function setUp(): void
    {
        $this->actionValidatorMock = $this->createMock(RemoveAction::class);
        $this->contextMock = new Context(
            $this->getMockForAbstractClass(LoggerInterface::class),
            $this->getMockForAbstractClass(ManagerInterface::class),
            $this->getMockForAbstractClass(CacheInterface::class),
            $this->createMock(State::class),
            $this->actionValidatorMock
        );
        $this->registryMock = $this->createMock(Registry::class);
        $this->resourceMock = $this->createPartialMock(AbstractDb::class, [
            '_construct',
            'getConnection',
            '__wakeup',
            'commit',
            'delete',
            'getIdFieldName',
            'rollBack'
        ]);
        $this->resourceCollectionMock = $this->getMockBuilder(\Magento\Framework\Data\Collection\AbstractDb::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->metadataServiceMock = $this->getMockBuilder(MetadataServiceInterface::class)
            ->getMock();
        $this->metadataServiceMock
            ->expects($this->any())
            ->method('getCustomAttributesMetadata')
            ->willReturn(
                [
                    new DataObject(['attribute_code' => 'attribute1']),
                    new DataObject(['attribute_code' => 'attribute2']),
                    new DataObject(['attribute_code' => 'attribute3']),
                ]
            );
        $extensionAttributesFactory = $this->getMockBuilder(ExtensionAttributesFactory::class)
            ->addMethods(['extractExtensionAttributes'])
            ->disableOriginalConstructor()
            ->getMock();
        $extensionAttributesFactory->expects($this->any())
            ->method('extractExtensionAttributes')
            ->willReturnArgument(1);
        $this->attributeValueFactoryMock = $this->getMockBuilder(AttributeValueFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = $this->getMockForAbstractClass(
            AbstractExtensibleModel::class,
            [
                $this->contextMock,
                $this->registryMock,
                $extensionAttributesFactory,
                $this->attributeValueFactoryMock,
                $this->resourceMock,
                $this->resourceCollectionMock
            ],
            '',
            true,
            true,
            true,
            ['getCustomAttributesCodes']
        );
        $this->customAttribute = new AttributeValue();
    }

    /**
     * Test implementation of interface for work with custom attributes.
     */
    public function testCustomAttributesWithEmptyCustomAttributes()
    {
        $this->model->expects($this->any())->method('getCustomAttributesCodes')->willReturn([]);
        $this->assertEquals(
            [],
            $this->model->getCustomAttributes(),
            "Empty array is expected as a result of getCustomAttributes() when custom attributes are not set."
        );
        $this->assertNull(
            $this->model->getCustomAttribute('not_existing_custom_attribute'),
            "Null is expected as a result of getCustomAttribute(\$code) when custom attribute is not set."
        );
        $attributesAsArray = ['attribute1' => true, 'attribute2' => 'Attribute Value', 'attribute3' => 333];
        $this->addCustomAttributesToModel($attributesAsArray, $this->model);
        $this->assertEquals(
            [],
            $this->model->getCustomAttributes(),
            'Custom attributes retrieved from the model using getCustomAttributes() are invalid.'
        );
    }

    public function testCustomAttributesWithNonEmptyCustomAttributes()
    {
        $customAttributeCode = 'attribute_code';
        $customAttributeValue = 'attribute_value';
        $this->model->expects($this->any())->method('getCustomAttributesCodes')->willReturn([$customAttributeCode]);

        $this->assertEquals(
            [],
            $this->model->getCustomAttributes(),
            "Empty array is expected as a result of getCustomAttributes() when custom attributes are not set."
        );
        $this->attributeValueFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->customAttribute);
        $this->customAttribute->setAttributeCode($customAttributeCode)->setValue($customAttributeValue);
        $this->model->setData($customAttributeCode, $customAttributeValue);
        $this->assertEquals(
            [$this->customAttribute],
            $this->model->getCustomAttributes(),
            "One custom attribute expected"
        );
        $this->assertNotNull($this->model->getCustomAttribute($customAttributeCode), 'customer attribute expected');
        $this->assertEquals(
            $customAttributeValue,
            $this->model->getCustomAttribute($customAttributeCode)->getValue(),
            "Custom attribute value is incorrect"
        );
        //unset the data
        $this->model->unsetData($customAttributeCode);
        $this->assertEquals(
            [],
            $this->model->getCustomAttributes(),
            "Empty array is expected as a result of getCustomAttributes() when custom attributes are not set."
        );
    }

    /**
     * Test if getData works with custom attributes as expected
     */
    public function testGetDataWithCustomAttributes()
    {
        $this->model->expects($this->any())->method('getCustomAttributesCodes')->willReturn([]);
        $attributesAsArray = [
            'attribute1' => true,
            'attribute2' => 'Attribute Value',
            'attribute3' => 333,
            'invalid' => true,
        ];
        $modelData = ['key1' => 'value1', 'key2' => 222];
        foreach ($modelData as $key => $value) {
            $this->model->setData($key, $value);
        }
        $this->addCustomAttributesToModel($attributesAsArray, $this->model);
        $this->assertEquals(
            $modelData,
            $this->model->getData(),
            'All model data should be represented as a flat array, including custom attributes.'
        );
        foreach ($modelData as $field => $value) {
            $this->assertEquals(
                $value,
                $this->model->getData($field),
                "Model data item '{$field}' was retrieved incorrectly."
            );
        }
    }

    public function testRestrictedCustomAttributesGet()
    {
        $this->expectException('LogicException');
        $this->model->getData(CustomAttributesDataInterface::CUSTOM_ATTRIBUTES);
    }

    public function testSetCustomAttributesAsLiterals()
    {
        $this->model->expects($this->any())->method('getCustomAttributesCodes')->willReturn([]);
        $attributeCode = 'attribute2';
        $attributeValue = 'attribute_value';
        $attributeMock = $this->getMockBuilder(AttributeValue::class)
            ->disableOriginalConstructor()
            ->getMock();
        $attributeMock->expects($this->never())
            ->method('setAttributeCode')
            ->with($attributeCode)->willReturnSelf();
        $attributeMock->expects($this->never())
            ->method('setValue')
            ->with($attributeValue)->willReturnSelf();
        $this->attributeValueFactoryMock->expects($this->never())->method('create')
            ->willReturn($attributeMock);
        $this->model->setData(
            CustomAttributesDataInterface::CUSTOM_ATTRIBUTES,
            [$attributeCode => $attributeValue]
        );
    }

    /**
     * @param string[] $attributesAsArray
     * @param AbstractExtensibleModel $model
     * @return AttributeInterface[]
     */
    protected function addCustomAttributesToModel($attributesAsArray, $model)
    {
        $addedAttributes = [];
        foreach ($attributesAsArray as $attributeCode => $attributeValue) {
            $addedAttributes[$attributeCode] = new AttributeValue(
                [
                    AttributeValue::ATTRIBUTE_CODE => $attributeCode,
                    AttributeValue::VALUE => $attributeValue,
                ]
            );
        }
        $model->setData(
            array_merge(
                $model->getData(),
                [CustomAttributesDataInterface::CUSTOM_ATTRIBUTES => $addedAttributes]
            )
        );
        return $addedAttributes;
    }
}
