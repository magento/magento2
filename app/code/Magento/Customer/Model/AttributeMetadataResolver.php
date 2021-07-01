<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model;

use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Config\Share as ShareConfig;
use Magento\Customer\Model\ResourceModel\Address\Attribute\Source\CountryWithWebsites;
use Magento\Customer\Model\ResourceModel\Address\Attribute\Source\PrefixSuffixWithWebsites;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Model\Entity\Type;
use Magento\Framework\Exception\LocalizedException;
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
     * @var PrefixSuffixWithWebsites
     */
    private $prefixSuffixWithWebsites;

    /**
     * @param CountryWithWebsites $countryWithWebsiteSource
     * @param EavValidationRules $eavValidationRules
     * @param FileUploaderDataResolver $fileUploaderDataResolver
     * @param ContextInterface $context
     * @param ShareConfig $shareConfig
     * @param GroupManagement $groupManagement
     * @param PrefixSuffixWithWebsites $prefixSuffixWithWebsites
     */
    public function __construct(
        CountryWithWebsites $countryWithWebsiteSource,
        EavValidationRules $eavValidationRules,
        FileUploaderDataResolver $fileUploaderDataResolver,
        ContextInterface $context,
        ShareConfig $shareConfig,
        GroupManagement $groupManagement,
        PrefixSuffixWithWebsites $prefixSuffixWithWebsites
    ) {
        $this->countryWithWebsiteSource = $countryWithWebsiteSource;
        $this->eavValidationRules = $eavValidationRules;
        $this->fileUploaderDataResolver = $fileUploaderDataResolver;
        $this->context = $context;
        $this->shareConfig = $shareConfig;
        $this->groupManagement = $groupManagement;
        $this->prefixSuffixWithWebsites = $prefixSuffixWithWebsites;
    }

    /**
     * Get meta data of the customer or customer address attribute
     *
     * @param AbstractAttribute $attribute
     * @param Type $entityType
     * @param bool $allowToShowHiddenAttributes
     * @return array
     * @throws LocalizedException
     */
    public function getAttributesMeta(
        AbstractAttribute $attribute,
        Type $entityType,
        bool $allowToShowHiddenAttributes
    ): array {
        $meta = $this->modifyBooleanAttributeMeta($attribute);
        $this->modifyGroupAttributeMeta($attribute);
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
                $meta['arguments']['data']['config']['formElement'] = self::$formElement[$value] ?? $value;
            }
        }

        if ($attribute->usesSource()) {
            if ($attribute->getAttributeCode() === AddressInterface::COUNTRY_ID) {
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

        $this->modifyPrefixSuffixMeta(
            $attribute,
            $meta['arguments']['data']['config']
        );

        return $meta;
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
     * @return void
     */
    private function modifyGroupAttributeMeta(AttributeInterface $attribute): void
    {
        if ($attribute->getAttributeCode() === 'group_id') {
            $defaultGroup = $this->groupManagement->getDefaultGroup();
            $defaultGroupId = !empty($defaultGroup) ? $defaultGroup->getId() : null;
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

        if (isset($meta[AddressInterface::PREFIX]) && !$this->shareConfig->isGlobalScope()) {
            $meta[AddressInterface::PREFIX]['arguments']['data']['config']['imports'] = [
                'update' => 'customer_form.customer_form_data_source:data.customer.website_id:value'
            ];

            $meta[AddressInterface::PREFIX]['arguments']['data']['config']['filterBy'] = [
                'target' => 'customer_form.customer_form_data_source:data.customer.website_id',
                'field' => 'website_ids'
            ];
        }

        if (isset($meta[AddressInterface::SUFFIX]) && !$this->shareConfig->isGlobalScope()) {
            $meta[AddressInterface::SUFFIX]['arguments']['data']['config']['imports'] = [
                'update' => 'customer_form.customer_form_data_source:data.customer.website_id:value'
            ];

            $meta[AddressInterface::SUFFIX]['arguments']['data']['config']['filterBy'] = [
                'target' => 'customer_form.customer_form_data_source:data.customer.website_id',
                'field' => 'website_ids'
            ];
        }
    }

    /**
     * Change prefix and suffix to input/select with correct options and required or not if configured
     *
     * @param AttributeInterface $attribute
     * @param array $meta
     */
    private function modifyPrefixSuffixMeta(AttributeInterface $attribute, &$meta): void
    {
        $attributeCode = $attribute->getAttributeCode();
        if (!in_array($attributeCode, [AddressInterface::PREFIX, AddressInterface::SUFFIX], false)) {
            return;
        }

        $meta['requiredPerWebsite'] = $this->prefixSuffixWithWebsites->getIsRequired($attributeCode);
        $meta['customEntry'] = $attributeCode . '_input';

        $options = $this->prefixSuffixWithWebsites->getAllOptions($attributeCode);

        if (count($options) > 0) {
            $meta['options'] = $options;
        }
    }
}
