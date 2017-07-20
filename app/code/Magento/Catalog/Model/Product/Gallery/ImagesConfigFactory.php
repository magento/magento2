<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Gallery;

use Magento\Framework\Data\CollectionFactory;
use Magento\Framework\DataObject;

class ImagesConfigFactory implements ImagesConfigFactoryInterface
{
    /**
     * @var CollectionFactory
     */
    protected $dataCollectionFactory;

    /**
     * @param CollectionFactory $dataCollectionFactory
     */
    public function __construct(CollectionFactory $dataCollectionFactory)
    {
        $this->dataCollectionFactory = $dataCollectionFactory;
    }

    /**
     * @inheritdoc
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
