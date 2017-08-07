<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Model\Synonym;

use Magento\Search\Model\ResourceModel\SynonymGroup\CollectionFactory;
use Magento\Framework\View\Element\UiComponent\DataProvider\FilterPool;

/**
 * Class DataProvider
 * @since 2.1.0
 */
class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var \Magento\Search\Model\ResourceModel\SynonymGroup\Collection
     * @since 2.1.0
     */
    protected $collection;

    /**
     * @var FilterPool
     * @since 2.1.0
     */
    protected $filterPool;

    /**
     * @var array
     * @since 2.1.0
     */
    protected $loadedData;

    /**
     * Constructor
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $blockCollectionFactory
     * @param FilterPool $filterPool
     * @param array $meta
     * @param array $data
     * @since 2.1.0
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $blockCollectionFactory,
        FilterPool $filterPool,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $blockCollectionFactory->create();
        $this->filterPool = $filterPool;
    }

    /**
     * Get data
     *
     * @return array
     * @since 2.1.0
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        /** @var \Magento\Search\Model\SynonymGroup $synGroup */
        foreach ($items as $synGroup) {
            // Set the virtual 'scope_id' column to appropriate value.
            // This is necessary to display the correct selection set
            // in 'scope' field on the GUI.
            $synGroup->setScope();
            $this->loadedData[$synGroup->getId()] = $synGroup->getData();
        }
        return $this->loadedData;
    }
}
