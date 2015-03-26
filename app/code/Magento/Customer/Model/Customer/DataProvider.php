<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Customer;

use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Type;
use Magento\Customer\Model\Address;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Resource\Customer\Collection;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface;
use Magento\Customer\Model\Resource\Customer\CollectionFactory as CustomerCollectionFactory;

/**
 * Class DataProvider
 */
class DataProvider implements DataProviderInterface
{
    /**
     * @var string
     */
    protected $primaryFieldName;

    /**
     * @var string
     */
    protected $requestFieldName;

    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @var Config
     */
    protected $eavConfig;

    /**
     * @var array
     */
    protected $meta = [];

    /**
     * Provider configuration data
     *
     * @var array
     */
    protected $data = [];

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
        'size' => 'scope_multiline_count'
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
     * Constructor
     *
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CustomerCollectionFactory $customerCollectionFactory
     * @param Config $eavConfig
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $primaryFieldName,
        $requestFieldName,
        CustomerCollectionFactory $customerCollectionFactory,
        Config $eavConfig,
        array $meta = [],
        array $data = []
    ) {
        $this->primaryFieldName = $primaryFieldName;
        $this->requestFieldName = $requestFieldName;
        $this->collection = $customerCollectionFactory->create();
        $this->collection->addAttributeToSelect('*');
        $this->eavConfig = $eavConfig;
        $this->meta = $meta;
        $this->meta['customer']['fields'] = $this->getAttributesMeta(
            $this->eavConfig->getEntityType('customer')
        );
        $this->meta['address']['fields'] = $this->getAttributesMeta(
            $this->eavConfig->getEntityType('customer_address')
        );
        $this->data = $data;
    }

    /**
     * @return array
     */
    public function getMeta()
    {
        return $this->meta;
    }

    /**
     * @param string $fieldSetName
     * @param string $fieldName
     * @return array
     */
    public function getFieldMetaInfo($fieldSetName, $fieldName)
    {
        return isset($this->meta[$fieldSetName]['fields'][$fieldName])
            ? $this->meta[$fieldSetName]['fields'][$fieldName]
            : [];
    }

    /**
     * @param Type $entityType
     * @return array
     * @throws \Magento\Eav\Exception
     */
    protected function getAttributesMeta(Type $entityType)
    {
        $meta = [];
        $attributes = $entityType->getAttributeCollection();
        /* @var \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute */
        foreach ($attributes as $attribute) {
            $code = $attribute->getAttributeCode();
            // use getDataUsingMethod, since some getters are defined and apply additional processing of returning value
            foreach ($this->metaProperties as $metaName => $origName) {
                $value = $attribute->getDataUsingMethod($origName);
                $meta[$code][$metaName] = $value;
                if ('frontend_input' === $origName) {
                    $meta[$code]['formElement'] = isset($this->formElement[$value])
                        ? $this->formElement[$value]
                        : $value;
                }
                if ($attribute->usesSource()) {
                    $meta[$code]['options'] = $attribute->getSource()->getAllOptions();
                }
            }
        }
        return $meta;
    }

    /**
     * Get config data
     *
     * @return mixed
     */
    public function getConfigData()
    {
        return isset($this->data['config']) ? $this->data['config'] : [];
    }

    /**
     * Set data
     *
     * @param mixed $config
     * @return void
     */
    public function setConfigData($config)
    {
        $this->data['config'] = $config;
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

            $addresses = [];
            /** @var Address $address */
            foreach ($customer->getAddresses() as $address) {
                $address->load($address->getId());
                $addresses[$address->getId()] = $address->getData();
            }
            $result['address'] = $addresses;

            $this->loadedData[$customer->getId()] = $result;
        }

        return $this->loadedData;
    }

    /**
     * Get field name in request
     *
     * @return string
     */
    public function getRequestFieldName()
    {
        return $this->requestFieldName;
    }

    /**
     * Get primary field name
     *
     * @return string
     */
    public function getPrimaryFieldName()
    {
        return $this->primaryFieldName;
    }

    /**
     * @inheritdoc
     */
    public function addFilter($field, $condition = null)
    {
        $this->collection->addFieldToFilter($field, $condition);
    }

    /**
     * Add field to select
     *
     * @param string|array $field
     * @param string|null $alias
     * @return void
     */
    public function addField($field, $alias = null)
    {
        $this->collection->addAttributeToSelect($field);
    }

    /**
     * self::setOrder() alias
     *
     * @param string $field
     * @param string $direction
     * @return void
     */
    public function addOrder($field, $direction)
    {
        $this->collection->addOrder($field, $direction);
    }

    /**
     * Set Query limit
     *
     * @param int $offset
     * @param int $size
     * @return void
     */
    public function setLimit($offset, $size)
    {
        $this->collection->setPageSize($size);
        $this->collection->setCurPage($offset);
    }

    /**
     * Removes field from select
     *
     * @param string|null $field
     * @param bool $isAlias Alias identifier
     * @return void
     */
    public function removeField($field, $isAlias = false)
    {
        $this->collection->removeAttributeToSelect($field);
    }

    /**
     * Removes all fields from select
     *
     * @return void
     */
    public function removeAllFields()
    {
        $this->collection->removeAttributeToSelect();
    }

    /**
     * Retrieve count of loaded items
     *
     * @return int
     */
    public function count()
    {
        return $this->collection->count();
    }

    /**
     * @param string $fieldSetName
     * @return array
     */
    public function getFieldsMetaInfo($fieldSetName)
    {
        return isset($this->meta[$fieldSetName]['fields']) ? $this->meta[$fieldSetName]['fields'] : [];
    }
}
