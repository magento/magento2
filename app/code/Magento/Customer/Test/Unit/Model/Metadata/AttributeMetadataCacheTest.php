<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Model\Metadata;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Cache\StateInterface;
use Magento\Customer\Api\Data\AttributeMetadataInterface;
use Magento\Customer\Api\Data\AttributeMetadataInterfaceFactory;
use Magento\Customer\Api\Data\OptionInterface;
use Magento\Customer\Api\Data\OptionInterfaceFactory;
use Magento\Customer\Api\Data\ValidationRuleInterface;
use Magento\Customer\Api\Data\ValidationRuleInterfaceFactory;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Eav\Model\Cache\Type;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Config\App\Config\Type\System;
use Magento\Customer\Model\Data\AttributeMetadata;
use Magento\Customer\Model\Data\Option;
use Magento\Customer\Model\Data\ValidationRule;
use Magento\Customer\Model\Metadata\AttributeMetadataCache;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AttributeMetadataCacheTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CacheInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cacheMock;

    /**
     * @var StateInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stateMock;

    /**
     * @var AttributeMetadataInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $attributeMetadataFactoryMock;

    /**
     * @var OptionInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $optionFactoryMock;

    /**
     * @var ValidationRuleInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $validationRuleFactoryMock;

    /**
     * @var SerializerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializerMock;

    /**
     * @var AttributeMetadataCache|\PHPUnit_Framework_MockObject_MockObject
     */
    private $attributeMetadataCache;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->cacheMock = $this->getMock(CacheInterface::class);
        $this->stateMock = $this->getMock(StateInterface::class);
        $this->attributeMetadataFactoryMock = $this->getMock(
            AttributeMetadataInterfaceFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->optionFactoryMock = $this->getMock(
            OptionInterfaceFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->validationRuleFactoryMock = $this->getMock(
            ValidationRuleInterfaceFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->serializerMock = $this->getMock(SerializerInterface::class);
        $this->attributeMetadataMock = $this->getMock(AttributeMetadataInterface::class);
        $this->optionMock = $this->getMock(OptionInterface::class);
        $this->validationRuleMock = $this->getMock(ValidationRuleInterface::class);
        $this->attributeMetadataCache = $objectManager->getObject(
            AttributeMetadataCache::class,
            [
                'cache' => $this->cacheMock,
                'state' => $this->stateMock,
                'attributeMetadataFactory' => $this->attributeMetadataFactoryMock,
                'optionFactory' => $this->optionFactoryMock,
                'validationRuleFactory' => $this->validationRuleFactoryMock,
                'serializer' => $this->serializerMock
            ]
        );
    }

    public function testLoadCacheDisabled()
    {
        $entityType = 'EntityType';
        $suffix = 'none';
        $this->stateMock->expects($this->once())
            ->method('isEnabled')
            ->with(Type::TYPE_IDENTIFIER)
            ->willReturn(false);
        $this->assertFalse($this->attributeMetadataCache->load($entityType, $suffix));
        $this->attributeMetadataCache->load($entityType, $suffix);
    }

    public function testLoadNoCache()
    {
        $entityType = 'EntityType';
        $suffix = 'none';
        $cacheKey = AttributeMetadataCache::ATTRIBUTE_METADATA_CACHE_PREFIX . $entityType . $suffix;
        $this->stateMock->expects($this->once())
            ->method('isEnabled')
            ->with(Type::TYPE_IDENTIFIER)
            ->willReturn(true);
        $this->cacheMock->expects($this->once())
            ->method('load')
            ->with($cacheKey)
            ->willReturn(false);
        $this->assertFalse($this->attributeMetadataCache->load($entityType, $suffix));
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testLoad()
    {
        $entityType = 'EntityType';
        $suffix = 'none';
        $cacheKey = AttributeMetadataCache::ATTRIBUTE_METADATA_CACHE_PREFIX . $entityType . $suffix;
        $serializedString = 'serialized string';
        $optionOneData = [
            'label' => 'Label 1',
            'options' => null
        ];
        $optionThreeData = [
            'label' => 'Label 3',
            'options' => null
        ];
        $optionFourData = [
            'label' => 'Label 4',
            'options' => null
        ];
        $optionTwoData = [
            'label' => 'Label 2',
            'options' => [$optionThreeData, $optionFourData]
        ];
        $validationRuleOneData = [
            'name' => 'Name 1',
            'value' => 'Value 1'
        ];
        $attributeMetadataOneData = [
            'attribute_code' => 'attribute_code',
            'frontend_input' => 'hidden',
            'options' => [$optionOneData, $optionTwoData],
            'validation_rules' => [$validationRuleOneData]
        ];
        $this->stateMock->expects($this->once())
            ->method('isEnabled')
            ->with(Type::TYPE_IDENTIFIER)
            ->willReturn(true);
        $this->cacheMock->expects($this->once())
            ->method('load')
            ->with($cacheKey)
            ->willReturn($serializedString);
        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->with($serializedString)
            ->willReturn([$attributeMetadataOneData]);

        $optionOne = new Option($optionOneData);
        $this->optionFactoryMock->expects($this->at(0))
            ->method('create')
            ->with(['data' => $optionOneData])
            ->willReturn($optionOne);
        $optionThree = new Option($optionThreeData);
        $this->optionFactoryMock->expects($this->at(1))
            ->method('create')
            ->with(['data' => $optionThreeData])
            ->willReturn($optionThree);
        $optionFour = new Option($optionFourData);
        $this->optionFactoryMock->expects($this->at(2))
            ->method('create')
            ->with(['data' => $optionFourData])
            ->willReturn($optionFour);

        $optionTwoDataForFactory = [
            'label' => 'Label 2',
            'options' => [$optionThree, $optionFour]
        ];
        $optionFour = new Option($optionTwoDataForFactory);
        $this->optionFactoryMock->expects($this->at(3))
            ->method('create')
            ->with(['data' => $optionTwoDataForFactory])
            ->willReturn($optionFour);

        $validationRule = new ValidationRule($validationRuleOneData);
        $this->validationRuleFactoryMock->expects($this->once())
            ->method('create')
            ->with(['data' => $validationRuleOneData])
            ->willReturn($validationRule);

        $attributeMetadataDataForFactory = [
            'attribute_code' => 'attribute_code',
            'frontend_input' => 'hidden',
            'options' => [$optionOne, $optionFour],
            'validation_rules' => [$validationRule]
        ];

        $this->attributeMetadataFactoryMock->expects($this->once())
            ->method('create')
            ->with(['data' => $attributeMetadataDataForFactory])
            ->willReturn(
                new AttributeMetadata($attributeMetadataDataForFactory)
            );

        $attributeMetadata = $this->attributeMetadataCache->load($entityType, $suffix);

        $this->assertEquals(
            $attributeMetadataOneData['attribute_code'],
            $attributeMetadata[0]->getAttributeCode()
        );
        $this->assertEquals(
            $optionOneData['label'],
            $attributeMetadata[0]->getOptions()[0]->getLabel()
        );
        $this->assertEquals(
            $optionThreeData['label'],
            $attributeMetadata[0]->getOptions()[1]->getOptions()[0]->getLabel()
        );
        $this->assertEquals(
            $validationRuleOneData['name'],
            $attributeMetadata[0]->getValidationRules()[0]->getName()
        );
    }

    public function testSaveCacheDisabled()
    {
        $entityType = 'EntityType';
        $suffix = 'none';
        $attributes = [['foo'], ['bar']];
        $this->stateMock->expects($this->once())
            ->method('isEnabled')
            ->with(Type::TYPE_IDENTIFIER)
            ->willReturn(false);
        $this->attributeMetadataCache->save($entityType, $attributes, $suffix);
        $this->assertEquals(
            $attributes,
            $this->attributeMetadataCache->load($entityType, $suffix)
        );
    }

    public function testSave()
    {
        $entityType = 'EntityType';
        $suffix = 'none';
        $cacheKey = AttributeMetadataCache::ATTRIBUTE_METADATA_CACHE_PREFIX . $entityType . $suffix;
        $serializedString = 'serialized string';
        $attributeMetadataOneData = [
            'attribute_code' => 'attribute_code',
            'frontend_input' => 'hidden',
        ];
        $this->stateMock->expects($this->once())
            ->method('isEnabled')
            ->with(Type::TYPE_IDENTIFIER)
            ->willReturn(true);
        $attributeMetadataMock = $this->getMock(
            AttributeMetadata::class,
            [],
            [],
            '',
            false
        );
        $attributeMetadataMock->expects($this->once())
            ->method('__toArray')
            ->willReturn($attributeMetadataOneData);
        $this->serializerMock->expects($this->once())
            ->method('serialize')
            ->with([$attributeMetadataOneData])
            ->willReturn($serializedString);
        $this->cacheMock->expects($this->once())
            ->method('save')
            ->with(
                $serializedString,
                $cacheKey,
                [
                    Type::CACHE_TAG,
                    Attribute::CACHE_TAG,
                    System::CACHE_TAG
                ]
            );
        $attributesMetadata = [$attributeMetadataMock];
        $this->attributeMetadataCache->save($entityType, $attributesMetadata, $suffix);
        $this->assertSame(
            $attributesMetadata,
            $this->attributeMetadataCache->load($entityType, $suffix)
        );
    }

    public function testCleanCacheDisabled()
    {
        $this->stateMock->expects($this->once())
            ->method('isEnabled')
            ->with(Type::TYPE_IDENTIFIER)
            ->willReturn(false);
        $this->cacheMock->expects($this->never())
            ->method('clean');
        $this->attributeMetadataCache->clean();
    }

    public function testClean()
    {
        $this->stateMock->expects($this->once())
            ->method('isEnabled')
            ->with(Type::TYPE_IDENTIFIER)
            ->willReturn(true);
        $this->cacheMock->expects($this->once())
            ->method('clean');
        $this->attributeMetadataCache->clean();
    }
}
