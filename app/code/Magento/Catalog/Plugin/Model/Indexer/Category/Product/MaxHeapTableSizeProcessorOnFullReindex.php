<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Plugin\Model\Indexer\Category\Product;

use Magento\Catalog\Model\Indexer\Category\Product\Action\Full;
use Magento\Catalog\Model\ResourceModel\MaxHeapTableSizeProcessor;
use Psr\Log\LoggerInterface;

/**
 * @deprecated 101.0.3
 */
class MaxHeapTableSizeProcessorOnFullReindex
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var MaxHeapTableSizeProcessor
     */
    protected $maxHeapTableSizeProcessor;

    /**
     * @param MaxHeapTableSizeProcessor $maxHeapTableSizeProcessor
     * @param LoggerInterface $logger
     */
    public function __construct(
        MaxHeapTableSizeProcessor $maxHeapTableSizeProcessor,
        LoggerInterface $logger
    ) {
        $this->maxHeapTableSizeProcessor = $maxHeapTableSizeProcessor;
        $this->logger = $logger;
    }

    /**
     * @param Full $subject
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeExecute(Full $subject)
    {
        try {
            $this->maxHeapTableSizeProcessor->set();
        } catch (\Exception $e) {
            $this->logger->error($e);
        }
    }

    /**
     * @param Full $subject
     * @param Full $result
     * @return Full
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute(Full $subject, Full $result)
    {
        try {
            $this->maxHeapTableSizeProcessor->restore();
        } catch (\Exception $e) {
            $this->logger->error($e);
        }
        return $result;
    }
}
