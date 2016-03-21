<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Mtf\Util\Generate\Fixture;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\ObjectManagerInterface;
use Magento\Eav\Model\Config;
use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * Provider of fields from database.
 */
class FieldsProvider
{
    /**
     * EAV configuration.
     *
     * @var Config
     */
    protected $eavConfig;

    /**
     * Resources and connections registry and factory.
     *
     * @var Resource
     */
    protected $resource;

    /**
     * Magento connection.
     *
     * @var AdapterInterface
     */
    protected $connection;

    /**
     * @constructor
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->eavConfig = $objectManager->create('Magento\Eav\Model\Config');
        $this->resource = $objectManager->create('Magento\Framework\App\ResourceConnection');
    }

    /**
     * Check connection to DB.
     *
     * @return bool
     */
    public function checkConnection()
    {
        $this->connection = $this->getConnection('core');
        if (!$this->connection || $this->connection instanceof \Zend_Db_Adapter_Exception) {
            echo ('Connection to Magento 2 database is absent. Fixture data has not been fetched.' . PHP_EOL);
            return false;
        }

        return true;
    }

    /**
     * Collect fields for the entity based on its type.
     *
     * @param array $fixture
     * @return array
     */
    public function getFields(array $fixture)
    {
        $method = $fixture['type'] . 'CollectFields';
        if (!method_exists($this, $method)) {
            return [];
        }

        return $this->$method($fixture);
    }

    /**
     * Collect fields for the entity with eav type.
     *
     * @param array $fixture
     * @return array
     */
    protected function eavCollectFields(array $fixture)
    {
        $entityType = $fixture['entity_type'];
        $collection = $this->eavConfig->getEntityType($entityType)->getAttributeCollection();
        $attributes = [];
        foreach ($collection as $attribute) {
            if (isset($fixture['product_type'])) {
                $applyTo = $attribute->getApplyTo();
                if (!empty($applyTo) && !in_array($fixture['product_type'], $applyTo)) {
                    continue;
                }
            }
            /** @var $attribute \Magento\Eav\Model\Entity\Attribute */
            $code = $attribute->getAttributeCode();
            $attributes[$code] = [
                'attribute_code' => $code,
                'backend_type' => $attribute->getBackendType(),
                'is_required' => $attribute->getIsRequired(),
                'default_value' => $attribute->getDefaultValue(),
                'input' => $attribute->getFrontendInput(),
            ];
        }

        return $attributes;
    }

    /**
     * Collect fields for the entity with table type.
     *
     * @param array $fixture
     * @return array
     */
    protected function tableCollectFields(array $fixture)
    {
        return $this->flatCollectFields($fixture);
    }

    /**
     * Collect fields for the entity with flat type.
     *
     * @param array $fixture
     * @return array
     */
    protected function flatCollectFields(array $fixture)
    {
        $entityType = $fixture['entity_type'];

        /** @var $connection \Magento\Framework\DB\Adapter\AdapterInterface */
        $fields = $this->connection->describeTable($entityType);

        $attributes = [];
        foreach ($fields as $code => $field) {
            $attributes[$code] = [
                'attribute_code' => $code,
                'backend_type' => $field['DATA_TYPE'],
                'is_required' => ($field['PRIMARY'] || $field['IDENTITY']),
                'default_value' => $field['DEFAULT'],
                'input' => '',
            ];
        }

        return $attributes;
    }

    /**
     * Collect fields for the entity with composite type.
     *
     * @param array $fixture
     * @return array
     */
    protected function compositeCollectFields(array $fixture)
    {
        $entityTypes = $fixture['entities'];

        $fields = [];
        foreach ($entityTypes as $entityType) {
            $fields = array_merge($fields, $this->connection->describeTable($entityType));
        }

        $attributes = [];
        foreach ($fields as $code => $field) {
            $attributes[$code] = [
                'attribute_code' => $code,
                'backend_type' => $field['DATA_TYPE'],
                'is_required' => ($field['PRIMARY'] || $field['IDENTITY']),
                'default_value' => $field['DEFAULT'],
                'input' => '',
            ];
        }

        return $attributes;
    }

    /**
     * Retrieve connection to resource specified by $resourceName.
     *
     * @param string $resourceName
     * @return \Exception|false|\Magento\Framework\DB\Adapter\AdapterInterface|\Zend_Exception
     */
    protected function getConnection($resourceName)
    {
        try {
            $connection = $this->resource->getConnection($resourceName);
            return $connection;
        } catch (\Zend_Exception $e) {
            echo $e->getMessage() . PHP_EOL;
            return $e;
        }
    }
}
