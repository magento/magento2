<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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
     * @var Collection
     */
    protected $collection;

    /**
     * @var DataProvider\DataLoader
     */
    protected $dataLoader;

    /**
     * @var DataProvider\MetadataLoader
     */
    private $metadataLoader;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param DataProvider\DataLoader $dataLoader
     * @param DataProvider\MetadataLoader $metadataLoader
     * @param CollectionFactory $configCollectionFactory
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        DataProvider\DataLoader $dataLoader,
        DataProvider\MetadataLoader $metadataLoader,
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
        $this->dataLoader = $dataLoader;
        $this->metadataLoader = $metadataLoader;

        $this->collection = $configCollectionFactory->create();

        $this->meta = array_merge($this->meta, $this->metadataLoader->getData());
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }

        $this->loadedData = $this->dataLoader->getData();
        return $this->loadedData;
    }
}
