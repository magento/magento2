<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Ui\Component\Listing;

use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Api\AddressMetadataManagementInterface;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\CustomerMetadataManagementInterface;
use Magento\Customer\Api\Data\AttributeMetadataInterface;
use Magento\Customer\Api\Data\OptionInterface;
use Magento\Customer\Model\AttributeMetadataDataProvider;
use Magento\Customer\Model\Indexer\Attribute\Filter;
use Magento\Customer\Ui\Component\Listing\AttributeRepository;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test AttributeRepository Class
 */
class AttributeRepositoryTest extends TestCase
{
    /** @var CustomerMetadataManagementInterface|MockObject */
    protected $customerMetadataManagement;

    /** @var AddressMetadataManagementInterface|MockObject */
    protected $addressMetadataManagement;

    /** @var CustomerMetadataInterface|MockObject */
    protected $customerMetadata;

    /** @var AddressMetadataInterface|MockObject */
    protected $addressMetadata;

    /** @var AttributeMetadataInterface|MockObject */
    protected $attribute;

    /** @var OptionInterface|MockObject */
    protected $option;

    /** @var Filter|MockObject */
    protected $attributeFilter;

    /** @var AttributeRepository */
    protected $component;

    /**
     * @var AttributeMetadataDataProvider|MockObject
     */
    private $attributeMetadataDataProvider;

    /**
     * @var AbstractAttribute|MockObject
     */
    private $attributeModel;

    protected function setUp(): void
    {
        $this->customerMetadataManagement = $this->getMockForAbstractClass(
            CustomerMetadataManagementInterface::class,
            [],
            '',
            false
        );
        $this->addressMetadataManagement = $this->getMockForAbstractClass(
            AddressMetadataManagementInterface::class,
            [],
            '',
            false
        );
        $this->customerMetadata = $this->getMockForAbstractClass(
            CustomerMetadataInterface::class,
            [],
            '',
            false
        );
        $this->addressMetadata = $this->getMockForAbstractClass(
            AddressMetadataInterface::class,
            [],
            '',
            false
        );
        $this->attribute = $this->getMockForAbstractClass(
            AttributeMetadataInterface::class,
            [],
            '',
            false
        );
        $this->option = $this->getMockForAbstractClass(OptionInterface::class);

        $this->attributeFilter = $this->createMock(Filter::class);

        $this->attributeMetadataDataProvider = $this->createMock(
            AttributeMetadataDataProvider::class
        );

        $this->attributeModel = $this->getMockBuilder(AbstractAttribute::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->component = new AttributeRepository(
            $this->customerMetadataManagement,
            $this->addressMetadataManagement,
            $this->customerMetadata,
            $this->addressMetadata,
            $this->attributeFilter,
            $this->attributeMetadataDataProvider
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

        $this->attributeModel->addData(['grid_filter_condition_type' => 1]);
        $this->attributeMetadataDataProvider->method('getAttribute')
            ->willReturn($this->attributeModel);

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
                    'grid_filter_condition_type' => 1
                ]
            ],
            $this->component->getList()
        );
    }
}
