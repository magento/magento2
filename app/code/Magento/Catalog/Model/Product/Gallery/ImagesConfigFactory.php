<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Gallery;

use \Magento\Framework\Data\CollectionFactory;
use Magento\Framework\DataObject;

class ImagesConfigFactory implements ImagesConfigFactoryInterface
{
    /**
     * @var \Magento\Framework\Data\CollectionFactory
     */
    protected $_dataCollectionFactory;

    /**
     * ImagesConfigFactory constructor.
     *
     * @param CollectionFactory $_dataCollectionFactory
     */
    public function __construct(CollectionFactory $_dataCollectionFactory)
    {
        $this->_dataCollectionFactory = $_dataCollectionFactory;
    }

    /**
     * create Gallery Images Config Collection from array
     *
     * @param array $imagesConfig
     * @param array $data
     *
     * @return \Magento\Framework\Data\Collection
     */
    public function create(array $imagesConfig, array $data = [])
    {
        /** @var \Magento\Framework\Data\Collection $collection */
        $collection = $this->_dataCollectionFactory->create($data);
        array_map(function($imageConfig) use ($collection) {
            $collection->addItem(new DataObject($imageConfig));
        }, $imagesConfig);

        return $collection;
    }
}
