<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model;

use Magento\Customer\Model\Attribute;
use Magento\Customer\Model\AttributeMetadataResolver;
use Magento\Customer\Model\AttributeWebsiteRequired;
use Magento\Customer\Model\Config\Share as ShareConfig;
use Magento\Customer\Model\FileUploaderDataResolver;
use Magento\Customer\Model\GroupManagement;
use Magento\Customer\Model\ResourceModel\Address\Attribute\Source\CountryWithWebsites;
use Magento\Eav\Model\Entity\Type;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\DataProvider\EavValidationRules;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;

/**
 *
 * Validate attributeMetadata contains correct values in meta data array
 */
class AttributeMetadataResolverTest extends TestCase
{
    use MockCreationTrait;

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
     * @var AttributeWebsiteRequired|MockObject
     */
    private $attributeWebsiteRequired;

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
        $this->countryWithWebsiteSource = $this->createPartialMock(
            CountryWithWebsites::class,
            ['getAllOptions']
        );
        $this->eavValidationRules = $this->createPartialMock(
            EavValidationRules::class,
            ['build']
        );
        $this->fileUploaderDataResolver = $this->createPartialMock(
            FileUploaderDataResolver::class,
            ['overrideFileUploaderMetadata']
        );
        $this->context =  $this->createMock(ContextInterface::class);
        $this->shareConfig =  $this->createMock(ShareConfig::class);
        $this->groupManagement =  $this->createPartialMockWithReflection(
            GroupManagement::class,
            [
                'getDefaultGroup',
                'getId'
            ]
        );
        $this->attributeWebsiteRequired = $this->createMock(AttributeWebsiteRequired::class);
        $this->attribute = $this->createPartialMock(
            Attribute::class,
            [
                'usesSource',
                'getDataUsingMethod',
                'getAttributeCode',
                'getFrontendInput',
                'getSource',
                'setDataUsingMethod'
            ]
        );

        $this->model = new AttributeMetadataResolver(
            $this->countryWithWebsiteSource,
            $this->eavValidationRules,
            $this->fileUploaderDataResolver,
            $this->context,
            $this->shareConfig,
            $this->groupManagement, // @phpstan-ignore argument.type
            $this->attributeWebsiteRequired
        );
    }

    /**
     * Test to get meta data of the customer or customer address attribute.
     *
     * @return void
     * @SuppressWarnings(PHPMD)
     */
    public function testGetAttributesMetaHasDefaultAttributeValue(): void
    {
        $rules = [
            'required-entry' => true
        ];
        $defaultGroupId = '3';
        $allowToShowHiddenAttributes = false;
        $usesSource = false;
        $entityType = $this->createMock(Type::class);
        $this->attribute->expects($this->once()) // @phpstan-ignore method.notFound
            ->method('usesSource')
            ->willReturn($usesSource);
        $this->attribute->expects($this->once()) // @phpstan-ignore method.notFound
            ->method('getAttributeCode')
            ->willReturn('group_id');
        $this->groupManagement->expects($this->once()) // @phpstan-ignore method.notFound
            ->method('getDefaultGroup')
            ->willReturnSelf();
        $this->groupManagement->expects($this->once()) // @phpstan-ignore method.notFound
            ->method('getId')
            ->willReturn($defaultGroupId);
        $this->attribute // @phpstan-ignore method.notFound
            ->method('getDataUsingMethod')
            ->willReturnCallback(function ($arg1) use ($defaultGroupId) {
                if (empty($arg1)) {
                    return null;
                } elseif ($arg1 == 'default_value') {
                    return $defaultGroupId;
                }
            });
        $this->attribute->expects($this->once()) // @phpstan-ignore method.notFound
            ->method('setDataUsingMethod')
            ->willReturnSelf();
        $this->eavValidationRules->expects($this->once()) // @phpstan-ignore method.notFound
            ->method('build')
            ->with($this->attribute)
            ->willReturn($rules);
        $this->fileUploaderDataResolver->expects($this->once()) // @phpstan-ignore method.notFound
            ->method('overrideFileUploaderMetadata')
            ->with($entityType, $this->attribute)
            ->willReturnSelf();

        $meta = $this->model->getAttributesMeta($this->attribute, $entityType, $allowToShowHiddenAttributes);
        $this->assertArrayHasKey('default', $meta['arguments']['data']['config']);
        $this->assertEquals($defaultGroupId, $meta['arguments']['data']['config']['default']);
    }
}
