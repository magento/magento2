<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Indexer;

use Magento\Customer\Model\Customer;
use Magento\Indexer\Model\FieldsetInterface;
use Magento\Customer\Model\Resource\Attribute\Collection;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute;

class AttributeProvider implements FieldsetInterface
{
    /**
     * EAV entity
     */
    const ENTITY = Customer::ENTITY;

    /**
     * @var Attribute[]
     */
    protected $searchableAttributes;

    /**
     * @param Config $eavConfig
     * @param Collection $collection
     */
    public function __construct(
        Config $eavConfig,
        Collection $collection
    ) {
        $this->eavConfig = $eavConfig;
        $this->collection = $collection;
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
            $this->collection->addFieldToFilter('is_used_in_grid', true);
            /** @var \Magento\Eav\Model\Entity\Attribute[] $attributes */
            $attributes = $this->collection->getItems();
            /** @var \Magento\Eav\Model\Entity\AbstractEntity $entity */
            $entity = $this->eavConfig->getEntityType(static::ENTITY)->getEntity();

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
            $field = [
                'name'     => $attribute->getName(),
                'handler'  => null,
                'origin'   => $attribute->getName(),
                'type'     => $this->getType($attribute),
                'filters'  => [],
            ];
            if ($attribute->getBackendType() != 'static') {
                $field['dataType'] = $attribute->getBackendType();
            }
            $fields[] = $field;
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
