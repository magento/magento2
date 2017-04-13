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
    protected $dataCollectionFactory;

    /**
     * ImagesConfigFactory constructor.
     *
     * @param CollectionFactory $dataCollectionFactory
     */
    public function __construct(CollectionFactory $dataCollectionFactory)
    {
        $this->dataCollectionFactory = $dataCollectionFactory;
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
        $collection = $this->dataCollectionFactory->create($data);
        array_map(function($imageConfig) use ($collection) {
            $collection->addItem(new DataObject($imageConfig));
        }, $imagesConfig);

        return $collection;
    }
}
