<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

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
     * @var \Magento\Framework\Model\Resource\Db\AbstractDb|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceMock;

    /**
     * @var \Magento\Framework\Data\Collection\Db|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceCollectionMock;

    /** @var \Magento\Framework\Api\MetadataServiceInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $metadataServiceMock;

    /** @var \Magento\Framework\Api\AttributeDataBuilder|\PHPUnit_Framework_MockObject_MockObject */
    protected $attributeDataBuilderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $actionValidatorMock;

    protected function setUp()
    {
        $this->actionValidatorMock = $this->getMock(
            '\Magento\Framework\Model\ActionValidator\RemoveAction',
            [],
            [],
            '',
            false
        );
        $this->contextMock = new \Magento\Framework\Model\Context(
            $this->getMock('Psr\Log\LoggerInterface'),
            $this->getMock('Magento\Framework\Event\ManagerInterface', [], [], '', false),
            $this->getMock('Magento\Framework\App\CacheInterface', [], [], '', false),
            $this->getMock('Magento\Framework\App\State', [], [], '', false),
            $this->actionValidatorMock
        );
        $this->registryMock = $this->getMock('Magento\Framework\Registry', [], [], '', false);
        $this->resourceMock = $this->getMock(
            'Magento\Framework\Model\Resource\Db\AbstractDb',
            [
                '_construct',
                '_getReadAdapter',
                '_getWriteAdapter',
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
        $this->resourceCollectionMock = $this->getMock(
            'Magento\Framework\Data\Collection\Db',
            [],
            [],
            '',
            false
        );
        $this->metadataServiceMock = $this->getMockBuilder('Magento\Framework\Api\MetadataServiceInterface')->getMock();
        $this->metadataServiceMock
            ->expects($this->any())
            ->method('getCustomAttributesMetadata')
            ->willReturn(
                [
                    new \Magento\Framework\Object(['attribute_code' => 'attribute1']),
                    new \Magento\Framework\Object(['attribute_code' => 'attribute2']),
                    new \Magento\Framework\Object(['attribute_code' => 'attribute3']),
                ]
            );
        $this->attributeDataBuilderMock = $this->getMockBuilder('Magento\Framework\Api\AttributeDataBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = $this->getMockForAbstractClass(
            'Magento\Framework\Model\AbstractExtensibleModel',
            [
                $this->contextMock,
                $this->registryMock,
                $this->metadataServiceMock,
                $this->attributeDataBuilderMock,
                $this->resourceMock,
                $this->resourceCollectionMock
            ]
        );
    }

    /**
     * Test implementation of interface for work with custom attributes.
     */
    public function testCustomAttributes()
    {
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
        $addedAttributes = $this->addCustomAttributesToModel($attributesAsArray, $this->model);
        $addedAttributes = array_values($addedAttributes);
        $this->assertEquals(
            $addedAttributes,
            $this->model->getCustomAttributes(),
            'Custom attributes retrieved from the model using getCustomAttributes() are invalid.'
        );
    }

    /**
     * Test if getData works with custom attributes as expected
     */
    public function testGetDataWithCustomAttributes()
    {
        $attributesAsArray = [
            'attribute1' => true,
            'attribute2' => 'Attribute Value',
            'attribute3' => 333,
            'invalid' => true,
        ];
        $modelData = ['key1' => 'value1', 'key2' => 222];
        $this->model->setData($modelData);
        $addedAttributes = $this->addCustomAttributesToModel($attributesAsArray, $this->model);
        $modelDataAsFlatArray = array_merge($modelData, $addedAttributes);
        unset($modelDataAsFlatArray['invalid']);
        $this->assertEquals(
            $modelDataAsFlatArray,
            $this->model->getData(),
            'All model data should be represented as a flat array, including custom attributes.'
        );
        foreach ($modelDataAsFlatArray as $field => $value) {
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
        $this->model->getData(\Magento\Framework\Api\ExtensibleDataInterface::CUSTOM_ATTRIBUTES);
    }

    public function testSetCustomAttributesAsLiterals()
    {
        $attributeCode = 'attribute2';
        $attributeValue = 'attribute_value';
        $this->attributeDataBuilderMock->expects($this->once())
            ->method('setAttributeCode')
            ->with($attributeCode)
            ->willReturn($this->attributeDataBuilderMock);
        $this->attributeDataBuilderMock->expects($this->once())
            ->method('setValue')
            ->with($attributeValue)
            ->willReturn($this->attributeDataBuilderMock);
        $this->attributeDataBuilderMock->expects($this->once())->method('create');
        $this->model->setData(
            \Magento\Framework\Api\ExtensibleDataInterface::CUSTOM_ATTRIBUTES,
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
        $objectManager = new ObjectManagerHelper($this);
        /** @var \Magento\Framework\Api\AttributeDataBuilder $attributeValueBuilder */
        $attributeValueBuilder = $objectManager->getObject('Magento\Framework\Api\AttributeDataBuilder');
        $addedAttributes = [];
        foreach ($attributesAsArray as $attributeCode => $attributeValue) {
            $addedAttributes[$attributeCode] = $attributeValueBuilder
                ->setAttributeCode($attributeCode)
                ->setValue($attributeValue)
                ->create();
        }
        $model->setData(
            array_merge(
                $model->getData(),
                [\Magento\Framework\Api\ExtensibleDataInterface::CUSTOM_ATTRIBUTES => $addedAttributes]
            )
        );
        return $addedAttributes;
    }
}
