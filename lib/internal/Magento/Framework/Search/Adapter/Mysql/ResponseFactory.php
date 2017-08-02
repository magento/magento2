<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Adapter\Mysql;

/**
 * Response Factory
 * @since 2.0.0
 */
class ResponseFactory
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     * @since 2.0.0
     */
    protected $objectManager;

    /**
     * Document Factory
     *
     * @var DocumentFactory
     * @since 2.0.0
     */
    protected $documentFactory;

    /**
     * Aggregation Factory
     *
     * @var AggregationFactory
     * @since 2.0.0
     */
    protected $aggregationFactory;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param DocumentFactory $documentFactory
     * @param AggregationFactory $aggregationFactory
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
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
     * @param mixed $rawResponse
     * @return \Magento\Framework\Search\Response\QueryResponse
     * @since 2.0.0
     */
    public function create($rawResponse)
    {
        $documents = [];
        foreach ($rawResponse['documents'] as $rawDocument) {
            /** @var \Magento\Framework\Api\Search\Document[] $documents */
            $documents[] = $this->documentFactory->create($rawDocument);
        }
        /** @var \Magento\Framework\Search\Response\Aggregation $aggregations */
        $aggregations = $this->aggregationFactory->create($rawResponse['aggregations']);
        return $this->objectManager->create(
            \Magento\Framework\Search\Response\QueryResponse::class,
            [
                'documents' => $documents,
                'aggregations' => $aggregations
            ]
        );
    }
}
