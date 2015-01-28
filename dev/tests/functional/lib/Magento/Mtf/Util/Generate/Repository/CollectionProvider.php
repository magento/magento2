<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Mtf\Util\Generate\Repository;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class CollectionProvider
 *
 */
class CollectionProvider implements CollectionProviderInterface
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Magetno resource instance.
     *
     * @var \Magento\Framework\App\Resource
     */
    protected $resource;

    /**
     * @constructor
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
        $this->resource = $objectManager->create('Magento\Framework\App\Resource');
    }

    /**
     * Check connection to DB.
     *
     * @return bool
     */
    public function checkConnection()
    {
        $connection = $this->getConnection('read');
        if (!$connection || $connection instanceof \Zend_Db_Adapter_Exception) {
            echo ('Connection to Magento 2 database is absent. Repository data has not been fetched.' . PHP_EOL);
            return false;
        }

        return true;
    }

    /**
     * Get collection by type which is specified in fixture .xml
     *
     * @param array $fixture
     * @return array
     */
    public function getCollection(array $fixture)
    {
        $type = $fixture['type'];
        $method = $type . 'Collection';
        if (!method_exists($this, $method)) {
            return [];
        }

        $collection = $this->$method($fixture);

        $keys = !empty($fixture['data_set']) && is_array($fixture['data_set'])
            ? array_keys($fixture['data_set'])
            : [];

        $items = [];
        foreach ($collection as $model) {
            /** @var $model \Magento\Framework\Object */
            $item = $model->toArray($keys);
            $item['id'] = $model->getId();
            $items[] = $item;
        }

        return $items;
    }

    /**
     * Get collection of objects for which eav or flat type is not exist
     *
     * @param array $fixture
     * @return \Magento\Framework\Object[]
     */
    protected function tableCollection(array $fixture)
    {
        $collection = $fixture['collection'];
        $collection = $this->objectManager->create($collection, ['fixture' => $fixture]);
        /** @var $collection \Magento\Mtf\Util\Generate\Repository\TableCollection */
        return $collection->getItems();
    }

    /**
     * Get collection of objects for flat type
     *
     * @param array $fixture
     * @return \Magento\Framework\Object[]
     */
    protected function flatCollection(array $fixture)
    {
        $collection = $fixture['collection'];
        $collection = $this->objectManager->create($collection);
        /** @var $collection \Magento\Framework\Model\Resource\Db\Collection\AbstractCollection */
        $collection->addFieldToSelect('*');

        return $collection->getItems();
    }

    /**
     * Get collection of objects for eav type
     *
     * @param array $fixture
     * @return \Magento\Framework\Object[]
     */
    protected function eavCollection(array $fixture)
    {
        $collection = $fixture['collection'];
        $collection = $this->objectManager->create($collection);
        /** @var $collection \Magento\Eav\Model\Entity\Collection\AbstractCollection */
        $collection->addAttributeToSelect('*');
        if (isset($fixture['product_type'])) {
            $collection->addAttributeToFilter('type_id', $fixture['product_type']);
        }

        return $collection->getItems();
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
