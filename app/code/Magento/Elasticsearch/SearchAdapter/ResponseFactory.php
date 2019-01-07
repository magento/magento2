<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\SearchAdapter;

use Magento\Framework\ObjectManagerInterface;

/**
 * Response Factory
 * @api
 * @since 100.1.0
 */
class ResponseFactory
{
    /**
     * Object Manager instance
     *
     * @var ObjectManagerInterface
     * @since 100.1.0
     */
    protected $objectManager;

    /**
     * Document Factory
     *
     * @var DocumentFactory
     * @since 100.1.0
     */
    protected $documentFactory;

    /**
     * Aggregation Factory
     *
     * @var AggregationFactory
     * @since 100.1.0
     */
    protected $aggregationFactory;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param DocumentFactory $documentFactory
     * @param AggregationFactory $aggregationFactory
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        DocumentFactory $documentFactory,
        AggregationFactory $aggregationFactory
    ) {
        $this->objectManager = $objectManager;
        $this->documentFactory = $documentFactory;
        $this->aggregationFactory = $aggregationFactory;
    }

    /**
     * Create Query Response instance
     *
     * @param array $response
     * @return \Magento\Framework\Search\Response\QueryResponse
     * @since 100.1.0
     */
    public function create($response)
    {
        $documents = [];
        foreach ($response['documents'] as $rawDocument) {
            /** @var \Magento\Framework\Api\Search\Document[] $documents */
            $documents[] = $this->documentFactory->create(
                $rawDocument
            );
        }
        /** @var \Magento\Framework\Search\Response\Aggregation $aggregations */
        $aggregations = $this->aggregationFactory->create($response['aggregations']);
        return $this->objectManager->create(
            \Magento\Framework\Search\Response\QueryResponse::class,
            [
                'documents' => $documents,
                'aggregations' => $aggregations,
            ]
        );
    }
}
