<?php
namespace Magento\BundleGraphQl\Model\Resolver\Products\DataProvider\Product\Option;

/**
 * Factory class bundle product option collection
 */
class CollectionFactory extends \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
{
    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function create(array $data = [])
    {
        $collection = $this->_objectManager->create($this->_instanceName, $data);
        $collection->setFlag('product_children', true);
        return $collection;
    }
}
