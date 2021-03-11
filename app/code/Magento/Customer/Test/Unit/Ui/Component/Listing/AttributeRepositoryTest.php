<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Ui\Component\Listing;

use Magento\Customer\Ui\Component\Listing\AttributeRepository;

/**
 * Test AttributeRepository Class
 */
class AttributeRepositoryTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Customer\Api\CustomerMetadataManagementInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $customerMetadataManagement;

    /** @var \Magento\Customer\Api\AddressMetadataManagementInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $addressMetadataManagement;

    /** @var \Magento\Customer\Api\CustomerMetadataInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $customerMetadata;

    /** @var \Magento\Customer\Api\AddressMetadataInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $addressMetadata;

    /** @var \Magento\Customer\Api\Data\AttributeMetadataInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $attribute;

    /** @var \Magento\Customer\Api\Data\OptionInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $option;

    /** @var \Magento\Customer\Model\Indexer\Attribute\Filter|\PHPUnit\Framework\MockObject\MockObject */
    protected $attributeFilter;

    /** @var AttributeRepository */
    protected $component;

    protected function setUp(): void
    {
        $this->customerMetadataManagement = $this->getMockForAbstractClass(
            \Magento\Customer\Api\CustomerMetadataManagementInterface::class,
            [],
            '',
            false
        );
        $this->addressMetadataManagement = $this->getMockForAbstractClass(
            \Magento\Customer\Api\AddressMetadataManagementInterface::class,
            [],
            '',
            false
        );
        $this->customerMetadata = $this->getMockForAbstractClass(
            \Magento\Customer\Api\CustomerMetadataInterface::class,
            [],
            '',
            false
        );
        $this->addressMetadata = $this->getMockForAbstractClass(
            \Magento\Customer\Api\AddressMetadataInterface::class,
            [],
            '',
            false
        );
        $this->attribute = $this->getMockForAbstractClass(
            \Magento\Customer\Api\Data\AttributeMetadataInterface::class,
            [],
            '',
            false
        );
        $this->option = $this->createMock(\Magento\Customer\Api\Data\OptionInterface::class);

        $this->attributeFilter = $this->createMock(\Magento\Customer\Model\Indexer\Attribute\Filter::class);

        $this->component = new AttributeRepository(
            $this->customerMetadataManagement,
            $this->addressMetadataManagement,
            $this->customerMetadata,
            $this->addressMetadata,
            $this->attributeFilter
        );
    }

    public function testGetList()
    {
        $attributeCode = 'attribute_code';
        $billingPrefix = 'billing_';

        $this->customerMetadata->expects($this->once())
            ->method('getAllAttributesMetadata')
            ->willReturn([]);
        $this->addressMetadata->expects($this->once())
            ->method('getAllAttributesMetadata')
            ->willReturn([$this->attribute]);
        $this->addressMetadataManagement->expects($this->once())
            ->method('canBeFilterableInGrid')
            ->with($this->attribute)
            ->willReturn(true);
        $this->addressMetadataManagement->expects($this->once())
            ->method('canBeSearchableInGrid')
            ->with($this->attribute)
            ->willReturn(true);
        $this->attribute->expects($this->atLeastOnce())
            ->method('getAttributeCode')
            ->willReturn($attributeCode);
        $this->attribute->expects($this->once())
            ->method('getFrontendInput')
            ->willReturn('frontend-input');
        $this->attribute->expects($this->once())
            ->method('getFrontendLabel')
            ->willReturn('frontend-label');
        $this->attribute->expects($this->once())
            ->method('getBackendType')
            ->willReturn('backend-type');
        $this->attribute->expects($this->once())
            ->method('getOptions')
            ->willReturn([$this->option]);
        $this->attribute->expects($this->once())
            ->method('getIsUsedInGrid')
            ->willReturn(true);
        $this->attribute->expects($this->once())
            ->method('getIsVisibleInGrid')
            ->willReturn(true);
        $this->attribute->expects($this->once())
            ->method('getValidationRules')
            ->willReturn([]);
        $this->attribute->expects($this->once())
            ->method('isRequired')
            ->willReturn(false);
        $this->option->expects($this->once())
            ->method('getLabel')
            ->willReturn('Label');
        $this->option->expects($this->once())
            ->method('getValue')
            ->willReturn('Value');
        $this->attributeFilter->expects($this->once())
            ->method('filter')
            ->willReturnArgument(0);

        $this->assertEquals(
            [
                $billingPrefix . $attributeCode => [
                    'attribute_code' => 'billing_attribute_code',
                    'frontend_input' => 'frontend-input',
                    'frontend_label' => 'frontend-label',
                    'backend_type' => 'backend-type',
                    'options' => [
                        [
                            'label' => 'Label',
                            'value' => 'Value',
                            '__disableTmpl' => true
                        ]
                    ],
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => true,
                    'is_filterable_in_grid' => true,
                    'is_searchable_in_grid' => true,
                    'validation_rules' => [],
                    'required'=> false,
                    'entity_type_code' => 'customer_address',
                ]
            ],
            $this->component->getList()
        );
    }
}
