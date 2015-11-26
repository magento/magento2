<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Design\Config;

use Magento\Theme\Model\ResourceModel\Design\Config\Collection;
use Magento\Theme\Model\ResourceModel\Design\Config\CollectionFactory;
use Magento\Ui\DataProvider\AbstractDataProvider;

class DataProvider extends AbstractDataProvider
{
    /**
     * @var array
     */
    protected $loadedData;

    /**
     * @var MetadataProvider
     */
    protected $metadataProvider;

    /**
     * @var CollectionFactory
     */
    protected $configCollectionFactory;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param MetadataProvider $metadataProvider
     * @param CollectionFactory $configCollectionFactory
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        MetadataProvider $metadataProvider,
        CollectionFactory $configCollectionFactory,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $meta,
            $data
        );
        $this->metadataProvider = $metadataProvider;
        $this->configCollectionFactory = $configCollectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }

        $metadata = $this->metadataProvider->get();
        array_walk($metadata, function (&$value) {
            $value = $value['path'];
        });

        /** @var Collection $collection */
        $this->collection = $this->configCollectionFactory->create();
        $this->collection->addPathsFilter($metadata);

        $items = $this->collection->getItems();
        /** @var \Magento\Framework\App\Config\Value $item */
        foreach ($items as $item) {
            $key = substr(str_replace('/', '_', $item['path']), 7);
            $this->loadedData[1]['design'][$key] = $item->getValue();
        }

        return $this->loadedData;
    }
}
