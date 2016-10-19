<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Customer;

use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Model\Attribute;
use Magento\Customer\Model\FileProcessor;
use Magento\Customer\Model\FileProcessorFactory;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Model\Entity\Type;
use Magento\Customer\Model\Address;
use Magento\Customer\Model\Customer;
use Magento\Framework\App\ObjectManager;
use Magento\Ui\DataProvider\EavValidationRules;
use Magento\Customer\Model\ResourceModel\Customer\Collection;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;
use Magento\Framework\View\Element\UiComponent\DataProvider\FilterPool;

/**
 * Class DataProvider
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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
     * @var FileProcessorFactory
     */
    private $fileProcessorFactory;

    /**
     * File types allowed for file uploader UI component
     *
     * @var array
     */
    private $fileUploaderTypes = [
        'image',
        'file',
    ];

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
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        EavValidationRules $eavValidationRules,
        CustomerCollectionFactory $customerCollectionFactory,
        Config $eavConfig,
        FilterPool $filterPool,
        FileProcessorFactory $fileProcessorFactory = null,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->eavValidationRules = $eavValidationRules;
        $this->collection = $customerCollectionFactory->create();
        $this->collection->addAttributeToSelect('*');
        $this->eavConfig = $eavConfig;
        $this->filterPool = $filterPool;
        $this->fileProcessorFactory = $fileProcessorFactory ?: $this->getFileProcessorFactory();
        $this->meta['customer']['fields'] = $this->getAttributesMeta(
            $this->eavConfig->getEntityType('customer')
        );
        $this->meta['address']['fields'] = $this->getAttributesMeta(
            $this->eavConfig->getEntityType('customer_address')
        );
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

            $this->overrideFileUploaderData($customer, $result['customer']);

            unset($result['address']);

            /** @var Address $address */
            foreach ($customer->getAddresses() as $address) {
                $addressId = $address->getId();
                $address->load($addressId);
                $result['address'][$addressId] = $address->getData();
                $this->prepareAddressData($addressId, $result['address'], $result['customer']);

                $this->overrideFileUploaderData($address, $result['address'][$addressId]);
            }
            $this->loadedData[$customer->getId()] = $result;
        }

        return $this->loadedData;
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
     * Retrieve array of values required by file uploader UI component
     *
     * @param Type $entityType
     * @param Attribute $attribute
     * @param array $customerData
     * @return array
     */
    private function getFileUploaderData(
        Type $entityType,
        Attribute $attribute,
        array $customerData
    ) {
        $attributeCode = $attribute->getAttributeCode();

        $file = isset($customerData[$attributeCode])
            ? $customerData[$attributeCode]
            : '';

        if (!empty($file)) {
            /** @var FileProcessor $fileProcessor */
            $fileProcessor = $this->getFileProcessorFactory()->create([
                'entityTypeCode' => $entityType->getEntityTypeCode(),
            ]);

            if ($fileProcessor->isExist($file)) {
                $stat = $fileProcessor->getStat($file);
                $viewUrl = $fileProcessor->getViewUrl($file, $attribute->getFrontendInput());
            }

            return [
                [
                    'file' => $file,
                    'size' => isset($stat) ? $stat['size'] : 0,
                    'url' => isset($viewUrl) ? $viewUrl : '',
                    'name' => $this->normalizeFileName($file),
                ],
            ];
        }

        return [];
    }

    /**
     * Normalize file name
     *
     * @param string $file
     * @return string
     */
    private function normalizeFileName($file)
    {
        if (strrpos($file, '/') !== false) {
            $file = substr($file, strrpos($file, '/') + 1);
        }
        return $file;
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
            $code = $attribute->getAttributeCode();
            // use getDataUsingMethod, since some getters are defined and apply additional processing of returning value
            foreach ($this->metaProperties as $metaName => $origName) {
                $value = $attribute->getDataUsingMethod($origName);
                $meta[$code][$metaName] = ($metaName === 'label') ? __($value) : $value;
                if ('frontend_input' === $origName) {
                    $meta[$code]['formElement'] = isset($this->formElement[$value])
                        ? $this->formElement[$value]
                        : $value;
                }
                if ($attribute->usesSource()) {
                    $meta[$code]['options'] = $attribute->getSource()->getAllOptions();
                }
            }

            $rules = $this->eavValidationRules->build($attribute, $meta[$code]);
            if (!empty($rules)) {
                $meta[$code]['validation'] = $rules;
            }

            $this->overrideFileUploaderMetadata($entityType, $attribute, $meta[$code]);
        }
        return $meta;
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
     * Retrieve metadata value
     *
     * @param array $config
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    private function getMetadataValue($config, $name, $default = null)
    {
        $value = isset($config[$name]) ? $config[$name] : $default;
        return $value;
    }

    /**
     * Retrieve URL to file upload
     *
     * @param string $entityTypeCode
     * @return string
     */
    private function getFileUploadUrl($entityTypeCode)
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
        if (isset($addresses[$addressId]['street'])) {
            $addresses[$addressId]['street'] = explode("\n", $addresses[$addressId]['street']);
        }
    }

    /**
     * Get FileProcessorFactory instance
     *
     * @return FileProcessorFactory
     *
     * @deprecated
     */
    private function getFileProcessorFactory()
    {
        if ($this->fileProcessorFactory === null) {
            $this->fileProcessorFactory = ObjectManager::getInstance()->get(FileProcessorFactory::class);
        }
        return $this->fileProcessorFactory;
    }
}
