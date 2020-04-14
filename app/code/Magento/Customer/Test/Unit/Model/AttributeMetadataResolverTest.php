<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model;

use Magento\Customer\Model\Attribute;
use Magento\Customer\Model\AttributeMetadataResolver;
use Magento\Customer\Model\FileUploaderDataResolver;
use Magento\Customer\Model\GroupManagement;
use Magento\Eav\Model\Entity\Type;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Ui\DataProvider\EavValidationRules;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class AttributeMetadataResolverTest
 *
 * Validate attributeMetadata contains correct values in meta data array
 */
class AttributeMetadataResolverTest extends TestCase
{
    /**
     * @var AttributeMetadataResolver
     */
    private $model;

    /**
     * @var EavValidationRules|MockObject
     */
    private $eavValidationRulesMock;

    /**
     * @var FileUploaderDataResolver|MockObject
     */
    private $fileUploaderDataResolverMock;

    /**
     * @var GroupManagement|MockObject
     */
    private $groupManagementMock;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->eavValidationRulesMock = $this->getMockBuilder(EavValidationRules::class)
            ->setMethods(['build'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->fileUploaderDataResolverMock = $this->getMockBuilder(FileUploaderDataResolver::class)
            ->setMethods(['overrideFileUploaderMetadata'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->groupManagementMock = $this->getMockBuilder(GroupManagement::class)
            ->setMethods(['getId', 'getDefaultGroup'])
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            AttributeMetadataResolver::class,
            [
                'eavValidationRules' => $this->eavValidationRulesMock,
                'fileUploaderDataResolver' => $this->fileUploaderDataResolverMock,
                'groupManagement' => $this->groupManagementMock
            ]
        );
    }

    /**
     * Test to get meta data of the customer or customer address attribute
     *
     * @return void
     */
    public function testGetAttributesMetaHasDefaultAttributeValue(): void
    {
        $attributeMock = $this->getMockBuilder(Attribute::class)
            ->setMethods([
                'usesSource',
                'getDataUsingMethod',
                'getAttributeCode',
                'getFrontendInput',
                'getSource',
                'setDataUsingMethod'
            ])
            ->disableOriginalConstructor()
            ->getMock();

        $rules = [
            'required-entry' => true
        ];
        $defaultGroupId = '3';
        $allowToShowHiddenAttributes = false;
        $usesSource = false;
        $entityType = $this->getMockBuilder(Type::class)
            ->disableOriginalConstructor()
            ->getMock();
        $attributeMock->expects($this->once())
            ->method('usesSource')
            ->willReturn($usesSource);
        $attributeMock->expects($this->exactly(2))
            ->method('getAttributeCode')
            ->willReturn('group_id');
        $this->groupManagementMock->expects($this->once())
            ->method('getDefaultGroup')
            ->willReturnSelf();
        $this->groupManagementMock->expects($this->once())
            ->method('getId')
            ->willReturn($defaultGroupId);
        $attributeMock->expects($this->at(9))
            ->method('getDataUsingMethod')
            ->with('default_value')
            ->willReturn($defaultGroupId);
        $attributeMock->expects($this->once())
            ->method('setDataUsingMethod')
            ->willReturnSelf();
        $this->eavValidationRulesMock->expects($this->once())
            ->method('build')
            ->with($attributeMock)
            ->willReturn($rules);
        $this->fileUploaderDataResolverMock->expects($this->once())
            ->method('overrideFileUploaderMetadata')
            ->with($entityType, $attributeMock)
            ->willReturnSelf();

        $meta = $this->model->getAttributesMeta($attributeMock, $entityType, $allowToShowHiddenAttributes);
        $this->assertArrayHasKey('default', $meta['arguments']['data']['config']);
        $this->assertEquals($defaultGroupId, $meta['arguments']['data']['config']['default']);
    }
}
