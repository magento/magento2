<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Ui\DataProvider\Customer;

use Magento\Customer\Model\Resource\Customer\Grid\ServiceCollectionFactory as ServiceCollectionFactory;

class CustomerDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * Customer collection
     *
     * @var \Magento\Catalog\Model\Resource\Product\Collection
     */
    protected $collection;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param ServiceCollectionFactory $serviceCollectionFactory
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        ServiceCollectionFactory $serviceCollectionFactory,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $serviceCollectionFactory->create();
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        return $this->getCollection()->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function addOrder($field, $direction)
    {
        $this->getCollection()->setOrder($field, $direction);
    }

    /**
     * Get collection
     *
     * @return \Magento\Customer\Model\Resource\Customer\Collection
     */
    protected function getCollection()
    {
        return $this->collection;
    }
}
