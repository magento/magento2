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
use Magento\Ui\DataProvider\EavValidationRules;
use Magento\Customer\Model\Resource\Customer\Collection;
use Magento\Customer\Model\Resource\Customer\CollectionFactory as CustomerCollectionFactory;
use Magento\Framework\View\Element\UiComponent\DataProvider\FilterPool;

/**
 * Class DataProvider
 */
class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
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
     * Constructor
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param EavValidationRules $eavValidationRules
     * @param CustomerCollectionFactory $customerCollectionFactory
     * @param Config $eavConfig
     * @param FilterPool $filterPool
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        EavValidationRules $eavValidationRules,
        CustomerCollectionFactory $customerCollectionFactory,
        Config $eavConfig,
        FilterPool $filterPool,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->eavValidationRules = $eavValidationRules;
        $this->collection = $customerCollectionFactory->create();
        $this->collection->addAttributeToSelect('*');
        $this->eavConfig = $eavConfig;
        $this->filterPool = $filterPool;
        $this->meta['customer']['fields'] = $this->getAttributesMeta(
            $this->eavConfig->getEntityType('customer')
        );
        $this->meta['address']['fields'] = $this->getAttributesMeta(
            $this->eavConfig->getEntityType('customer_address')
        );
    }

    /**
     * @return Collection|\Magento\Framework\Model\Resource\Db\Collection\AbstractCollection
     */
    protected function getCollection()
    {
        return $this->collection;
    }

    /**
     * @inheritdoc
     */
    public function addFilter($condition, $field = null, $type = 'regular')
    {
        $this->filterPool->registerNewFilter($condition, $field, $type);
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
        $this->filterPool->applyFilters($this->collection);
        $items = $this->collection->getItems();
        /** @var Customer $customer */
        foreach ($items as $customer) {
            $result['customer'] = $customer->getData();

            $addresses = [];
            /** @var Address $address */
            foreach ($customer->getAddresses() as $address) {
                $addressId = $address->getId();
                $address->load($addressId);
                $addresses[$addressId] = $address->getData();
                $this->prepareAddressData($addressId, $addresses, $result['customer']);
            }
            if (!empty($addresses)) {
                $result['address'] = $addresses;
            }

            $this->loadedData[$customer->getId()] = $result;
        }

        return $this->loadedData;
    }

    /**
     * Retrieve count of loaded items
     *
     * @return int
     */
    public function count()
    {
        $this->filterPool->applyFilters($this->collection);
        return $this->collection->count();
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

            $rules = $this->eavValidationRules->build($attribute, $meta[$code]);
            if (!empty($rules)) {
                $meta[$code]['validation'] = $rules;
            }
        }
        return $meta;
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
}
