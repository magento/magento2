<?php
declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Address;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\AddressRegistry;
use Magento\Customer\Model\ResourceModel\Address\Attribute\Collection as AddressAttributeCollection;
use Magento\Customer\Model\ResourceModel\Address\CollectionFactory;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Type;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Customer\Model\Address;
use Magento\Customer\Model\FileUploaderDataResolver;
use Magento\Customer\Model\AttributeMetadataResolver;
use Magento\Ui\Component\Form\Element\Multiline;

/**
 * Dataprovider of customer addresses for customer address grid.
 *
 * @property \Magento\Customer\Model\ResourceModel\Address\Collection $collection
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var array
     */
    private $loadedData;

    /**
     * Allow to manage attributes, even they are hidden on storefront
     *
     * @var bool
     */
    private $allowToShowHiddenAttributes;

    /**
     * @var ContextInterface
     */
    private $context;

    /**
     * @var array
     */
    private $bannedInputTypes = ['media_image'];

    /**
     * @var array
     */
    private static $attributesToEliminate = [
        'region',
        'vat_is_valid',
        'vat_request_date',
        'vat_request_id',
        'vat_request_success'
    ];

    /**
     * @var FileUploaderDataResolver
     */
    private $fileUploaderDataResolver;

    /**
     * @var AttributeMetadataResolver
     */
    private $attributeMetadataResolver;

    /**
     * @var AddressRegistry
     */
    private $addressRegistry;

    /**
     * DataProvider constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $addressCollectionFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param Config $eavConfig
     * @param ContextInterface $context
     * @param FileUploaderDataResolver $fileUploaderDataResolver
     * @param AttributeMetadataResolver $attributeMetadataResolver
     * @param array $meta
     * @param array $data
     * @param bool $allowToShowHiddenAttributes
     * @param AddressRegistry|null $addressRegistry
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $addressCollectionFactory,
        CustomerRepositoryInterface $customerRepository,
        Config $eavConfig,
        ContextInterface $context,
        FileUploaderDataResolver $fileUploaderDataResolver,
        AttributeMetadataResolver $attributeMetadataResolver,
        array $meta = [],
        array $data = [],
        $allowToShowHiddenAttributes = true,
        ?AddressRegistry $addressRegistry = null
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $addressCollectionFactory->create();
        $this->collection->addAttributeToSelect('*');
        $this->customerRepository = $customerRepository;
        $this->allowToShowHiddenAttributes = $allowToShowHiddenAttributes;
        $this->context = $context;
        $this->fileUploaderDataResolver = $fileUploaderDataResolver;
        $this->attributeMetadataResolver = $attributeMetadataResolver;
        $this->addressRegistry = $addressRegistry ?? ObjectManager::getInstance()->get(AddressRegistry::class);
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
    public function getData(): array
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
            $this->fileUploaderDataResolver->overrideFileUploaderData($item, $this->loadedData[$addressId]);
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
    private function prepareAddressData($addressId, array &$addresses, $defaultBilling, $defaultShipping): void
    {
        if (null !== $defaultBilling && $addressId === $defaultBilling) {
            $addresses[$addressId]['default_billing'] = '1';
        }
        if (null !== $defaultShipping && $addressId === $defaultShipping) {
            $addresses[$addressId]['default_shipping'] = '1';
        }
        foreach ($this->meta['general']['children'] as $attributeName => $attributeMeta) {
            if ($attributeMeta['arguments']['data']['config']['dataType'] === Multiline::NAME
                && isset($this->loadedData[$addressId][$attributeName])
                && !\is_array($this->loadedData[$addressId][$attributeName])
            ) {
                $this->loadedData[$addressId][$attributeName] = explode(
                    "\n",
                    $this->loadedData[$addressId][$attributeName]
                );
            }
        }
    }

    /**
     * Get default customer data for adding new address
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @return array
     */
    private function getDefaultData(): array
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
     * Get attributes meta
     *
     * @param Type $entityType
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function getAttributesMeta(Type $entityType): array
    {
        $meta = [];
        /** @var AddressAttributeCollection $attributes */
        $attributes = $entityType->getAttributeCollection();
        $customerId = $this->context->getRequestParam('parent_id');
        $entityId = $this->context->getRequestParam('entity_id');
        if (!$customerId && $entityId) {
            $customerId = $this->addressRegistry->retrieve($entityId)->getParentId();
        }

        if ($customerId) {
            $customer = $this->customerRepository->getById($customerId);
            $attributes->setWebsite($customer->getWebsiteId());
        }
        /* @var AbstractAttribute $attribute */
        foreach ($attributes as $attribute) {
            if (\in_array($attribute->getFrontendInput(), $this->bannedInputTypes, true)) {
                continue;
            }
            if (\in_array($attribute->getAttributeCode(), self::$attributesToEliminate, true)) {
                continue;
            }
            $meta[$attribute->getAttributeCode()] = $this->attributeMetadataResolver->getAttributesMeta(
                $attribute,
                $entityType,
                $this->allowToShowHiddenAttributes
            );
            if ($attribute->getAttributeCode() === 'street' && $entityId) {
                $customerAddressStreet = $this->addressRegistry->retrieve($entityId)->getStreet();
                $meta[$attribute->getAttributeCode()]["arguments"]["data"]["config"]["size"] = max(
                    $meta[$attribute->getAttributeCode()]["arguments"]["data"]["config"]["size"],
                    count($customerAddressStreet)
                );
            }
        }
        $this->attributeMetadataResolver->processWebsiteMeta($meta);

        return $meta;
    }
}
