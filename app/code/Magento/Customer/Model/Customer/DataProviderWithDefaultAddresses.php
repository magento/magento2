<?php
declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Customer;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Address;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;
use Magento\Directory\Model\CountryFactory;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Model\Entity\Type;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Customer\Model\FileUploaderDataResolver;
use Magento\Customer\Model\AttributeMetadataResolver;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Form\Element\Multiline;
use Magento\Ui\DataProvider\AbstractDataProvider;

/**
 * Refactored version of Magento\Customer\Model\Customer\DataProvider with eliminated usage of addresses collection.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DataProviderWithDefaultAddresses extends AbstractDataProvider
{
    /**
     * @var array
     */
    private $loadedData = [];

    /**
     * @var SessionManagerInterface
     */
    private $session;

    /**
     * Customer fields that must be removed
     *
     * @var array
     */
    private static $forbiddenCustomerFields = [
        'password_hash',
        'rp_token',
    ];

    /**
     * Allow to manage attributes, even they are hidden on storefront
     *
     * @var bool
     */
    private $allowToShowHiddenAttributes;

    /**
     * @var CountryFactory
     */
    private $countryFactory;

    /**
     * @var FileUploaderDataResolver
     */
    private $fileUploaderDataResolver;

    /**
     * @var AttributeMetadataResolver
     */
    private $attributeMetadataResolver;

    /**
     * @var CustomerFactory
     */
    private $customerFactory;

    /**
     * @var ContextInterface
     */
    private $context;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CustomerCollectionFactory $customerCollectionFactory
     * @param Config $eavConfig
     * @param CountryFactory $countryFactory
     * @param SessionManagerInterface $session
     * @param FileUploaderDataResolver $fileUploaderDataResolver
     * @param AttributeMetadataResolver $attributeMetadataResolver
     * @param bool $allowToShowHiddenAttributes
     * @param array $meta
     * @param array $data
     * @param CustomerFactory|null $customerFactory
     * @param ContextInterface|null $context
     * @param CustomerRepositoryInterface|null $customerRepository
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        CustomerCollectionFactory $customerCollectionFactory,
        Config $eavConfig,
        CountryFactory $countryFactory,
        SessionManagerInterface $session,
        FileUploaderDataResolver $fileUploaderDataResolver,
        AttributeMetadataResolver $attributeMetadataResolver,
        $allowToShowHiddenAttributes = true,
        array $meta = [],
        array $data = [],
        CustomerFactory $customerFactory = null,
        ?ContextInterface $context = null,
        CustomerRepositoryInterface $customerRepository = null
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $customerCollectionFactory->create();
        $this->collection->addAttributeToSelect('*');
        $this->allowToShowHiddenAttributes = $allowToShowHiddenAttributes;
        $this->session = $session;
        $this->countryFactory = $countryFactory;
        $this->fileUploaderDataResolver = $fileUploaderDataResolver;
        $this->attributeMetadataResolver = $attributeMetadataResolver;
        $this->context = $context ?? ObjectManager::getInstance()->get(ContextInterface::class);
        $this->customerFactory = $customerFactory ?: ObjectManager::getInstance()->get(CustomerFactory::class);
        $this->customerRepository = $customerRepository ??
            ObjectManager::getInstance()->get(CustomerRepositoryInterface::class);
        $this->meta['customer']['children'] = $this->getAttributesMeta(
            $eavConfig->getEntityType('customer')
        );
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData(): array
    {
        if (!empty($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        /** @var Customer $customer */
        foreach ($items as $customer) {
            $result['customer'] = $customer->getData();

            $this->fileUploaderDataResolver->overrideFileUploaderData($customer, $result['customer']);

            $result['customer'] = array_diff_key(
                $result['customer'],
                array_flip(self::$forbiddenCustomerFields)
            );
            $this->prepareCustomAttributeValue($result['customer']);

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
            $customer = $this->customerFactory->create();
            $this->fileUploaderDataResolver->overrideFileUploaderData($customer, $data['customer']);
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
        if (!$address) {
            return [];
        }

        $addressData = $address->getData();
        if (isset($addressData['street']) && !is_array($addressData['street'])) {
            $addressData['street'] = explode("\n", $addressData['street']);
        }
        if (!empty($addressData['country_id'])) {
            $addressData['country'] = $this->countryFactory->create()
                ->loadByCode($addressData['country_id'])
                ->getName();
        }
        $addressData['region'] = $address->getRegion();

        return $addressData;
    }

    /***
     * Prepare values for Custom Attributes.
     *
     * @param array $data
     * @return void
     */
    private function prepareCustomAttributeValue(array &$data): void
    {
        foreach ($this->meta['customer']['children'] as $attributeName => $attributeMeta) {
            if ($attributeMeta['arguments']['data']['config']['dataType'] === Multiline::NAME
                && isset($data[$attributeName])
                && !is_array($data[$attributeName])
            ) {
                $data[$attributeName] = explode("\n", $data[$attributeName]);
            }
        }
    }

    /**
     * Get attributes meta
     *
     * @param Type $entityType
     * @return array
     * @throws LocalizedException
     */
    private function getAttributesMeta(Type $entityType): array
    {
        $meta = [];
        $attributes = $entityType->getAttributeCollection();
        $customerId = $this->context->getRequestParam('id');
        if ($customerId) {
            $customer = $this->customerRepository->getById($customerId);
            $attributes->setWebsite($customer->getWebsiteId());
        }
        /* @var AbstractAttribute $attribute */
        foreach ($attributes as $attribute) {
            $meta[$attribute->getAttributeCode()] = $this->attributeMetadataResolver->getAttributesMeta(
                $attribute,
                $entityType,
                $this->allowToShowHiddenAttributes
            );
        }
        $this->attributeMetadataResolver->processWebsiteMeta($meta);

        return $meta;
    }
}
