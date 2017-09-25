<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

use Magento\Framework\App\FeedFactoryInterface;
use Magento\Framework\ObjectManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Feed factory
 */
class FeedFactory implements FeedFactoryInterface
{
    /**
     * @var FeedProcessorInterface
     */
    private $feedProcessor;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @param ObjectManagerInterface $objectManger
     * @param LoggerInterface $logger
     * @param array $formats
     */
    public function __construct(
        ObjectManagerInterface $objectManger,
        LoggerInterface $logger,
        array $formats
    ) {
        $this->objectManager = $objectManger;
        $this->logger = $logger;
        $this->formats = $formats;
    }

    /**
     * Get a new \Magento\Framework\App\FeedInterface object from a custom array
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
        if (!isset($this->formats[$format])) {
            throw new \Magento\Framework\Exception\InputException(
                __('The format is not supported'),
                $e
            );
        }
        try {
            return $this->objectManager->create(
                $this->formats[$format],
                $data
            );
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new \Magento\Framework\Exception\InputException(
                __('There has been an error with import'),
                $e
            );
        }
    }
}
