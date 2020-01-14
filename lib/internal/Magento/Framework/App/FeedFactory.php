<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App;

use Magento\Framework\ObjectManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Feed factory
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
     * {@inheritdoc}
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
            $this->logger->error($e->getMessage());
            throw new \Magento\Framework\Exception\RuntimeException(
                new \Magento\Framework\Phrase('There has been an error with import'),
                $e
            );
        }
    }
}
