<?php
declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model;

use Magento\Config\Model\Config\Source\Nooptreq;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Helper\Address as AddressHelper;
use Magento\Customer\Model\Config\Share as ShareConfig;
use Magento\Customer\Model\Options as CustomerOptions;
use Magento\Customer\Model\ResourceModel\Address\Attribute\Source\CountryWithWebsites;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Model\Entity\Type;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Store\Model\StoreManagerInterface;
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
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CustomerOptions
     */
    private $customerOptions;

    /**
     * @var AddressHelper
     */
    private $addressHelper;

    /**
     * @param CountryWithWebsites $countryWithWebsiteSource
     * @param EavValidationRules $eavValidationRules
     * @param FileUploaderDataResolver $fileUploaderDataResolver
     * @param ContextInterface $context
     * @param ShareConfig $shareConfig
     * @param GroupManagement|null $groupManagement
     * @param StoreManagerInterface|null $storeManager
     * @param Options|null $customerOptions
     * @param AddressHelper|null $addressHelper
     */
    public function __construct(
        CountryWithWebsites $countryWithWebsiteSource,
        EavValidationRules $eavValidationRules,
        FileUploaderDataResolver $fileUploaderDataResolver,
        ContextInterface $context,
        ShareConfig $shareConfig,
        ?GroupManagement $groupManagement = null,
        StoreManagerInterface $storeManager = null,
        CustomerOptions $customerOptions = null,
        AddressHelper $addressHelper = null
    ) {
        $this->countryWithWebsiteSource = $countryWithWebsiteSource;
        $this->eavValidationRules = $eavValidationRules;
        $this->fileUploaderDataResolver = $fileUploaderDataResolver;
        $this->context = $context;
        $this->shareConfig = $shareConfig;
        $this->groupManagement = $groupManagement ?? ObjectManager::getInstance()->get(GroupManagement::class);
        $this->storeManager = $storeManager ?: ObjectManager::getInstance()->get(StoreManagerInterface::class);
        $this->customerOptions = $customerOptions ?: ObjectManager::getInstance()->get(CustomerOptions::class);
        $this->addressHelper = $addressHelper ?: ObjectManager::getInstance()->get(AddressHelper::class);
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
            $meta['arguments']['data']['config'],
            $meta[CustomerInterface::WEBSITE_ID] ?? null
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
    }

    /**
     * Change prefix and suffix to select with correct options if configured
     *
     * @param AttributeInterface $attribute
     * @param $meta
     * @param int|null $websiteId
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    private function modifyPrefixSuffixMeta(AttributeInterface $attribute, &$meta, $websiteId): void
    {
        $attributeCode = $attribute->getAttributeCode();
        if (!in_array($attributeCode, [AddressInterface::PREFIX, AddressInterface::SUFFIX], false)) {
            return;
        }

        $storeId = null;

        if ($websiteId) {
            $groupId = $this->storeManager->getWebsite($websiteId)->getDefaultGroupId();
            $storeId = $this->storeManager->getGroup($groupId)->getDefaultStoreId();
        } elseif ($defaultStoreView = $this->storeManager->getDefaultStoreView()) {
            $storeId = $defaultStoreView->getId();
        }

        $isRequired = $this->addressHelper->getConfig(
            $attributeCode . '_show',
            $storeId
        ) === Nooptreq::VALUE_REQUIRED;

        if (!$isRequired) {
            $meta['required'] = 0;
            unset($meta['validation']);
        }

        $options = $attributeCode === AddressInterface::PREFIX ?
            $this->customerOptions->getNamePrefixOptions($storeId) :
            $this->customerOptions->getNameSuffixOptions($storeId);

        if ($options !== false) {
            $meta['dataType'] = 'select';
            $meta['formElement'] = 'select';
            $meta['options'] = $this->mapPrefixSuffixOptions($options);
        }
    }

    /**
     * Map options array to valid source for UI select
     *
     * @param array $options
     * @return array
     */
    private function mapPrefixSuffixOptions(array $options): array
    {
        $result = [];

        foreach ($options as $value => $label) {
            $result[] = ['label' => $label, 'value' => trim($value)];
        }

        return $result;
    }
}
