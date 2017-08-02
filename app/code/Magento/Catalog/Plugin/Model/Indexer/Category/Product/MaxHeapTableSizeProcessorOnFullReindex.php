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
 * @deprecated 2.2.0
 * @since 2.0.0
 */
class MaxHeapTableSizeProcessorOnFullReindex
{
    /**
     * @var MaxHeapTableSizeProcessor
     * @since 2.0.0
     */
    protected $maxHeapTableSizeProcessor;

    /**
     * @param MaxHeapTableSizeProcessor $maxHeapTableSizeProcessor
     * @param LoggerInterface $logger
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
