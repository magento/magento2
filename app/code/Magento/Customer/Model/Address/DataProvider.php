<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Model\Address;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\ResourceModel\Address\CollectionFactory;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Type;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Ui\DataProvider\EavValidationRules;
use Magento\Ui\Component\Form\Field;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Customer\Model\ResourceModel\Address\Attribute\Source\CountryWithWebsites;
use Magento\Customer\Model\Attribute;
use Magento\Framework\App\ObjectManager;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Customer\Model\Address;
use Magento\Customer\Model\FileProcessor;
use Magento\Customer\Model\FileProcessorFactory;

/**
 * Dataprovider for customer address grid.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * Maximum file size allowed for file_uploader UI component
     */
    const MAX_FILE_SIZE = 2097152;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Address\Collection
     */
    protected $collection;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var array
     */
    private $loadedData;

    /**
     * EAV attribute properties to fetch from meta storage
     * @var array
     */
    private $metaProperties = [
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
    private $formElement = [
        'text' => 'input',
        'hidden' => 'input',
        'boolean' => 'checkbox',
    ];

    /**
     * @var EavValidationRules
     */
    private $eavValidationRules;

    /**
     * @var CountryWithWebsites
     */
    private $countryWithWebsiteSource;

    /**
     * Allow to manage attributes, even they are hidden on storefront
     *
     * @var bool
     */
    private $allowToShowHiddenAttributes;

    /*
     * @var ContextInterface
     */
    private $context;

    /**
     * File types allowed for file_uploader UI component
     *
     * @var array
     */
    private $fileUploaderTypes = [
        'image',
        'file',
    ];

    /**
     * @var \Magento\Customer\Model\Config\Share
     */
    private $shareConfig;

    /**
     * @var FileProcessorFactory
     */
    private $fileProcessorFactory;

    /**
     * @var array
     */
    private $bannedInputTypes = ['media_image'];

    /**
     * @var array
     */
    private $attributesToEliminate = [
        'region',
        'vat_is_valid',
        'vat_request_date',
        'vat_request_id',
        'vat_request_success'
    ];

    /**
     * DataProvider constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $addressCollectionFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param Config $eavConfig
     * @param EavValidationRules $eavValidationRules
     * @param ContextInterface $context
     * @param FileProcessorFactory $fileProcessorFactory
     * @param \Magento\Customer\Model\Config\Share $shareConfig
     * @param CountryWithWebsites $countryWithWebsites
     * @param array $meta
     * @param array $data
     * @param bool $allowToShowHiddenAttributes
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $addressCollectionFactory,
        CustomerRepositoryInterface $customerRepository,
        Config $eavConfig,
        EavValidationRules $eavValidationRules,
        ContextInterface $context,
        FileProcessorFactory $fileProcessorFactory,
        \Magento\Customer\Model\Config\Share $shareConfig,
        CountryWithWebsites $countryWithWebsites,
        array $meta = [],
        array $data = [],
        $allowToShowHiddenAttributes = true
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $addressCollectionFactory->create();
        $this->collection->addAttributeToSelect('*');
        $this->customerRepository = $customerRepository;
        $this->eavValidationRules = $eavValidationRules;
        $this->allowToShowHiddenAttributes = $allowToShowHiddenAttributes;
        $this->context = $context;
        $this->fileProcessorFactory = $fileProcessorFactory;
        $this->countryWithWebsiteSource = $countryWithWebsites;
        $this->shareConfig = $shareConfig;
        $this->meta['general']['children'] = $this->getAttributesMeta(
            $eavConfig->getEntityType('customer_address')
        );
    }

    /**
     * Get Addresses data and process customer default billing & shipping addresses
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getData()
    {
        if (null !== $this->loadedData) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        /** @var Address $item */
        foreach ($items as $item) {
            $addressId = $item->getEntityId();
            $item->load($addressId);
            $this->loadedData[$addressId] = $item->getData();
            $customerId = $this->loadedData[$addressId]['parent_id'];
            /** @var \Magento\Customer\Model\Customer $customer */
            $customer = $this->customerRepository->getById($customerId);
            $defaultBilling = $customer->getDefaultBilling();
            $defaultShipping = $customer->getDefaultShipping();
            $this->prepareAddressData($addressId, $this->loadedData, $defaultBilling, $defaultShipping);
            $this->overrideFileUploaderData($item, $this->loadedData[$addressId]);
        }

        if (null === $this->loadedData) {
            $this->loadedData[''] = $this->getDefaultData();
        }

        return $this->loadedData;
    }

    /**
     * Prepare address data
     *
     * @param int $addressId
     * @param array $addresses
     * @param string|null $defaultBilling
     * @param string|null $defaultShipping
     * @return void
     */
    private function prepareAddressData($addressId, array &$addresses, $defaultBilling, $defaultShipping)
    {
        if (null !== $defaultBilling && $addressId == $defaultBilling) {
            $addresses[$addressId]['default_billing'] = '1';
        }
        if (null !== $defaultShipping && $addressId == $defaultShipping) {
            $addresses[$addressId]['default_shipping'] = '1';
        }
        if (null !== $addresses[$addressId]['street'] && !is_array($addresses[$addressId]['street'])) {
            $addresses[$addressId]['street'] = explode("\n", $addresses[$addressId]['street']);
        }
    }

    /**
     * Get default customer data for adding new address
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @return array
     */
    private function getDefaultData()
    {
        $parentId = $this->context->getRequestParam('parent_id');
        $customer = $this->customerRepository->getById($parentId);
        $data = [
            'parent_id' => $parentId,
            'firstname' => $customer->getFirstname(),
            'lastname' => $customer->getLastname()
        ];

        return $data;
    }

    /**
     * Override file uploader UI component data
     *
     * Overrides data for attributes with frontend_input equal to 'image' or 'file'.
     *
     * @param Address $entity
     * @param array $entityData
     * @return void
     */
    private function overrideFileUploaderData($entity, array &$entityData)
    {
        $attributes = $entity->getAttributes();
        foreach ($attributes as $attribute) {
            /** @var Attribute $attribute */
            if (in_array($attribute->getFrontendInput(), $this->fileUploaderTypes)) {
                $entityData[$attribute->getAttributeCode()] = $this->getFileUploaderData(
                    $entity->getEntityType(),
                    $attribute,
                    $entityData
                );
            }
        }
    }

    /**
     * Get attributes meta
     *
     * @param Type $entityType
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getAttributesMeta(Type $entityType): array
    {
        $meta = [];
        $attributes = $entityType->getAttributeCollection();
        /* @var AbstractAttribute $attribute */
        foreach ($attributes as $attribute) {
            $this->processFrontendInput($attribute, $meta);

            $code = $attribute->getAttributeCode();

            if (in_array($attribute->getFrontendInput(), $this->bannedInputTypes)) {
                continue;
            }
            if (in_array($attribute->getAttributeCode(), $this->attributesToEliminate)) {
                continue;
            }

            // use getDataUsingMethod, since some getters are defined and apply additional processing of returning value
            foreach ($this->metaProperties as $metaName => $origName) {
                $value = $attribute->getDataUsingMethod($origName);
                $meta[$code]['arguments']['data']['config'][$metaName] = ($metaName === 'label') ? __($value) : $value;
                if ('frontend_input' === $origName) {
                    $meta[$code]['arguments']['data']['config']['formElement'] = $this->formElement[$value] ?? $value;
                }
            }

            if ($attribute->usesSource()) {
                if ($code == AddressInterface::COUNTRY_ID) {
                    $meta[$code]['arguments']['data']['config']['options'] = $this->countryWithWebsiteSource
                        ->getAllOptions();
                } else {
                    $meta[$code]['arguments']['data']['config']['options'] = $attribute->getSource()->getAllOptions();
                }
            }

            $rules = $this->eavValidationRules->build($attribute, $meta[$code]['arguments']['data']['config']);
            if (!empty($rules)) {
                $meta[$code]['arguments']['data']['config']['validation'] = $rules;
            }

            $meta[$code]['arguments']['data']['config']['componentType'] = Field::NAME;
            $meta[$code]['arguments']['data']['config']['visible'] = $this->canShowAttribute($attribute);

            $this->overrideFileUploaderMetadata($entityType, $attribute, $meta[$code]['arguments']['data']['config']);
        }

        $this->processWebsiteMeta($meta);
        return $meta;
    }

    /**
     * Process attributes by frontend input type
     *
     * @param AttributeInterface $attribute
     * @param array $meta
     * @return void
     */
    private function processFrontendInput(AttributeInterface $attribute, array &$meta)
    {
        $code = $attribute->getAttributeCode();
        if ($attribute->getFrontendInput() === 'boolean') {
            $meta[$code]['arguments']['data']['config']['prefer'] = 'toggle';
            $meta[$code]['arguments']['data']['config']['valueMap'] = [
                'true' => '1',
                'false' => '0',
            ];
        }
    }

    /**
     * Detect can we show attribute on specific form or not
     *
     * @param AbstractAttribute $customerAttribute
     * @return bool
     */
    private function canShowAttribute(AbstractAttribute $customerAttribute): bool
    {
        $userDefined = (bool) $customerAttribute->getIsUserDefined();
        if (!$userDefined) {
            return $customerAttribute->getIsVisible();
        }

        $canShowOnForm = $this->canShowAttributeInForm($customerAttribute);

        return ($this->allowToShowHiddenAttributes && $canShowOnForm) ||
            (!$this->allowToShowHiddenAttributes && $canShowOnForm && $customerAttribute->getIsVisible());
    }

    /**
     * Check whether the specific attribute can be shown in form: customer registration, customer edit, etc...
     *
     * @param Attribute $customerAttribute
     * @return bool
     */
    private function canShowAttributeInForm(AbstractAttribute $customerAttribute): bool
    {
        $isRegistration = $this->context->getRequestParam($this->getRequestFieldName()) === null;

        if ($customerAttribute->getEntityType()->getEntityTypeCode() === 'customer') {
            return is_array($customerAttribute->getUsedInForms()) &&
                (
                    (in_array('customer_account_create', $customerAttribute->getUsedInForms()) && $isRegistration) ||
                    (in_array('customer_account_edit', $customerAttribute->getUsedInForms()) && !$isRegistration)
                );
        }
        return is_array($customerAttribute->getUsedInForms()) &&
            in_array('customer_address_edit', $customerAttribute->getUsedInForms());
    }

    /**
     * Override file uploader UI component metadata
     *
     * Overrides metadata for attributes with frontend_input equal to 'image' or 'file'.
     *
     * @param Type $entityType
     * @param AbstractAttribute $attribute
     * @param array $config
     * @return void
     */
    private function overrideFileUploaderMetadata(
        Type $entityType,
        AbstractAttribute $attribute,
        array &$config
    ) {
        if (in_array($attribute->getFrontendInput(), $this->fileUploaderTypes)) {
            $maxFileSize = self::MAX_FILE_SIZE;

            if (isset($config['validation']['max_file_size'])) {
                $maxFileSize = (int)$config['validation']['max_file_size'];
            }

            $allowedExtensions = [];

            if (isset($config['validation']['file_extensions'])) {
                $allowedExtensions = explode(',', $config['validation']['file_extensions']);
                array_walk($allowedExtensions, function (&$value) {
                    $value = strtolower(trim($value));
                });
            }

            $allowedExtensions = implode(' ', $allowedExtensions);

            $entityTypeCode = $entityType->getEntityTypeCode();
            $url = $this->getFileUploadUrl($entityTypeCode);

            $config = [
                'formElement' => 'fileUploader',
                'componentType' => 'fileUploader',
                'maxFileSize' => $maxFileSize,
                'allowedExtensions' => $allowedExtensions,
                'uploaderConfig' => [
                    'url' => $url,
                ],
                'label' => $this->getMetadataValue($config, 'label'),
                'sortOrder' => $this->getMetadataValue($config, 'sortOrder'),
                'required' => $this->getMetadataValue($config, 'required'),
                'visible' => $this->getMetadataValue($config, 'visible'),
                'validation' => $this->getMetadataValue($config, 'validation'),
            ];
        }
    }

    /**
     * Add global scope parameter and filter options to website meta
     *
     * @param array $meta
     * @return void
     */
    private function processWebsiteMeta(&$meta)
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
     * Retrieve metadata value
     *
     * @param array $config
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    private function getMetadataValue($config, $name, $default = null)
    {
        return $config[$name] ?? $default;
    }

    /**
     * Retrieve URL to file upload
     *
     * @param string $entityTypeCode
     * @return string
     */
    private function getFileUploadUrl($entityTypeCode): string
    {
        switch ($entityTypeCode) {
            case CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER:
                $url = 'customer/file/customer_upload';
                break;

            case AddressMetadataInterface::ENTITY_TYPE_ADDRESS:
                $url = 'customer/file/address_upload';
                break;

            default:
                $url = '';
                break;
        }
        return $url;
    }

    /**
     * Retrieve array of values required by file uploader UI component
     *
     * @param Type $entityType
     * @param Attribute $attribute
     * @param array $customerData
     * @return array
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function getFileUploaderData(
        Type $entityType,
        Attribute $attribute,
        array $customerData
    ): array {
        $attributeCode = $attribute->getAttributeCode();

        $file = $customerData[$attributeCode] ?? '';

        /** @var FileProcessor $fileProcessor */
        $fileProcessor = $this->fileProcessorFactory->create([
            'entityTypeCode' => $entityType->getEntityTypeCode(),
        ]);

        if (!empty($file)
            && $fileProcessor->isExist($file)
        ) {
            $stat = $fileProcessor->getStat($file);
            $viewUrl = $fileProcessor->getViewUrl($file, $attribute->getFrontendInput());

            return [
                [
                    'file' => $file,
                    'size' => null !== $stat ? $stat['size'] : 0,
                    'url' => $viewUrl ?? '',
                    'name' => basename($file),
                    'type' => $fileProcessor->getMimeType($file),
                ],
            ];
        }

        return [];
    }
}
