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
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command for dump application state
 */
class ApplicationDumpCommand extends Command
{
    const INPUT_CONFIG_TYPES = 'config-types';

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
        ?Hash $configHash = null
    ) {
        $this->writer = $writer;
        $this->sources = $sources;
        $this->configHash = $configHash ?: ObjectManager::getInstance()->get(Hash::class);
        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('app:config:dump');
        $this->setDescription('Create dump of application');

        $configTypes = array_unique(array_column($this->sources, 'namespace'));
        $this->addArgument(
            self::INPUT_CONFIG_TYPES,
            InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
            sprintf('Space-separated list of config types or omit to dump all [%s]', implode(', ', $configTypes))
        );
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
        $this->groupSourcesByPool();
        $dumpedTypes = [];
        foreach ($this->sources as $pool => $sources) {
            $dump = [];
            $comments = [];
            foreach ($sources as $sourceData) {
                if ($this->skipDump($input, $sourceData)) {
                    continue;
                }
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
            $dumpedTypes = array_unique($dumpedTypes + array_keys($dump));
            if (!empty($comments)) {
                $output->writeln($comments);
            }
        }

        if (!$dumpedTypes) {
            $output->writeln('<error>Nothing dumped. Check the config types specified and try again');
            return Cli::RETURN_FAILURE;
        }

        // Generate and save new hash of deployment configuration.
        $this->configHash->regenerate();

        $output->writeln(sprintf('<info>Done. Config types dumped: %s</info>', implode(', ', $dumpedTypes)));
        return Cli::RETURN_SUCCESS;
    }

    /**
     * Groups sources by theirs pool.
     *
     * If source doesn't have pool option puts him into APP_CONFIG pool.
     *
     * @return void
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

    /**
     * Check whether the dump source should be skipped
     *
     * @param InputInterface $input
     * @param array $sourceData
     * @return bool
     */
    private function skipDump(InputInterface $input, array $sourceData): bool
    {
        $allowedTypes = $input->getArgument(self::INPUT_CONFIG_TYPES);
        if ($allowedTypes && !in_array($sourceData['namespace'], $allowedTypes)) {
            return true;
        }
        return false;
    }
}
