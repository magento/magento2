<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

use Magento\Framework\App\FeedFactory;
use Magento\Framework\App\FeedFactoryInterface;
use Psr\Log\LoggerInterface;

/**
 * Feed factory
 */
class FeedFactory implements FeedFactoryInterface
{
    /**
     * @var \Zend_Feed
     */
    private $feedProcessor;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param \Zend_Feed $feedProcessor
     * @param LoggerInterface $logger
     */
    public function __construct(
        \Zend_Feed $feedProcessor,
        LoggerInterface $logger
    ) {
        $this->feedProcessor = $feedProcessor;
        $this->logger = $logger;
    }

    /**
     * Get a new \Magento\Framework\App\Feed object from a custom array
     *
     * @throws \Magento\Framework\Exception\InputException
     * @param  array  $data
     * @param  string $format
     * @return \Magento\Framework\App\FeedInterface
     */
    public function create(
        array $data, 
        $format = FeedFactoryInterface::DEFAULT_FORMAT
    ) {
        try {
            return $this->feedProcessor->importArray($data, $format);
        } catch (\Zend_Feed_Exception $e) {
            $this->logger->error($e->getMessage());
            throw new \Magento\Framework\Exception\InputException(
                __('There has been an error with import'),
                $e
            );
        }
    }
}
