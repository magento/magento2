<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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
            if (isset($elements[$attribute->getAttributeCode()]['label'])) {
                $label = $elements[$attribute->getAttributeCode()]['label'];
                $elements[$attribute->getAttributeCode()]['label'] = __($label);
            }
        }

        // The following code is a workaround for custom address attributes
        if (isset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
            ['payment']['children']
        )) {
            if (!isset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                ['payment']['children']['payments-list']['children'])) {
                $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                ['payment']['children']['payments-list']['children'] = [];
            }

            $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
            ['payment']['children']['payments-list']['children'] =
                array_merge_recursive(
                    $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                    ['payment']['children']['payments-list']['children'],
                    $this->processPaymentConfiguration(
                        $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                        ['payment']['children']['renders']['children'],
                        $elements
                    )
                );
        }

        if (isset($jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
            ['children']['shippingAddress']['children']['shipping-address-fieldset']['children']
        )) {
            $fields = $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
            ['children']['shippingAddress']['children']['shipping-address-fieldset']['children'];
            $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
            ['children']['shippingAddress']['children']['shipping-address-fieldset']['children'] = $this->merger->merge(
                $elements,
                'checkoutProvider',
                'shippingAddress',
                $fields
            );
        }
        return $jsLayout;
    }

    /**
     * Inject billing address component into every payment component
     *
     * @param array $configuration list of payment components
     * @param array $elements attributes that must be displayed in address form
     * @return array
     */
    private function processPaymentConfiguration(array &$configuration, array $elements)
    {
        $output = [];
        foreach ($configuration as $paymentGroup => $groupConfig) {
            foreach ($groupConfig['methods'] as $paymentCode => $paymentComponent) {
                if (empty($paymentComponent['isBillingAddressRequired'])) {
                    continue;
                }
                $output[$paymentCode . '-form'] = [
                    'component' => 'Magento_Checkout/js/view/billing-address',
                    'displayArea' => 'billing-address-form-' . $paymentCode,
                    'provider' => 'checkoutProvider',
                    'deps' => 'checkoutProvider',
                    'dataScopePrefix' => 'billingAddress' . $paymentCode,
                    'sortOrder' => 1,
                    'children' => [
                        'form-fields' => [
                            'component' => 'uiComponent',
                            'displayArea' => 'additional-fieldsets',
                            'children' => $this->merger->merge(
                                $elements,
                                'checkoutProvider',
                                'billingAddress' . $paymentCode,
                                [
                                    'country_id' => [
                                        'sortOrder' => 115,
                                    ],
                                    'region' => [
                                        'visible' => false,
                                    ],
                                    'region_id' => [
                                        'component' => 'Magento_Ui/js/form/element/region',
                                        'config' => [
                                            'template' => 'ui/form/field',
                                            'elementTmpl' => 'ui/form/element/select',
                                            'customEntry' => 'billingAddress' . $paymentCode . '.region',
                                        ],
                                        'validation' => [
                                            'validate-select' => true,
                                        ],
                                        'filterBy' => [
                                            'target' => '${ $.provider }:${ $.parentScope }.country_id',
                                            'field' => 'country_id',
                                        ],
                                    ],
                                    'postcode' => [
                                        'component' => 'Magento_Ui/js/form/element/post-code',
                                        'validation' => [
                                            'required-entry' => true,
                                        ],
                                    ],
                                    'company' => [
                                        'validation' => [
                                            'min_text_length' => 0,
                                        ],
                                    ],
                                    'fax' => [
                                        'validation' => [
                                            'min_text_length' => 0,
                                        ],
                                    ],
                                    'telephone' => [
                                        'config' => [
                                            'tooltip' => [
                                                'description' => 'For delivery questions.',
                                            ],
                                        ],
                                    ],
                                ]
                            ),
                        ],
                    ],
                ];
            }
            unset($configuration[$paymentGroup]['methods']);
        }

        return $output;
    }
}
