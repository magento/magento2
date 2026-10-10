<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Backend\Console\Command;

use Magento\Backend\Model\Cache\WarmupRunner;
use Magento\Framework\Console\Cli;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Standalone CLI: warm full-page cache via HTTP GET without flushing or cleaning.
 */
class CacheWarmupCommand extends Command
{
    public const NAME = 'cache:warmup';

    /**
     * @param WarmupRunner $warmupRunner
     */
    public function __construct(
        private readonly WarmupRunner $warmupRunner
    ) {
        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    protected function configure(): void
    {
        $this->setName(self::NAME)
            ->setDescription('Sends HTTP GET requests to warm full-page cache (see Stores > Configuration > Advanced > Developer > CLI Cache Warmup).');
        parent::configure();
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->warmupRunner->run($output);
        return Cli::RETURN_SUCCESS;
    }
}
