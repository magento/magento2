<?php
declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Customer;

use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Address;
use Magento\Customer\Model\Attribute;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\FileProcessor;
use Magento\Customer\Model\FileProcessorFactory;
use Magento\Customer\Model\ResourceModel\Address\Attribute\Source\CountryWithWebsites;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Model\Entity\Type;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\DataProvider\EavValidationRules;

/**
 * Refactored version of Magento\Customer\Model\Customer\DataProvider with eliminated usage of addresses collection.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DataProviderWithDefaultAddresses extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * Maximum file size allowed for file_uploader UI component
     */
    const MAX_FILE_SIZE = 2097152;

    /**
     * @var array
     */
    private $loadedData;

    /**
     * @var CountryWithWebsites
     */
    private $countryWithWebsiteSource;

    /**
     * @var \Magento\Customer\Model\Config\Share
     */
    private $shareConfig;

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
     * @var SessionManagerInterface
     * @since 100.1.0
     */
    private $session;

    /**
     * @var FileProcessorFactory
     */
    private $fileProcessorFactory;

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
     * Customer fields that must be removed
     *
     * @var array
     */
    private $forbiddenCustomerFields = [
        'password_hash',
        'rp_token',
        'confirmation',
    ];

    /*
     * @var ContextInterface
     */
    private $context;

    /**
     * Allow to manage attributes, even they are hidden on storefront
     *
     * @var bool
     */
    private $allowToShowHiddenAttributes;

    /**
     * @var \Magento\Directory\Model\CountryFactory
     */
    private $countryFactory;

    /**
     * DataProviderWithDefaultAddresses constructor.
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param EavValidationRules $eavValidationRules
     * @param CustomerCollectionFactory $customerCollectionFactory
     * @param Config $eavConfig
     * @param FileProcessorFactory $fileProcessorFactory
     * @param ContextInterface $context
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     * @param SessionManagerInterface $session
     * @param \Magento\Customer\Model\Config\Share $shareConfig
     * @param CountryWithWebsites $countryWithWebsites
     * @param bool $allowToShowHiddenAttributes
     * @param array $meta
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        EavValidationRules $eavValidationRules,
        CustomerCollectionFactory $customerCollectionFactory,
        Config $eavConfig,
        FileProcessorFactory $fileProcessorFactory,
        ContextInterface $context,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Framework\Session\SessionManagerInterface $session,
        \Magento\Customer\Model\Config\Share $shareConfig,
        CountryWithWebsites $countryWithWebsites,
        $allowToShowHiddenAttributes = true,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->eavValidationRules = $eavValidationRules;
        $this->collection = $customerCollectionFactory->create();
        $this->collection->addAttributeToSelect('*');
        $this->fileProcessorFactory = $fileProcessorFactory;
        $this->context = $context ?: ObjectManager::getInstance()->get(ContextInterface::class);
        $this->allowToShowHiddenAttributes = $allowToShowHiddenAttributes;
        $this->session = $session;
        $this->countryWithWebsiteSource = $countryWithWebsites;
        $this->shareConfig = $shareConfig;
        $this->countryFactory = $countryFactory;
        $this->meta['customer']['children'] = $this->getAttributesMeta(
            $eavConfig->getEntityType('customer')
        );
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (null !== $this->loadedData) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        /** @var Customer $customer */
        foreach ($items as $customer) {
            $result['customer'] = $customer->getData();

            $this->overrideFileUploaderData($customer, $result['customer']);

            $result['customer'] = array_diff_key(
                $result['customer'],
                array_flip($this->forbiddenCustomerFields)
            );
            unset($result['address']);

            $result['default_billing_address'] = $this->prepareDefaultAddress(
                $customer->getDefaultBillingAddress()
            );
            $result['default_shipping_address'] = $this->prepareDefaultAddress(
                $customer->getDefaultShippingAddress()
            );
            $result['customer_id'] = $customer->getId();

            $this->loadedData[$customer->getId()] = $result;
        }

        $data = $this->session->getCustomerFormData();
        if (!empty($data)) {
            $customerId = $data['customer']['entity_id'] ?? null;
            $this->loadedData[$customerId] = $data;
            $this->session->unsCustomerFormData();
        }

        return $this->loadedData;
    }

    /**
     * Prepare default address data.
     *
     * @param Address|false $address
     * @return array
     */
    private function prepareDefaultAddress($address): array
    {
        $addressData = [];

        if (!empty($address)) {
            $addressData = $address->getData();
            if (isset($addressData['street']) && !\is_array($address['street'])) {
                $addressData['street'] = explode("\n", $addressData['street']);
            }
            $addressData['country'] = $this->countryFactory->create()
                ->loadByCode($addressData['country_id'])->getName();
        }

        return $addressData;
    }

    /**
     * Override file uploader UI component data
     *
     * Overrides data for attributes with frontend_input equal to 'image' or 'file'.
     *
     * @param Customer|Address $entity
     * @param array $entityData
     * @return void
     */
    private function overrideFileUploaderData($entity, array &$entityData)
    {
        $attributes = $entity->getAttributes();
        foreach ($attributes as $attribute) {
            /** @var Attribute $attribute */
            if (\in_array($attribute->getFrontendInput(), $this->fileUploaderTypes)) {
                $entityData[$attribute->getAttributeCode()] = $this->getFileUploaderData(
                    $entity->getEntityType(),
                    $attribute,
                    $entityData
                );
            }
        }
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
     * Check whether the specific attribute can be shown in form: customer registration, customer edit, etc...
     *
     * @param AbstractAttribute $customerAttribute
     * @return bool
     */
    private function canShowAttributeInForm(AbstractAttribute $customerAttribute)
    {
        $isRegistration = $this->context->getRequestParam($this->getRequestFieldName()) === null;

        if ($customerAttribute->getEntityType()->getEntityTypeCode() === 'customer') {
            return \is_array($customerAttribute->getUsedInForms()) &&
                (
                    (\in_array('customer_account_create', $customerAttribute->getUsedInForms()) && $isRegistration) ||
                    (\in_array('customer_account_edit', $customerAttribute->getUsedInForms()) && !$isRegistration)
                );
        }
        return \is_array($customerAttribute->getUsedInForms()) &&
            \in_array('customer_address_edit', $customerAttribute->getUsedInForms());
    }

    /**
     * Detect can we show attribute on specific form or not
     *
     * @param AbstractAttribute $customerAttribute
     * @return bool
     */
    private function canShowAttribute(AbstractAttribute $customerAttribute)
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
                'target' => '${ $.provider }:data.customer.website_id',
                'field' => 'website_ids'
            ];
        }
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
        if (\in_array($attribute->getFrontendInput(), $this->fileUploaderTypes)) {
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
}
