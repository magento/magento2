<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\Search\Adapter\Mysql;

use Magento\Framework\Search\QueryResponse;

/**
 * Response Factory
 */
class ResponseFactory
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManager
     */
    protected $objectManager;

    /**
     * Document Factory
     *
     * @var DocumentFactory
     */
    protected $documentFactory;

    /**
     * Aggregation Factory
     *
     * @var AggregationFactory
     */
    protected $aggregationFactory;

    /**
     * @param \Magento\Framework\ObjectManager $objectManager
     * @param DocumentFactory $documentFactory
     * @param AggregationFactory $aggregationFactory
     */
    public function __construct(
        \Magento\Framework\ObjectManager $objectManager,
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
     * @return QueryResponse
     */
    public function create($rawResponse)
    {
        $rawResponse = $this->prepareData($rawResponse);
        $documents = array();
        foreach ($rawResponse['documents'] as $rawDocument) {
            /** @var \Magento\Framework\Search\Document[] $documents */
            $documents[] = $this->documentFactory->create($rawDocument);
        }
        /** @var \Magento\Framework\Search\Aggregation $aggregations */
        $aggregations = $this->aggregationFactory->create($rawResponse['aggregations']);
        return $this->objectManager->create(
            '\Magento\Framework\Search\QueryResponse',
            [
                'documents' => $documents,
                'aggregations' => $aggregations
            ]
        );
    }

    /**
     * Preparing
     *
     * @param array $rawResponse
     * @return array
     */
    private function prepareData(array $rawResponse)
    {
        $preparedResponse = [];
        $preparedResponse['documents'] = $this->prepareDocuments($rawResponse['documents']);
        $preparedResponse['aggregations'] = $this->prepareAggregations($rawResponse['aggregations']);
        return $preparedResponse;
    }

    /**
     * Prepare Documents
     *
     * @param array $rawDocumentList
     * @return array
     */
    private function prepareDocuments(array $rawDocumentList)
    {
        $documentList = [];
        foreach ($rawDocumentList as $document) {
            $documentFieldList = [];
            foreach ($document as $name => $values) {
                $documentFieldList[] = [
                    'name' => $name,
                    'value' => $values
                ];
            }
            $documentList[] = $documentFieldList;
        }
        return $documentList;
    }

    /**
     * Prepare Aggregations
     *
     * @param array $rawAggregations
     * @return array
     */
    private function prepareAggregations(array $rawAggregations)
    {
        return $rawAggregations; // Prepare aggregations here
    }
}
