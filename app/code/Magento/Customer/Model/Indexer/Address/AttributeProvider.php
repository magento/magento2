<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Indexer\Address;

use Magento\Indexer\Model\FieldsetInterface;
use Magento\Customer\Model\Resource\Address\Attribute\CollectionFactory;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute;

class AttributeProvider implements FieldsetInterface
{
    /**
     * @var Attribute[]
     */
    protected $searchableAttributes;

    /**
     * @param Config $eavConfig
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Config $eavConfig,
        CollectionFactory $collectionFactory
    ) {
        $this->eavConfig = $eavConfig;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @param array $data
     * @return array
     */
    public function addDynamicData(array $data)
    {
        $additionalFields = $this->convert($this->getSearchableAttributes());
        $data['fields'] = $this->merge($data['fields'], $additionalFields);
        return $data;
    }

    /**
     * Retrieve searchable attributes
     *
     * @return Attribute[]
     */
    private function getSearchableAttributes()
    {
        if ($this->searchableAttributes === null) {
            $this->searchableAttributes = [];
            /** @var \Magento\Customer\Model\Resource\Address\Attribute\Collection $addressAttributes */
            $addressAttributes = $this->collectionFactory->create();
            /** @var \Magento\Eav\Model\Entity\Attribute[] $attributes */
            $attributes = $addressAttributes->getItems();
            /** @var \Magento\Eav\Model\Entity\AbstractEntity $entity */
            $entity = $this->eavConfig->getEntityType('customer_address')->getEntity();

            foreach ($attributes as $attribute) {
                $attribute->setEntity($entity);
            }
            $this->searchableAttributes = $attributes;
        }

        return $this->searchableAttributes;
    }

    /**
     * @param Attribute[] $attributes
     * @return array
     */
    protected function convert(array $attributes)
    {
        $fields = [];
        foreach ($attributes as $attribute) {
            $fields[] = [
                'name'     => $attribute->getName(),
                'handler'  => null,
                'origin'   => $attribute->getName(),
                'type'     => $this->getType($attribute),
                'filters'  => [],
            ];
            if ($attribute->getBackendType() != 'static') {
                $fields['dataType'] = $attribute->getBackendType();
            }
        }

        return $fields;
    }

    /**
     * @param Attribute $attribute
     * @return string
     */
    protected function getType(Attribute $attribute)
    {
        if ($attribute->getData('is_searchable_in_grid')) {
            $type = 'searchable';
        } elseif ($attribute->getData('is_filterable_in_grid')) {
            $type = 'filterable';
        } else {
            $type = 'virtual';
        }

        return $type;
    }

    /**
     * @param array $dataFields
     * @param array $searchableFields
     * @return array
     */
    protected function merge(array $dataFields, array $searchableFields)
    {
        foreach ($searchableFields as $field) {
            if (!isset($dataFields[$field['name']]) && !isset($field['dataType'])) {
                continue;
            }
            if (!isset($dataFields[$field['name']])) {
                $dataFields[$field['name']] = [];
            }
            foreach ($field as $key => $value) {
                $dataFields[$field['name']][$key] = $value;
            }
        }

        return $dataFields;
    }

    /**
     * @return string
     */
    public function getDefaultHandler()
    {
    }
}
