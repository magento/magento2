<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AsynchronousOperations\Ui\Component\Operation;

/**
 * Class DataProvider
 */
class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var \Magento\AsynchronousOperations\Model\ResourceModel\Bulk\Collection
     */
    protected $collection;

    /**
     * @var \Magento\AsynchronousOperations\Model\Operation\Details
     */
    private $operationDetails;

    /**
     * @var \Magento\Framework\App\RequestInterface $request,
     */
    private $request;

    /**
     * DataProvider constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param \Magento\AsynchronousOperations\Model\ResourceModel\Bulk\CollectionFactory $bulkCollectionFactory
     * @param \Magento\AsynchronousOperations\Model\Operation\Details $operationDetails
     * @param \Magento\Framework\App\RequestInterface $request
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \Magento\AsynchronousOperations\Model\ResourceModel\Bulk\CollectionFactory $bulkCollectionFactory,
        \Magento\AsynchronousOperations\Model\Operation\Details $operationDetails,
        \Magento\Framework\App\RequestInterface $request,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $bulkCollectionFactory->create();
        $this->operationDetails = $operationDetails;
        $this->request = $request;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->meta = $this->prepareMeta($this->meta);
    }

    /**
     * Human readable summary for bulk
     *
     * @param array $operationDetails structure is implied as getOperationDetails() result
     * @return string
     */
    private function getSummaryReport($operationDetails)
    {
        if (0 == $operationDetails['operations_successful'] && 0 == $operationDetails['operations_failed']) {
            return __('Pending, in queue...');
        }

        $summaryReport = __('%1 items selected for mass update', $operationDetails['operations_total'])->__toString();
        if ($operationDetails['operations_successful'] > 0) {
            $summaryReport .= __(', %1 successfully updated', $operationDetails['operations_successful']);
        }

        if ($operationDetails['operations_failed'] > 0) {
            $summaryReport .= __(', %1 failed to update', $operationDetails['operations_failed']);
        }

        return $summaryReport;
    }

    /**
     * Bulk summary with operation statistics
     *
     * @return array
     */
    public function getData()
    {
        $data = [];
        $items = $this->collection->getItems();
        if (count($items) == 0) {
            return $data;
        }
        $bulk = array_shift($items);
        /** @var \Magento\AsynchronousOperations\Api\Data\BulkSummaryInterface $bulk */
        $data = $bulk->getData();
        $operationDetails = $this->operationDetails->getDetails($data['uuid']);
        $data['summary'] = $this->getSummaryReport($operationDetails);
        $data = array_merge($data, $operationDetails);

        return [$bulk->getBulkId() => $data];
    }

    /**
     * Prepares Meta
     *
     * @param array $meta
     * @return array
     */
    public function prepareMeta($meta)
    {
        $requestId = $this->request->getParam($this->requestFieldName);
        $operationDetails = $this->operationDetails->getDetails($requestId);

        if (isset($operationDetails['failed_retriable']) && !$operationDetails['failed_retriable']) {
            $meta['retriable_operations']['arguments']['data']['disabled'] = true;
        }

        if (isset($operationDetails['failed_not_retriable']) && !$operationDetails['failed_not_retriable']) {
            $meta['failed_operations']['arguments']['data']['disabled'] = true;
        }

        return $meta;
    }
}
