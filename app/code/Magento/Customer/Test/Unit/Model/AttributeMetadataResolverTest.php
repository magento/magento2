<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model;

use Magento\Customer\Model\Attribute;
use Magento\Customer\Model\AttributeMetadataResolver;
use Magento\Customer\Model\Config\Share as ShareConfig;
use Magento\Customer\Model\FileUploaderDataResolver;
use Magento\Customer\Model\GroupManagement;
use Magento\Customer\Model\ResourceModel\Address\Attribute\Source\CountryWithWebsites;
use Magento\Eav\Model\Entity\Type;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\DataProvider\EavValidationRules;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 *
 * Validate attributeMetadata contains correct values in meta data array
 */
class AttributeMetadataResolverTest extends TestCase
{
    /**
     * @var CountryWithWebsites|MockObject
     */
    private $countryWithWebsiteSource;

    /**
     * @var EavValidationRules|MockObject
     */
    private $eavValidationRules;

    /**
     * @var FileUploaderDataResolver|MockObject
     */
    private $fileUploaderDataResolver;

    /**
     * @var ShareConfig|MockObject
     */
    private $shareConfig;

    /**
     * @var GroupManagement|MockObject
     */
    private $groupManagement;

    /**
     * @var ContextInterface|MockObject
     */
    private $context;

    /**
     * @var AttributeMetadataResolver
     */
    private $model;

    /**
     * @var Attribute|MockObject
     */
    private $attribute;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->countryWithWebsiteSource = $this->getMockBuilder(CountryWithWebsites::class)
            ->onlyMethods(['getAllOptions'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->eavValidationRules = $this->getMockBuilder(EavValidationRules::class)
            ->onlyMethods(['build'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->fileUploaderDataResolver = $this->getMockBuilder(FileUploaderDataResolver::class)
            ->onlyMethods(['overrideFileUploaderMetadata'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->context =  $this->getMockBuilder(ContextInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->shareConfig =  $this->getMockBuilder(ShareConfig::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->groupManagement =  $this->getMockBuilder(GroupManagement::class)
            ->onlyMethods(['getDefaultGroup'])
            ->addMethods(['getId'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->attribute = $this->getMockBuilder(Attribute::class)
            ->onlyMethods(
                [
                    'usesSource',
                    'getDataUsingMethod',
                    'getAttributeCode',
                    'getFrontendInput',
                    'getSource',
                    'setDataUsingMethod'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new AttributeMetadataResolver(
            $this->countryWithWebsiteSource,
            $this->eavValidationRules,
            $this->fileUploaderDataResolver,
            $this->context,
            $this->shareConfig,
            $this->groupManagement
        );
    }

    /**
     * Test to get meta data of the customer or customer address attribute.
     *
     * @return void
     */
    public function testGetAttributesMetaHasDefaultAttributeValue(): void
    {
        $rules = [
            'required-entry' => true
        ];
        $defaultGroupId = '3';
        $allowToShowHiddenAttributes = false;
        $usesSource = false;
        $entityType = $this->getMockBuilder(Type::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->attribute->expects($this->once())
            ->method('usesSource')
            ->willReturn($usesSource);
        $this->attribute->expects($this->once())
            ->method('getAttributeCode')
            ->willReturn('group_id');
        $this->groupManagement->expects($this->once())
            ->method('getDefaultGroup')
            ->willReturnSelf();
        $this->groupManagement->expects($this->once())
            ->method('getId')
            ->willReturn($defaultGroupId);
        $this->attribute
            ->method('getDataUsingMethod')
            ->withConsecutive([], [], [], [], [], [], ['default_value'])
            ->willReturnOnConsecutiveCalls(null, null, null, null, null, null, $defaultGroupId);
        $this->attribute->expects($this->once())
            ->method('setDataUsingMethod')
            ->willReturnSelf();
        $this->eavValidationRules->expects($this->once())
            ->method('build')
            ->with($this->attribute)
            ->willReturn($rules);
        $this->fileUploaderDataResolver->expects($this->once())
            ->method('overrideFileUploaderMetadata')
            ->with($entityType, $this->attribute)
            ->willReturnSelf();

        $meta = $this->model->getAttributesMeta($this->attribute, $entityType, $allowToShowHiddenAttributes);
        $this->assertArrayHasKey('default', $meta['arguments']['data']['config']);
        $this->assertEquals($defaultGroupId, $meta['arguments']['data']['config']['default']);
    }
}
