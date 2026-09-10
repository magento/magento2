<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\App;

use Magento\Framework\ObjectManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Creates a feed of the requested format from the given feed data
 */
class FeedFactory implements FeedFactoryInterface
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var array
     */
    private $formats;

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
     * @inheritdoc
     *
     * @param array $data
     * @param string $format
     * @return FeedInterface
     */
    public function create(array $data, string $format = FeedFactoryInterface::FORMAT_RSS) : FeedInterface
    {
        if (!isset($this->formats[$format])) {
            throw new \Magento\Framework\Exception\InputException(
                new \Magento\Framework\Phrase('The format is not supported')
            );
        }

        if (!is_subclass_of($this->formats[$format], \Magento\Framework\App\FeedInterface::class)) {
            throw new \Magento\Framework\Exception\InputException(
                new \Magento\Framework\Phrase('Wrong format handler type')
            );
        }

        try {
            return $this->objectManager->create(
                $this->formats[$format],
                ['data' => $data]
            );
        } catch (\Exception $e) {
            $this->logger->error(
                'Unable to create a feed of the {feedFormat} format',
                ['feedFormat' => $format, 'exception' => $e]
            );
            throw new \Magento\Framework\Exception\RuntimeException(
                new \Magento\Framework\Phrase('There has been an error with import'),
                $e
            );
        }
    }
}
