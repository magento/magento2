<?php
declare(strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Customer\Model;

use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Config\Share as ShareConfig;
use Magento\Customer\Model\ResourceModel\Address\Attribute\Source\CountryWithWebsites;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Model\Entity\Type;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\DataProvider\EavValidationRules;

/**
 * Class to build meta data of the customer or customer address attribute
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AttributeMetadataResolver
{
    /**
     * EAV attribute properties to fetch from meta storage
     * @var array
     */
    private static $metaProperties = [
        'dataType' => 'frontend_input',
        'visible' => 'is_visible',
        'required' => 'is_required',
        'label' => 'frontend_label',
        'sortOrder' => 'sort_order',
        'notice' => 'note',
        'default' => 'default_value',
        'size' => 'multiline_count',
        'attributeId' => 'attribute_id',
    ];

    /**
     * Form element mapping
     *
     * @var array
     */
    private static $formElement = [
        'text' => 'input',
        'hidden' => 'input',
        'boolean' => 'checkbox',
    ];

    /**
     * @var CountryWithWebsites
     */
    private $countryWithWebsiteSource;

    /**
     * @var EavValidationRules
     */
    private $eavValidationRules;

    /**
     * @var FileUploaderDataResolver
     */
    private $fileUploaderDataResolver;

    /**
     * @var ContextInterface
     */
    private $context;

    /**
     * @var ShareConfig
     */
    private $shareConfig;

    /**
     * @var GroupManagement
     */
    private $groupManagement;

    /**
     * @var AttributeWebsiteRequired|null
     */
    private ?AttributeWebsiteRequired $attributeWebsiteRequired;

    /**
     * @var Options
     */
    private ?Options $options;

    /**
     * @param CountryWithWebsites $countryWithWebsiteSource
     * @param EavValidationRules $eavValidationRules
     * @param FileUploaderDataResolver $fileUploaderDataResolver
     * @param ContextInterface $context
     * @param ShareConfig $shareConfig
     * @param GroupManagement|null $groupManagement
     * @param AttributeWebsiteRequired|null $attributeWebsiteRequired
     * @param Options|null $options
     */
    public function __construct(
        CountryWithWebsites $countryWithWebsiteSource,
        EavValidationRules $eavValidationRules,
        FileUploaderDataResolver $fileUploaderDataResolver,
        ContextInterface $context,
        ShareConfig $shareConfig,
        ?GroupManagement $groupManagement = null,
        ?AttributeWebsiteRequired $attributeWebsiteRequired = null,
        ?Options $options = null
    ) {
        $this->countryWithWebsiteSource = $countryWithWebsiteSource;
        $this->eavValidationRules = $eavValidationRules;
        $this->fileUploaderDataResolver = $fileUploaderDataResolver;
        $this->context = $context;
        $this->shareConfig = $shareConfig;
        $this->groupManagement = $groupManagement ?? ObjectManager::getInstance()->get(GroupManagement::class);
        $this->attributeWebsiteRequired = $attributeWebsiteRequired ??
            ObjectManager::getInstance()->get(AttributeWebsiteRequired::class);
        $this->options = $options;
    }

    /**
     * Get meta data of the customer or customer address attribute
     *
     * @param AbstractAttribute $attribute
     * @param Type $entityType
     * @param bool $allowToShowHiddenAttributes
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAttributesMeta(
        AbstractAttribute $attribute,
        Type $entityType,
        bool $allowToShowHiddenAttributes
    ): array {
        $meta = $this->modifyBooleanAttributeMeta($attribute);
        $attributeCode = $attribute->getAttributeCode();
        $this->modifyGroupAttributeMeta($attribute, $attributeCode);
        // use getDataUsingMethod, since some getters are defined and apply additional processing of returning value
        foreach (self::$metaProperties as $metaName => $origName) {
            $value = $attribute->getDataUsingMethod($origName);
            if ($metaName === 'label') {
                $meta['arguments']['data']['config'][$metaName] = __($value);
                $meta['arguments']['data']['config']['__disableTmpl'] = [$metaName => true];
            } else {
                $meta['arguments']['data']['config'][$metaName] = $value;
            }
            if ('frontend_input' === $origName) {
                $value = $value ?? '';
                $meta['arguments']['data']['config']['formElement'] = self::$formElement[$value] ?? $value;
            }
        }

        if ($attribute->usesSource()) {
            if ($attributeCode === AddressInterface::COUNTRY_ID) {
                $meta['arguments']['data']['config']['options'] = $this->countryWithWebsiteSource
                    ->getAllOptions();
            } else {
                $options = $attribute->getSource()->getAllOptions();
                array_walk(
                    $options,
                    function (&$item) {
                        $item['__disableTmpl'] = ['label' => true];
                    }
                );
                $meta['arguments']['data']['config']['options'] = $options;
            }
        }

        $this->applyNameOptionsMeta($attributeCode, $meta);

        $rules = $this->eavValidationRules->build($attribute, $meta['arguments']['data']['config']);
        if (!empty($rules)) {
            $meta['arguments']['data']['config']['validation'] = $rules;
        }

        $meta['arguments']['data']['config']['componentType'] = Field::NAME;
        $meta['arguments']['data']['config']['visible'] = $this->canShowAttribute(
            $attribute,
            $allowToShowHiddenAttributes
        );

        $this->fileUploaderDataResolver->overrideFileUploaderMetadata(
            $entityType,
            $attribute,
            $meta['arguments']['data']['config']
        );
        return $meta;
    }

    /**
     * Render name prefix and suffix as a dropdown when the options are configured
     *
     * @param string|null $attributeCode
     * @param array $meta
     * @return void
     */
    private function applyNameOptionsMeta(?string $attributeCode, array &$meta): void
    {
        if ($attributeCode !== 'prefix' && $attributeCode !== 'suffix') {
            return;
        }
        $this->options ??= ObjectManager::getInstance()->get(Options::class);
        $values = $attributeCode === 'prefix'
            ? $this->options->getNamePrefixOptions()
            : $this->options->getNameSuffixOptions();
        if (empty($values)) {
            return;
        }

        $options = [];
        foreach ($values as $value) {
            $options[] = [
                'value' => trim((string)$value),
                'label' => (string)$value,
                '__disableTmpl' => ['label' => true]
            ];
        }
        $config = &$meta['arguments']['data']['config'];
        $config['formElement'] = 'select';
        $config['component'] = 'Magento_Ui/js/form/element/select';
        $config['elementTmpl'] = 'ui/form/element/select';
        $config['options'] = $options;
    }

    /**
     * Detect can we show attribute on specific form or not
     *
     * @param AbstractAttribute $customerAttribute
     * @param bool $allowToShowHiddenAttributes
     * @return bool
     */
    private function canShowAttribute(
        AbstractAttribute $customerAttribute,
        bool $allowToShowHiddenAttributes
    ) {
        return $allowToShowHiddenAttributes && (bool) $customerAttribute->getIsUserDefined()
            ? true
            : (bool) $customerAttribute->getIsVisible();
    }

    /**
     * Modify boolean attribute meta data
     *
     * @param AttributeInterface $attribute
     * @return array
     */
    private function modifyBooleanAttributeMeta(AttributeInterface $attribute): array
    {
        $meta = [];
        if ($attribute->getFrontendInput() === 'boolean') {
            $meta['arguments']['data']['config']['prefer'] = 'toggle';
            $meta['arguments']['data']['config']['valueMap'] = [
                'true' => '1',
                'false' => '0',
            ];
        }

        return $meta;
    }

    /**
     * Modify group attribute meta data
     *
     * @param AttributeInterface $attribute
     * @param string|null $attributeCode
     * @return void
     */
    private function modifyGroupAttributeMeta(AttributeInterface $attribute, ?string $attributeCode): void
    {
        if ($attributeCode === 'group_id') {
            $defaultGroup = $this->groupManagement->getDefaultGroup();
            $defaultGroupId = $defaultGroup->getId();
            $attribute->setDataUsingMethod(self::$metaProperties['default'], $defaultGroupId);
        }
    }

    /**
     * Add global scope parameter and filter options to website meta
     *
     * @param array $meta
     * @return void
     */
    public function processWebsiteMeta(&$meta): void
    {
        if (isset($meta[CustomerInterface::WEBSITE_ID]) && $this->shareConfig->isGlobalScope()) {
            $meta[CustomerInterface::WEBSITE_ID]['arguments']['data']['config']['isGlobalScope'] = 1;
        }

        if (isset($meta[AddressInterface::COUNTRY_ID]) && !$this->shareConfig->isGlobalScope()) {
            $meta[AddressInterface::COUNTRY_ID]['arguments']['data']['config']['filterBy'] = [
                'target' => 'customer_form.customer_form_data_source:data.customer.website_id',
                'field' => 'website_ids'
            ];
        }

        if (isset($meta[CustomerInterface::WEBSITE_ID])) {
            $this->processWebsiteIsRequired($meta);
        }
    }

    /**
     * Adds attribute 'required' validation according to the scope.
     *
     * @param array $meta
     * @return void
     */
    private function processWebsiteIsRequired(&$meta): void
    {
        $attributeIds = array_values(
            array_map(
                function ($attribute) {
                    return $attribute['arguments']['data']['config']['attributeId'];
                },
                array_filter(
                    $meta,
                    function ($attribute) {
                        return isset($attribute['arguments']['data']['config']['attributeId']);
                    }
                )
            )
        );
        $websiteIds = array_values(
            array_map(
                function ($option) {
                    return (int)$option['value'];
                },
                $meta[CustomerInterface::WEBSITE_ID]['arguments']['data']['config']['options']
            )
        );

        $websiteRequired = $this->attributeWebsiteRequired->get($attributeIds, $websiteIds);
        array_walk(
            $meta,
            function (&$attribute) use ($websiteRequired) {
                $id = $attribute['arguments']['data']['config']['attributeId'];
                unset($attribute['arguments']['data']['config']['attributeId']);
                if (!empty($websiteRequired[$id])) {
                    $attribute['arguments']['data']['config']
                        ['validation']['required-entry-website'] = $websiteRequired[$id];
                }
            }
        );
    }
}
