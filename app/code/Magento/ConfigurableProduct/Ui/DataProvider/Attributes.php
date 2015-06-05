<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Ui\DataProvider;

use Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory;

class Attributes extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var \Magento\Catalog\Model\Resource\Product\Attribute\Collection
     */
    protected $collection;

    /**
     * @param \Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory $collectionFactory
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->collection->addVisibleFilter(true);
    }

    /**
     * @return \Magento\Catalog\Model\Resource\Product\Attribute\Collection
     */
    protected function getCollection()
    {
        return $this->collection;
    }
}
