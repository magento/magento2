<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AsynchronousOperations\Ui\Component\Operation;

/**
 * Class DataProvider
 * @since 2.2.0
 */
class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var \Magento\AsynchronousOperations\Model\ResourceModel\Bulk\Collection
     * @since 2.2.0
     */
    protected $collection;

    /**
     * @var \Magento\AsynchronousOperations\Model\Operation\Details
     * @since 2.2.0
     */
    private $operationDetails;

    /**
     * @var \Magento\Framework\App\RequestInterface $request,
     * @since 2.2.0
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
     * @since 2.2.0
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
     * @since 2.2.0
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
     * @since 2.2.0
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
     * @since 2.2.0
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
