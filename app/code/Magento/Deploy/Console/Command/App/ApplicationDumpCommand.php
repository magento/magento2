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
 * @since 2.2.0
 */
class ApplicationDumpCommand extends Command
{
    /**
     * @var Writer
     * @since 2.2.0
     */
    private $writer;

    /**
     * @var ConfigSourceInterface[]
     * @since 2.2.0
     */
    private $sources;

    /**
     * @var Hash
     * @since 2.2.0
     */
    private $configHash;

    /**
     * ApplicationDumpCommand constructor
     *
     * @param Writer $writer
     * @param array $sources
     * @param Hash $configHash
     * @since 2.2.0
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
     * @since 2.2.0
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
     * @since 2.2.0
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->groupSourcesByPool();

        foreach ($this->sources as $pool => $sources) {
            $dump = [];
            $comments = [];
            foreach ($sources as $sourceData) {
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
                [$pool => $dump],
                true,
                null,
                $comments
            );
            if (!empty($comments)) {
                $output->writeln($comments);
            }
        }

        // Generate and save new hash of deployment configuration.
        $this->configHash->regenerate();

        $output->writeln('<info>Done.</info>');
        return Cli::RETURN_SUCCESS;
    }

    /**
     * Groups sources by theirs pool.
     *
     * If source doesn't have pool option puts him into APP_CONFIG pool.
     *
     * @return void
     * @since 2.2.0
     */
    private function groupSourcesByPool()
    {
        $sources = [];
        foreach ($this->sources as $sourceData) {
            if (!isset($sourceData['pool'])) {
                $sourceData['pool'] = ConfigFilePool::APP_CONFIG;
            }

            $sources[$sourceData['pool']][] = $sourceData;
        }

        $this->sources = $sources;
    }
}
