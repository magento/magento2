<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Quote;

use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Customer\Model\Session;
use Magento\Customer\Api\CustomerRepositoryInterface as CustomerRepository;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Eav\Model\Entity\Type as EntityType;
use Magento\Customer\Helper\Address as AddressHelper;
use Magento\Ui\DataProvider\EavValidationRul;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface;

/**
 * TODO implement all methods declared in the interface. Now only getMeta() has proper implementation.
 */
class AddressDataProvider implements DataProviderInterface
{
    /**
     * @var EavConfig
     */
    private $eavConfig;

    /**
     * @var EavValidationRul
     */
    private $eavValidationRule;

    /**
     * @var array
     */
    private $meta = [];

    /**
     * Provider configuration data
     *
     * @var array
     */
    private $data = [];

    /**
     * @var AddressHelper
     */
    private $addressHelper;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var CustomerRepository
     */
    private $customerRepository;

    /**
     * @var \Magento\Customer\Api\Data\CustomerInterface
     */
    private $customer;

    /**
     * @var \Magento\Directory\Helper\Data
     */
    private $directoryHelper;

    /**
     * Form element mapping
     *
     * @var array
     */
    private $formElementMap = [
        'text' => 'input',
        'hidden' => 'input',
        'boolean' => 'checkbox',
    ];

    /**
     * EAV attribute properties to fetch from meta storage
     * @var array
     */
    private $metaPropertiesMap = [
        'dataType' => 'frontend_input',
        'visible' => 'is_visible',
        'required' => 'is_required',
        'label' => 'frontend_label',
        'sortOrder' => 'sort_order',
        'notice' => 'note',
        'default' => 'default_value',
        'size' => 'scope_multiline_count'
    ];

    /**
     * @param EavValidationRul $eavValidationRule
     * @param EavConfig $eavConfig
     * @param AddressHelper $addressHelper
     * @param Session $customerSession
     * @param CustomerRepository $customerRepository
     * @param DirectoryHelper $directoryHelper
     * @param array $meta
     * @param array $data
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        EavValidationRul $eavValidationRule,
        EavConfig $eavConfig,
        AddressHelper $addressHelper,
        Session $customerSession,
        CustomerRepository $customerRepository,
        DirectoryHelper $directoryHelper,
        array $meta = [],
        array $data = []
    ) {
        $this->eavValidationRule = $eavValidationRule;
        $this->eavConfig = $eavConfig;
        $this->meta = $meta;
        $this->meta['address']['fields'] = $this->getAttributesMeta(
            $this->eavConfig->getEntityType('customer_address')
        );
        $this->data = $data;
        $this->addressHelper = $addressHelper;
        $this->customerSession = $customerSession;
        $this->customerRepository = $customerRepository;
        $this->directoryHelper = $directoryHelper;
    }

    /**
     * Get meta data
     *
     * @return array
     */
    public function getMeta()
    {
        return $this->meta;
    }

    /**
     * Get fields meta info
     *
     * @param string $fieldSetName
     * @return array
     */
    public function getFieldsMetaInfo($fieldSetName)
    {
        return isset($this->meta[$fieldSetName]['fields']) ? $this->meta[$fieldSetName]['fields'] : [];
    }

    /**
     * Get field meta info
     *
     * @param string $fieldSetName
     * @param string $fieldName
     * @return array
     */
    public function getFieldMetaInfo($fieldSetName, $fieldName)
    {
        return isset($this->meta[$fieldSetName]['fields'][$fieldName])
            ? $this->meta[$fieldSetName]['fields'][$fieldName]
            : [];
    }

    /**
     * Get attributes meta
     *
     * @param EntityType $entityType
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getAttributesMeta(EntityType $entityType)
    {
        $meta = [];
        $attributes = $entityType->getAttributeCollection();
        /* @var \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute */
        foreach ($attributes as $attribute) {
            $code = $attribute->getAttributeCode();
            // use getDataUsingMethod, since some getters are defined and apply additional processing of returning value
            foreach ($this->metaPropertiesMap as $metaName => $originalName) {
                $value = $attribute->getDataUsingMethod($originalName);
                $meta[$code][$metaName] = $value;
                if ('frontend_input' === $originalName) {
                    $meta[$code]['formElement'] = isset($this->formElementMap[$value])
                        ? $this->formElementMap[$value]
                        : $value;
                }
                if ($attribute->usesSource()) {
                    $meta[$code]['options'] = $attribute->getSource()->getAllOptions();
                }
            }

            $rules = $this->eavValidationRule->build($attribute, $meta[$code]);
            if (!empty($rules)) {
                $meta[$code]['validation'] = $rules;
            }
        }
        return $meta;
    }

    /**
     * Get config data
     *
     * @return mixed
     */
    public function getConfigData()
    {
        return [];
    }

    /**
     * Set data
     *
     * @param mixed $config
     * @return void
     */
    public function setConfigData($config)
    {
        // do nothing
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        return [];
    }

    /**
     * Get field name in request
     *
     * @return string
     */
    public function getRequestFieldName()
    {
        return null;
    }

    /**
     * Get primary field name
     *
     * @return string
     */
    public function getPrimaryFieldName()
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function addFilter($field, $condition = null)
    {
        // do nothing
    }

    /**
     * Add field to select
     *
     * @param string|array $field
     * @param string|null $alias
     * @return void
     */
    public function addField($field, $alias = null)
    {
        // do nothing
    }

    /**
     * self::setOrder() alias
     *
     * @param string $field
     * @param string $direction
     * @return void
     */
    public function addOrder($field, $direction)
    {
        // do nothing
    }

    /**
     * Set Query limit
     *
     * @param int $offset
     * @param int $size
     * @return void
     */
    public function setLimit($offset, $size)
    {
        // do nothing
    }

    /**
     * Removes field from select
     *
     * @param string|null $field
     * @param bool $isAlias Alias identifier
     * @return void
     */
    public function removeField($field, $isAlias = false)
    {
        // do nothing
    }

    /**
     * Removes all fields from select
     *
     * @return void
     */
    public function removeAllFields()
    {
        // do nothing
    }

    /**
     * Retrieve count of loaded items
     *
     * @return int
     */
    public function count()
    {
        return 0;
    }

    /**
     * Retrieve additional address fields for given provider
     *
     * @param string $providerName name of the storage container used by UI component
     * @param string $dataScopePrefix
     * @param array $fields
     * @return array
     */
    public function getAdditionalAddressFields($providerName, $dataScopePrefix, array $fields = [])
    {
        foreach ($this->getFieldsMetaInfo('address') as $attributeCode => $attributeConfig) {
            $additionalConfig = isset($fields[$attributeCode]) ? $fields[$attributeCode] : [];
            if (!$this->isFieldVisible($attributeCode, $attributeConfig, $additionalConfig)) {
                continue;
            }
            $fields[$attributeCode] = $this->getFieldConfig(
                $attributeCode,
                $attributeConfig,
                $additionalConfig,
                $providerName,
                $dataScopePrefix
            );
        }
        return $fields;
    }

    /**
     * Retrieve UI field configuration for given attribute
     *
     * @param string $attributeCode
     * @param array $attributeConfig
     * @param array $additionalConfig field configuration provided via layout XML
     * @param string $providerName name of the storage container used by UI component
     * @param string $dataScopePrefix
     * @return array
     */
    protected function getFieldConfig(
        $attributeCode,
        array $attributeConfig,
        array $additionalConfig,
        $providerName,
        $dataScopePrefix
    ) {
        // street attribute is unique in terms of configuration, so it has its own configuration builder
        if ($attributeCode == 'street') {
            return $this->getStreetFieldConfig($attributeCode, $attributeConfig, $providerName, $dataScopePrefix);
        }

        $uiComponent = $attributeConfig['formElement'] == 'select'
            ? 'Magento_Ui/js/form/element/select'
            : 'Magento_Ui/js/form/element/abstract';
        $elementTemplate = $attributeConfig['formElement'] == 'select'
            ? 'ui/form/element/select'
            : 'ui/form/element/input';

        $element = [
            'component' => isset($additionalConfig['component']) ? $additionalConfig['component'] : $uiComponent,
            'config' => [
                // customScope is used to group elements within a single form (e.g. they can be validated separately)
                'customScope' => $dataScopePrefix,
                'customEntry' => isset($additionalConfig['config']['customEntry'])
                    ? $additionalConfig['config']['customEntry']
                    : null,
                'template' => 'ui/form/field',
                'elementTmpl' => isset($additionalConfig['config']['elementTmpl'])
                    ? $additionalConfig['config']['elementTmpl']
                    : $elementTemplate,
            ],
            'dataScope' => $dataScopePrefix . '.' . $attributeCode,
            'label' => $attributeConfig['label'],
            'provider' => $providerName,
            'sortOrder' => $attributeConfig['sortOrder'],
            'validation' => $this->mergeConfigurationNode('validation', $additionalConfig, $attributeConfig),
            'options' => isset($attributeConfig['options']) ? $attributeConfig['options'] : [],
            'filterBy' => isset($additionalConfig['filterBy']) ? $additionalConfig['filterBy'] : null,
            'customEntry' => isset($additionalConfig['customEntry']) ? $additionalConfig['customEntry'] : null,
            'visible' => isset($additionalConfig['visible']) ? $additionalConfig['visible'] : true,
        ];

        $defaultValue = $this->getDefaultValue($attributeCode);
        if (null !== $defaultValue) {
            $element['default'] = $defaultValue;
        }
        return $element;
    }

    /**
     * Merge two configuration nodes recursively
     *
     * @param string $nodeName
     * @param array $mainSource
     * @param array $additionalSource
     * @return array
     */
    protected function mergeConfigurationNode($nodeName, array $mainSource, array $additionalSource)
    {
        $mainData = isset($mainSource[$nodeName]) ? $mainSource[$nodeName] : [];
        $additionalData = isset($additionalSource[$nodeName]) ? $additionalSource[$nodeName] : [];
        return array_replace_recursive($additionalData, $mainData);
    }

    /**
     * Check if address attribute is visible on frontend
     *
     * @param string $attributeCode
     * @param array $attributeConfig
     * @param array $additionalConfig field configuration provided via layout XML
     * @return bool
     */
    protected function isFieldVisible($attributeCode, array $attributeConfig, array $additionalConfig = [])
    {
        // TODO move this logic to separate model so it can be customized
        if ($attributeConfig['visible'] == false
            || (isset($additionalConfig['visible']) && $additionalConfig['visible'] == false)
        ) {
            return false;
        }
        if ($attributeCode == 'vat_id' && !$this->addressHelper->isVatAttributeVisible()) {
            return false;
        }
        return true;
    }

    /**
     * Retrieve field configuration for street address attribute
     *
     * @param string $attributeCode
     * @param array $attributeConfig
     * @param string $providerName name of the storage container used by UI component
     * @param string $dataScopePrefix
     * @return array
     */
    protected function getStreetFieldConfig($attributeCode, array $attributeConfig, $providerName, $dataScopePrefix)
    {
        $streetLines = [];
        for ($lineIndex = 0; $lineIndex < $this->addressHelper->getStreetLines(); $lineIndex++) {
            $isFirstLine = $lineIndex === 0;
            $streetLines[] = [
                'component' => 'Magento_Ui/js/form/element/abstract',
                'config' => [
                    // customScope is used to group elements within a single form e.g. they can be validated separately
                    'customScope' => $dataScopePrefix,
                    'template' => 'ui/form/field',
                    'elementTmpl' => 'ui/form/element/input',
                ],
                'dataScope' => $lineIndex,
                'provider' => $providerName,
                'validation' => $isFirstLine ? ['required-entry' => true] : [],
            ];
        }
        return [
            'component' => 'Magento_Ui/js/form/components/group',
            'label' => __('Address'),
            'required' => true,
            'dataScope' => $dataScopePrefix . '.' . $attributeCode,
            'provider' => $providerName,
            'sortOrder' => $attributeConfig['sortOrder'],
            'type' => 'group',
            'config' => [
                'template' => 'ui/group/group',
            ],
            'children' => $streetLines,
        ];
    }

    /**
     * @param string $attributeCode
     * @return null|string
     */
    protected function getDefaultValue($attributeCode)
    {
        switch ($attributeCode) {
            case 'firstname':
                if ($this->getCustomer()) {
                    return $this->getCustomer()->getFirstname();
                }
                break;
            case 'lastname':
                if ($this->getCustomer()) {
                    return $this->getCustomer()->getLastname();
                }
                break;
            case 'country_id':
                return $this->directoryHelper->getDefaultCountry();
        }
        return null;
    }

    /**
     * @return \Magento\Customer\Api\Data\CustomerInterface|null
     */
    protected function getCustomer()
    {
        if (!$this->customer) {
            if ($this->customerSession->isLoggedIn()) {
                $this->customer = $this->customerRepository->getById($this->customerSession->getCustomerId());
            } else {
                return null;
            }
        }
        return $this->customer;
    }
}
