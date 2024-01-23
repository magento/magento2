<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Block\Checkout;

use Magento\Checkout\Helper\Data;
use Magento\Customer\Model\AttributeMetadataDataProvider;
use Magento\Customer\Model\Options;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Shipping\Model\Config;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\Form\AttributeMapper;

/**
 * Checkout Layout Processor
 */
class LayoutProcessor implements LayoutProcessorInterface
{
    /**
     * @var AttributeMetadataDataProvider
     */
    private $attributeMetadataDataProvider;

    /**
     * @var AttributeMapper
     */
    protected $attributeMapper;

    /**
     * @var AttributeMerger
     */
    protected $merger;

    /**
     * @var Options
     */
    private $options;

    /**
     * @var Data
     */
    private $checkoutDataHelper;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Config
     */
    private $shippingConfig;

    /**
     * @param AttributeMetadataDataProvider $attributeMetadataDataProvider
     * @param AttributeMapper $attributeMapper
     * @param AttributeMerger $merger
     * @param Options $options
     * @param Data $checkoutDataHelper
     * @param Config $shippingConfig
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        AttributeMetadataDataProvider $attributeMetadataDataProvider,
        AttributeMapper $attributeMapper,
        AttributeMerger $merger,
        Options $options,
        Data $checkoutDataHelper,
        Config $shippingConfig,
        StoreManagerInterface $storeManager
    ) {
        $this->attributeMetadataDataProvider = $attributeMetadataDataProvider;
        $this->attributeMapper = $attributeMapper;
        $this->merger = $merger;
        $this->options = $options;
        $this->checkoutDataHelper = $checkoutDataHelper;
        $this->shippingConfig = $shippingConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * Get address attributes.
     *
     * @return array
     */
    private function getAddressAttributes()
    {
        /** @var AttributeInterface[] $attributes */
        $attributes = $this->attributeMetadataDataProvider->loadAttributesCollection(
            'customer_address',
            'customer_register_address'
        );

        $elements = [];
        foreach ($attributes as $attribute) {
            $code = $attribute->getAttributeCode();
            if ($attribute->getIsUserDefined()) {
                continue;
            }
            $elements[$code] = $this->attributeMapper->map($attribute);
            if (isset($elements[$code]['label'])) {
                $label = $elements[$code]['label'];
                $elements[$code]['label'] = __($label);
            }
        }
        return $elements;
    }

    /**
     * Convert elements(like prefix and suffix) from inputs to selects when necessary
     *
     * @param array $elements address attributes
     * @param array $attributesToConvert fields and their callbacks
     * @return array
     */
    private function convertElementsToSelect($elements, $attributesToConvert)
    {
        $codes = array_keys($attributesToConvert);
        foreach (array_keys($elements) as $code) {
            if (!in_array($code, $codes)) {
                continue;
            }
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            $options = call_user_func($attributesToConvert[$code]);
            if (!is_array($options)) {
                continue;
            }
            $elements[$code]['dataType'] = 'select';
            $elements[$code]['formElement'] = 'select';

            foreach ($options as $value) {
                $elements[$code]['options'][] = [
                    'value' => $value,
                    'label' => $value,
                ];
            }
        }

        return $elements;
    }

    /**
     * Process js Layout of block
     *
     * @param array $jsLayout
     * @return array
     */
    public function process($jsLayout)
    {
        $attributesToConvert = [
            'prefix' => [$this->options, 'getNamePrefixOptions'],
            'suffix' => [$this->options, 'getNameSuffixOptions'],
        ];

        $elements = $this->getAddressAttributes();
        $elements = $this->convertElementsToSelect($elements, $attributesToConvert);
        // The following code is a workaround for custom address attributes
        if (isset(
            $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']
            ['children']
        )) {
            $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
            ['payment']['children'] = $this->processPaymentChildrenComponents(
                $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                ['payment']['children'],
                $elements
            );
        }

        if (isset(
            $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
            ['step-config']['children']['shipping-rates-validation']['children']
        )) {
            $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
            ['step-config']['children']['shipping-rates-validation']['children'] =
                $this->processShippingChildrenComponents(
                    $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
                    ['step-config']['children']['shipping-rates-validation']['children']
                );
        }

        if (isset(
            $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
            ['shippingAddress']['children']['shipping-address-fieldset']['children']
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
     * Process shipping configuration to exclude inactive carriers.
     *
     * @param array $shippingRatesLayout
     * @return array
     */
    private function processShippingChildrenComponents($shippingRatesLayout)
    {
        $activeCarriers = $this->shippingConfig->getActiveCarriers(
            $this->storeManager->getStore()->getId()
        );
        foreach (array_keys($shippingRatesLayout) as $carrierName) {
            $carrierKey = str_replace('-rates-validation', '', $carrierName);
            if (!array_key_exists($carrierKey, $activeCarriers)) {
                unset($shippingRatesLayout[$carrierName]);
            }
        }
        return $shippingRatesLayout;
    }

    /**
     * Appends billing address form component to payment layout
     *
     * @param array $paymentLayout
     * @param array $elements
     * @return array
     */
    private function processPaymentChildrenComponents(array $paymentLayout, array $elements)
    {
        if (!isset($paymentLayout['payments-list']['children'])) {
            $paymentLayout['payments-list']['children'] = [];
        }

        if (!isset($paymentLayout['afterMethods']['children'])) {
            $paymentLayout['afterMethods']['children'] = [];
        }

        // The if billing address should be displayed on Payment method or page
        if ($this->checkoutDataHelper->isDisplayBillingOnPaymentMethodAvailable()) {
            $paymentLayout['payments-list']['children'] =
                array_merge_recursive(
                    $paymentLayout['payments-list']['children'],
                    $this->processPaymentConfiguration(
                        $paymentLayout['renders']['children'],
                        $elements
                    )
                );
        } else {
            $component['billing-address-form'] = $this->getBillingAddressComponent('shared', $elements);
            $paymentLayout['afterMethods']['children'] =
                array_merge_recursive(
                    $component,
                    $paymentLayout['afterMethods']['children']
                );
        }

        return $paymentLayout;
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
                $output[$paymentCode . '-form'] = $this->getBillingAddressComponent($paymentCode, $elements);
            }
            unset($configuration[$paymentGroup]['methods']);
        }

        return $output;
    }

    /**
     * Gets billing address component details
     *
     * @param string $paymentCode
     * @param array $elements
     * @return array
     */
    private function getBillingAddressComponent($paymentCode, $elements)
    {
        return [
            'component' => 'Magento_Checkout/js/view/billing-address',
            'displayArea' => 'billing-address-form-' . $paymentCode,
            'provider' => 'checkoutProvider',
            'deps' => 'checkoutProvider',
            'dataScopePrefix' => 'billingAddress' . $paymentCode,
            'billingAddressListProvider' => '${$.name}.billingAddressList',
            '__disableTmpl' => ['billingAddressListProvider' => false],
            'sortOrder' => 1,
            'children' => [
                'billingAddressList' => [
                    'component' => 'Magento_Checkout/js/view/billing-address/list',
                    'displayArea' => 'billing-address-list',
                    'template' => 'Magento_Checkout/billing-address/list'
                ],
                'form-fields' => [
                    'component' => 'uiComponent',
                    'displayArea' => 'additional-fieldsets',
                    'children' => $this->merger->merge(
                        $elements,
                        'checkoutProvider',
                        'billingAddress' . $paymentCode,
                        [
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
                                    'required-entry' => true,
                                ],
                                'filterBy' => [
                                    'target' => '${ $.provider }:${ $.parentScope }.country_id',
                                    '__disableTmpl' => ['target' => false],
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
                                        'description' => __('For delivery questions.'),
                                    ],
                                ],
                            ],
                        ]
                    ),
                ],
            ],
        ];
    }
}
