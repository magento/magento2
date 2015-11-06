<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\Client;

use Magento\Framework\ObjectManagerInterface;
use Magento\AdvancedSearch\Model\Client\FactoryInterface;

class ElasticsearchFactory implements FactoryInterface
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
    public function create(array $options = [])
    {
        return $this->objectManager->create(
            'Magento\Elasticsearch\Model\Client\Elasticsearch',
            ['options' => $options]
        );
    }
}
