<?php
declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model;

use Magento\Customer\Model\ResourceModel\Address\Attribute\Source\CountryWithWebsites;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Ui\DataProvider\EavValidationRules;
use Magento\Ui\Component\Form\Field;
use Magento\Eav\Model\Entity\Type;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Config\Share as ShareConfig;

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
     * @param CountryWithWebsites $countryWithWebsiteSource
     * @param EavValidationRules $eavValidationRules
     * @param \Magento\Customer\Model\FileUploaderDataResolver $fileUploaderDataResolver
     * @param ContextInterface $context
     * @param ShareConfig $shareConfig
     */
    public function __construct(
        CountryWithWebsites $countryWithWebsiteSource,
        EavValidationRules $eavValidationRules,
        fileUploaderDataResolver $fileUploaderDataResolver,
        ContextInterface $context,
        ShareConfig $shareConfig
    ) {
        $this->countryWithWebsiteSource = $countryWithWebsiteSource;
        $this->eavValidationRules = $eavValidationRules;
        $this->fileUploaderDataResolver = $fileUploaderDataResolver;
        $this->context = $context;
        $this->shareConfig = $shareConfig;
    }

    /**
     * Get meta data of the customer or customer address attribute
     *
     * @param AbstractAttribute $attribute
     * @param Type $entityType
     * @param bool $allowToShowHiddenAttributes
     * @param string $requestFieldName
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAttributesMeta(
        AbstractAttribute $attribute,
        Type $entityType,
        bool $allowToShowHiddenAttributes,
        string $requestFieldName
    ): array {
        $meta = $this->modifyBooleanAttributeMeta($attribute);
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
            $requestFieldName,
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
     * Detect can we show attribute on specific form or not
     *
     * @param AbstractAttribute $customerAttribute
     * @param string $requestFieldName
     * @param bool $allowToShowHiddenAttributes
     * @return bool
     */
    private function canShowAttribute(
        AbstractAttribute $customerAttribute,
        string $requestFieldName,
        bool $allowToShowHiddenAttributes
    ) {
        $userDefined = (bool)$customerAttribute->getIsUserDefined();
        if (!$userDefined) {
            return $customerAttribute->getIsVisible();
        }

        $canShowOnForm = $this->canShowAttributeInForm($customerAttribute, $requestFieldName);

        return ($allowToShowHiddenAttributes && $canShowOnForm) ||
            (!$allowToShowHiddenAttributes && $canShowOnForm && $customerAttribute->getIsVisible());
    }

    /**
     * Check whether the specific attribute can be shown in form: customer registration, customer edit, etc...
     *
     * @param AbstractAttribute $customerAttribute
     * @param string $requestFieldName
     * @return bool
     */
    private function canShowAttributeInForm(AbstractAttribute $customerAttribute, string $requestFieldName): bool
    {
        $isRegistration = $this->context->getRequestParam($requestFieldName) === null;

        if ($customerAttribute->getEntityType()->getEntityTypeCode() === 'customer') {
            return \is_array($customerAttribute->getUsedInForms()) &&
                (
                    (\in_array('customer_account_create', $customerAttribute->getUsedInForms(), true)
                        && $isRegistration) ||
                    (\in_array('customer_account_edit', $customerAttribute->getUsedInForms(), true)
                        && !$isRegistration)
                );
        }
        return \is_array($customerAttribute->getUsedInForms()) &&
            \in_array('customer_address_edit', $customerAttribute->getUsedInForms(), true);
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
}
