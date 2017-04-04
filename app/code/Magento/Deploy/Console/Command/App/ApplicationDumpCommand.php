<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Console\Command\App;

use Magento\Deploy\Model\DeploymentConfig\Hash;
use Magento\Framework\App\Config\ConfigSourceInterface;
use Magento\Framework\App\DeploymentConfig\Writer;
use Magento\Framework\App\ObjectManager;
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
     * @var Hash
     */
    private $configHash;

    /**
     * ApplicationDumpCommand constructor
     *
     * @param Writer $writer
     * @param array $sources
     * @param Hash $configHash
     */
    public function __construct(
        Writer $writer,
        array $sources,
        Hash $configHash = null
    ) {
        parent::__construct();
        $this->writer = $writer;
        $this->sources = $sources;
        $this->configHash = $configHash ?: ObjectManager::getInstance()->get(Hash::class);
    }

    /**
     * @inheritdoc
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
        $this->writer->saveConfig(
            [ConfigFilePool::APP_CONFIG => $dump],
            true,
            null,
            $comments
        );
        if (!empty($comments)) {
            $output->writeln($comments);
        }

        // Generate and save new hash of deployment configuration.
        $this->configHash->regenerate();

        $output->writeln('<info>Done.</info>');
        return Cli::RETURN_SUCCESS;
    }
}
