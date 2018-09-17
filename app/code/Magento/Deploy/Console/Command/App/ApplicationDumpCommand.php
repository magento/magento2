<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Console\Command\App;

use Magento\Framework\App\Config\ConfigSourceInterface;
use Magento\Framework\App\DeploymentConfig\Writer;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Console\Cli;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command for dump application state
 */
class ApplicationDumpCommand extends Command
{
    /**
     * @var Writer
     */
    private $writer;

    /**
     * @var ConfigSourceInterface[]
     */
    private $sources;

    /**
     * ApplicationDumpCommand constructor.
     *
     * @param Writer $writer
     * @param array $sources
     */
    public function __construct(
        Writer $writer,
        array $sources
    ) {
        parent::__construct();
        $this->writer = $writer;
        $this->sources = $sources;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('app:config:dump');
        $this->setDescription('Create dump of application');
        parent::configure();
    }

    /**
     * Dump Application
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return boolean
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dump = [];
        $comments = [];
        foreach ($this->sources as $sourceData) {
            /** @var ConfigSourceInterface $source */
            $source = $sourceData['source'];
            $namespace = $sourceData['namespace'];
            $dump[$namespace] = $source->get();
            if (!empty($sourceData['comment'])) {
                $comments[$namespace] = is_string($sourceData['comment'])
                    ? $sourceData['comment']
                    : $sourceData['comment']->get();
            }
        }
        $this->writer
            ->saveConfig(
                [ConfigFilePool::APP_CONFIG => $dump],
                true,
                ConfigFilePool::LOCAL,
                $comments
            );
        if (!empty($comments)) {
            $output->writeln($comments);
        }
        $output->writeln('<info>Done.</info>');
        return Cli::RETURN_SUCCESS;
    }
}
