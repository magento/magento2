<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Block\Checkout;

class LayoutProcessor implements \Magento\Checkout\Block\Checkout\LayoutProcessorInterface
{
    /**
     * @var \Magento\Customer\Model\AttributeMetadataDataProvider
     */
    private $attributeMetadataDataProvider;

    /**
     * @var \Magento\Ui\Component\Form\AttributeMapper
     */
    protected $attributeMapper;

    /**
     * @var AttributeMerger
     */
    protected $merger;

    /**
     * @param \Magento\Customer\Model\AttributeMetadataDataProvider $attributeMetadataDataProvider
     * @param \Magento\Ui\Component\Form\AttributeMapper $attributeMapper
     * @param AttributeMerger $merger
     */
    public function __construct(
        \Magento\Customer\Model\AttributeMetadataDataProvider $attributeMetadataDataProvider,
        \Magento\Ui\Component\Form\AttributeMapper $attributeMapper,
        AttributeMerger $merger
    ) {
        $this->attributeMetadataDataProvider = $attributeMetadataDataProvider;
        $this->attributeMapper = $attributeMapper;
        $this->merger = $merger;
    }

    /**
     * Process js Layout of block
     *
     * @param array $jsLayout
     * @return array
     */
    public function process($jsLayout)
    {
        /** @var \Magento\Eav\Api\Data\AttributeInterface[] $attributes */
        $attributes = $this->attributeMetadataDataProvider->loadAttributesCollection(
            'customer_address',
            'customer_register_address'
        );

        $elements = [];
        foreach ($attributes as $attribute) {
            if ($attribute->getIsUserDefined()) {
                continue;
            }
            $elements[$attribute->getAttributeCode()] = $this->attributeMapper->map($attribute);
        }

        // The following code is a workaround for custom address attributes
        if (isset($jsLayout['components']['checkout']['children']['steps']['children']['billingAddress']
            ['children']['billing-address-fieldset']['children']
        )) {
            $fields = $jsLayout['components']['checkout']['children']['steps']['children']['billingAddress']
            ['children']['billing-address-fieldset']['children'];
            $jsLayout['components']['checkout']['children']['steps']['children']['billingAddress']
            ['children']['billing-address-fieldset']['children'] = $this->merger->merge(
                $elements,
                'checkoutProvider',
                'billingAddress',
                $fields
            );
        }
        if (isset($jsLayout['components']['checkout']['children']['steps']['children']['shippingAddress']
            ['children']['shipping-address-fieldset']['children']
        )) {
            $fields = $jsLayout['components']['checkout']['children']['steps']['children']['shippingAddress']
            ['children']['shipping-address-fieldset']['children'];
            $jsLayout['components']['checkout']['children']['steps']['children']['shippingAddress']
            ['children']['shipping-address-fieldset']['children'] = $this->merger->merge(
                $elements,
                'checkoutProvider',
                'shippingAddress',
                $fields
            );
        }
        return $jsLayout;
    }
}
