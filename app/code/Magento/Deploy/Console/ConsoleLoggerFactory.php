<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Console;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Setup\Model\ObjectManagerProvider;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Factory class for @see \Magento\Deploy\Console\ConsoleLogger
 *
 * This factory can be requested only in CLI command classes constructors
 * @since 2.2.0
 */
class ConsoleLoggerFactory
{
    /**
     * Object manager instance
     *
     * @var ObjectManagerProvider
     * @since 2.2.0
     */
    private $objectManagerProvider;

    /**
     * Type of logger instance to create
     *
     * @var string
     * @since 2.2.0
     */
    private $type;

    /**
     * ConsoleLoggerFactory constructor
     *
     * @param ObjectManagerProvider $objectManagerProvider
     * @param string $type
     * @since 2.2.0
     */
    public function __construct(ObjectManagerProvider $objectManagerProvider, $type = ConsoleLogger::class)
    {
        $this->objectManagerProvider = $objectManagerProvider;
        $this->type = $type;
    }

    /**
     * Create new logger instance
     *
     * @param OutputInterface $output
     * @param int $verbose
     * @return ConsoleLogger
     * @throws LocalizedException
     * @since 2.2.0
     */
    public function getLogger(OutputInterface $output, $verbose)
    {
        $output->setVerbosity($verbose);
        $logger = $this->objectManagerProvider->get()->create($this->type, ['output' => $output]);
        if (!$logger instanceof LoggerInterface) {
            throw new LocalizedException(
                new Phrase("Wrong logger interface specified.")
            );
        }
        return $logger;
    }
}
