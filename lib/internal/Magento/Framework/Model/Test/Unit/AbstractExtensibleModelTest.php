<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model\Test\Unit;

use Magento\Framework\Api\AttributeValue;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AbstractExtensibleModelTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Model\AbstractExtensibleModel
     */
    protected $model;

    /**
     * @var \Magento\Framework\Model\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $registryMock;

    /**
     * @var \Magento\Framework\Model\ResourceModel\Db\AbstractDb|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceMock;

    /**
     * @var \Magento\Framework\Data\Collection\AbstractDb|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceCollectionMock;

    /** @var \Magento\Framework\Api\MetadataServiceInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $metadataServiceMock;

    /** @var \Magento\Framework\Api\AttributeValueFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $attributeValueFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $actionValidatorMock;

    /**
     * @var AttributeValue
     */
    protected $customAttribute;

    protected function setUp()
    {
        $this->actionValidatorMock = $this->getMock(
            \Magento\Framework\Model\ActionValidator\RemoveAction::class,
            [],
            [],
            '',
            false
        );
        $this->contextMock = new \Magento\Framework\Model\Context(
            $this->getMock(\Psr\Log\LoggerInterface::class),
            $this->getMock(\Magento\Framework\Event\ManagerInterface::class, [], [], '', false),
            $this->getMock(\Magento\Framework\App\CacheInterface::class, [], [], '', false),
            $this->getMock(\Magento\Framework\App\State::class, [], [], '', false),
            $this->actionValidatorMock
        );
        $this->registryMock = $this->getMock(\Magento\Framework\Registry::class, [], [], '', false);
        $this->resourceMock = $this->getMock(
            \Magento\Framework\Model\ResourceModel\Db\AbstractDb::class,
            [
                '_construct',
                'getConnection',
                '__wakeup',
                'commit',
                'delete',
                'getIdFieldName',
                'rollBack'
            ],
            [],
            '',
            false
        );
        $this->resourceCollectionMock = $this->getMockBuilder(\Magento\Framework\Data\Collection\AbstractDb::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->metadataServiceMock = $this->getMockBuilder(\Magento\Framework\Api\MetadataServiceInterface::class)
            ->getMock();
        $this->metadataServiceMock
            ->expects($this->any())
            ->method('getCustomAttributesMetadata')
            ->willReturn(
                [
                    new \Magento\Framework\DataObject(['attribute_code' => 'attribute1']),
                    new \Magento\Framework\DataObject(['attribute_code' => 'attribute2']),
                    new \Magento\Framework\DataObject(['attribute_code' => 'attribute3']),
                ]
            );
        $extensionAttributesFactory = $this->getMockBuilder(\Magento\Framework\Api\ExtensionAttributesFactory::class)
            ->setMethods(['extractExtensionAttributes'])
            ->disableOriginalConstructor()
            ->getMock();
        $extensionAttributesFactory->expects($this->any())
            ->method('extractExtensionAttributes')
            ->willReturnArgument(1);
        $this->attributeValueFactoryMock = $this->getMockBuilder(\Magento\Framework\Api\AttributeValueFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = $this->getMockForAbstractClass(
            \Magento\Framework\Model\AbstractExtensibleModel::class,
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
        $this->assertEquals(
            null,
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

    /**
     * @expectedException \LogicException
     */
    public function testRestrictedCustomAttributesGet()
    {
        $this->model->getData(\Magento\Framework\Api\CustomAttributesDataInterface::CUSTOM_ATTRIBUTES);
    }

    public function testSetCustomAttributesAsLiterals()
    {
        $this->model->expects($this->any())->method('getCustomAttributesCodes')->willReturn([]);
        $attributeCode = 'attribute2';
        $attributeValue = 'attribute_value';
        $attributeMock = $this->getMockBuilder(\Magento\Framework\Api\AttributeValue::class)
            ->disableOriginalConstructor()
            ->getMock();
        $attributeMock->expects($this->never())
            ->method('setAttributeCode')
            ->with($attributeCode)
            ->will($this->returnSelf());
        $attributeMock->expects($this->never())
            ->method('setValue')
            ->with($attributeValue)
            ->will($this->returnSelf());
        $this->attributeValueFactoryMock->expects($this->never())->method('create')
            ->willReturn($attributeMock);
        $this->model->setData(
            \Magento\Framework\Api\CustomAttributesDataInterface::CUSTOM_ATTRIBUTES,
            [$attributeCode => $attributeValue]
        );
    }

    /**
     * @param string[] $attributesAsArray
     * @param \Magento\Framework\Model\AbstractExtensibleModel $model
     * @return \Magento\Framework\Api\AttributeInterface[]
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
                [\Magento\Framework\Api\CustomAttributesDataInterface::CUSTOM_ATTRIBUTES => $addedAttributes]
            )
        );
        return $addedAttributes;
    }
}
