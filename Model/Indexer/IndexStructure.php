<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\Indexer;

use Magento\Framework\Indexer\IndexStructureInterface;
use Magento\Elasticsearch\Model\Adapter\Elasticsearch as ElasticsearchAdapter;

class IndexStructure implements IndexStructureInterface
{
    /**
     * @var ElasticsearchAdapter
     */
    private $adapter;

    /**
     * @param ElasticsearchAdapter $adapter
     */
    public function __construct(
        ElasticsearchAdapter $adapter
    ) {
        $this->adapter = $adapter;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(
        $indexerId,
        array $dimensions = []
    ) {
        $dimension = current($dimensions);
        $storeId = $dimension->getValue();
        $this->adapter->cleanIndex($storeId, $indexerId);
    }

    /**
     * {@inheritdoc}
     *
     */
    public function create(
        $indexerId,
        array $fields,
        array $dimensions = []
    ) {
        $dimension = current($dimensions);
        $storeId = $dimension->getValue();
        $this->adapter->checkIndex($storeId, $indexerId, false);
    }
}
