<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Customer;

use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Address;
use Magento\Customer\Model\Attribute;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\FileProcessorFactory;
use Magento\Customer\Model\ResourceModel\Address\Attribute\Source\CountryWithWebsites;
use Magento\Customer\Model\ResourceModel\Customer\Collection;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Model\Entity\Type;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\FilterPool;
use Magento\Ui\Component\Form\Element\Multiline;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\DataProvider\EavValidationRules;
use Magento\Customer\Model\FileUploaderDataResolver;

/**
 * Supplies the data for the customer UI component
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 *
 * @deprecated 102.0.1 \Magento\Customer\Model\Customer\DataProviderWithDefaultAddresses is used instead
 * @api
 * @since 100.0.2
 */
class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * Maximum file size allowed for file_uploader UI component
     */
    const MAX_FILE_SIZE = 2097152;

    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @var Config
     */
    protected $eavConfig;

    /**
     * @var FilterPool
     */
    protected $filterPool;

    /**
     * @var array
     */
    protected $loadedData;

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
    protected $metaProperties = [
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
    protected $formElement = [
        'text' => 'input',
        'hidden' => 'input',
        'boolean' => 'checkbox',
    ];

    /**
     * @var EavValidationRules
     */
    protected $eavValidationRules;

    /**
     * @var SessionManagerInterface
     * @since 100.1.0
     */
    protected $session;

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
     * @var FileUploaderDataResolver
     */
    private $fileUploaderDataResolver;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param EavValidationRules $eavValidationRules
     * @param CustomerCollectionFactory $customerCollectionFactory
     * @param Config $eavConfig
     * @param FilterPool $filterPool
     * @param FileProcessorFactory $fileProcessorFactory
     * @param array $meta
     * @param array $data
     * @param ContextInterface $context
     * @param bool $allowToShowHiddenAttributes
     * @param FileUploaderDataResolver|null $fileUploaderDataResolver
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        EavValidationRules $eavValidationRules,
        CustomerCollectionFactory $customerCollectionFactory,
        Config $eavConfig,
        FilterPool $filterPool,
        ?FileProcessorFactory $fileProcessorFactory = null,
        array $meta = [],
        array $data = [],
        ?ContextInterface $context = null,
        $allowToShowHiddenAttributes = true,
        $fileUploaderDataResolver = null
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->eavValidationRules = $eavValidationRules;
        $this->collection = $customerCollectionFactory->create();
        $this->collection->addAttributeToSelect('*');
        $this->eavConfig = $eavConfig;
        $this->filterPool = $filterPool;
        $this->context = $context ?: ObjectManager::getInstance()->get(ContextInterface::class);
        $this->allowToShowHiddenAttributes = $allowToShowHiddenAttributes;
        $this->fileUploaderDataResolver = $fileUploaderDataResolver
            ?: ObjectManager::getInstance()->get(FileUploaderDataResolver::class);
        $this->meta['customer']['children'] = $this->getAttributesMeta(
            $this->eavConfig->getEntityType('customer')
        );
        $this->meta['address']['children'] = $this->getAttributesMeta(
            $this->eavConfig->getEntityType('customer_address')
        );
    }

    /**
     * Get session object
     *
     * @return SessionManagerInterface
     * @deprecated 100.1.3
     * @since 100.1.0
     */
    protected function getSession()
    {
        if ($this->session === null) {
            $this->session = ObjectManager::getInstance()->get(
                \Magento\Framework\Session\SessionManagerInterface::class
            );
        }
        return $this->session;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        /** @var Customer $customer */
        foreach ($items as $customer) {
            $result['customer'] = $customer->getData();

            $this->fileUploaderDataResolver->overrideFileUploaderData($customer, $result['customer']);

            $result['customer'] = array_diff_key(
                $result['customer'],
                array_flip($this->forbiddenCustomerFields)
            );
            unset($result['address']);

            /** @var Address $address */
            foreach ($customer->getAddresses() as $address) {
                $addressId = $address->getId();
                $address->load($addressId);
                $result['address'][$addressId] = $address->getData();
                $this->prepareAddressData($addressId, $result['address'], $result['customer']);

                $this->fileUploaderDataResolver->overrideFileUploaderData($address, $result['address'][$addressId]);
            }
            $this->loadedData[$customer->getId()] = $result;
        }

        $data = $this->getSession()->getCustomerFormData();
        if (!empty($data)) {
            $customerId = isset($data['customer']['entity_id']) ? $data['customer']['entity_id'] : null;
            $this->loadedData[$customerId] = $data;
            $this->getSession()->unsCustomerFormData();
        }

        return $this->loadedData;
    }

    /**
     * Get attributes meta
     *
     * @param Type $entityType
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getAttributesMeta(Type $entityType)
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
                    $meta[$code]['arguments']['data']['config']['formElement'] = isset($this->formElement[$value])
                        ? $this->formElement[$value]
                        : $value;
                }
            }

            if ($attribute->usesSource()) {
                if ($code == AddressInterface::COUNTRY_ID) {
                    $meta[$code]['arguments']['data']['config']['options'] = $this->getCountryWithWebsiteSource()
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

            $this->fileUploaderDataResolver->overrideFileUploaderMetadata(
                $entityType,
                $attribute,
                $meta[$code]['arguments']['data']['config']
            );
        }

        $this->processWebsiteMeta($meta);
        return $meta;
    }

    /**
     * Detect can we show attribute on specific form or not
     *
     * @param Attribute $customerAttribute
     * @return bool
     */
    private function canShowAttribute(AbstractAttribute $customerAttribute): bool
    {
        return $this->allowToShowHiddenAttributes && (bool) $customerAttribute->getIsUserDefined()
            ? true
            : (bool) $customerAttribute->getIsVisible();
    }

    /**
     * Retrieve Country With Websites Source
     *
     * @return CountryWithWebsites
     * @deprecated 101.0.0
     */
    private function getCountryWithWebsiteSource()
    {
        if (!$this->countryWithWebsiteSource) {
            $this->countryWithWebsiteSource = ObjectManager::getInstance()->get(CountryWithWebsites::class);
        }

        return $this->countryWithWebsiteSource;
    }

    /**
     * Retrieve Customer Config Share
     *
     * @return \Magento\Customer\Model\Config\Share
     * @deprecated 100.1.3
     */
    private function getShareConfig()
    {
        if (!$this->shareConfig) {
            $this->shareConfig = ObjectManager::getInstance()->get(\Magento\Customer\Model\Config\Share::class);
        }

        return $this->shareConfig;
    }

    /**
     * Add global scope parameter and filter options to website meta
     *
     * @param array $meta
     * @return void
     */
    private function processWebsiteMeta(&$meta)
    {
        if (isset($meta[CustomerInterface::WEBSITE_ID]) && $this->getShareConfig()->isGlobalScope()) {
            $meta[CustomerInterface::WEBSITE_ID]['arguments']['data']['config']['isGlobalScope'] = 1;
        }

        if (isset($meta[AddressInterface::COUNTRY_ID]) && !$this->getShareConfig()->isGlobalScope()) {
            $meta[AddressInterface::COUNTRY_ID]['arguments']['data']['config']['filterBy'] = [
                'target' => '${ $.provider }:data.customer.website_id',
                '__disableTmpl' => ['target' => false],
                'field' => 'website_ids'
            ];
        }
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
     * Prepare address data
     *
     * @param int $addressId
     * @param array $addresses
     * @param array $customer
     * @return void
     */
    protected function prepareAddressData($addressId, array &$addresses, array $customer)
    {
        if (isset($customer['default_billing'])
            && $addressId == $customer['default_billing']
        ) {
            $addresses[$addressId]['default_billing'] = $customer['default_billing'];
        }
        if (isset($customer['default_shipping'])
            && $addressId == $customer['default_shipping']
        ) {
            $addresses[$addressId]['default_shipping'] = $customer['default_shipping'];
        }

        foreach ($this->meta['address']['children'] as $attributeName => $attributeMeta) {
            if ($attributeMeta['arguments']['data']['config']['dataType'] === Multiline::NAME
                && isset($addresses[$addressId][$attributeName])
                && !is_array($addresses[$addressId][$attributeName])
            ) {
                $addresses[$addressId][$attributeName] = explode("\n", $addresses[$addressId][$attributeName]);
            }
        }
    }
}
