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
        $this->collection = $configCollectionFactory->create();
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

        $this->collection->addPathsFilter($metadata);

        $metadata = array_flip($metadata);

        $items = $this->collection->getItems();
        foreach ($items as $item) {
            /** @var \Magento\Framework\App\Config\Value $item */
            $this->loadedData[1][$metadata[$item->getPath()]] = $item->getValue();
        }

        return $this->loadedData;
    }
}
