<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model;

use Magento\Framework\ObjectManagerInterface;

/**
 * @codeCoverageIgnore
 */
class ElasticsearchFactory implements AdapterFactoryInterface
{
    /**
     * Object manager
     *
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * {@inheritdoc}
     */
    public function createAdapter()
    {
        return $this->objectManager->create('Magento\Elasticsearch\Model\Adapter\Elasticsearch');
    }
}
