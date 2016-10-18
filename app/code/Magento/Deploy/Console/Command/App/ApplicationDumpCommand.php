<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Console\Command\App;

use Magento\Framework\App\Config\Reader\Source\SourceInterface;
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
     * @var SourceInterface[]
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
        foreach ($this->sources as $sourceData) {
            /** @var SourceInterface $source */
            $source = $sourceData['source'];
            $namespace = $sourceData['namespace'];
            $dump[$namespace] = $source->get();
        }

        $this->writer
            ->saveConfig(
                [ConfigFilePool::APP_CONFIG => $dump],
                true,
                ConfigFilePool::LOCAL
            );
        $output->writeln('<info>Done.</info>');
        return  Cli::RETURN_SUCCESS;
    }
}
