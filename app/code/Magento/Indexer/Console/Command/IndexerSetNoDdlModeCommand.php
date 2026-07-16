<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Indexer\Console\Command;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\Console\Cli;
use Magento\Indexer\Model\Indexer;
use Magento\Indexer\Model\IndexerFactory;
use Magento\Framework\Indexer\NoDdlModeInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to enable, disable, or show the status of DDL-free full reindex for a given indexer
 */
class IndexerSetNoDdlModeCommand extends Command
{
    private const ARG_INDEXER = 'indexer';
    private const ARG_MODE = 'mode';
    private const MODE_ENABLE = 'enable';
    private const MODE_DISABLE = 'disable';
    private const MODE_STATUS = 'status';

    /**
     * @var IndexerFactory
     */
    private $indexerFactory;

    /**
     * @var NoDdlModeInterface
     */
    private $noDdlMode;

    /**
     * @var \Magento\Framework\App\Config\ConfigResource\ConfigInterface
     */
    private $configWriter;

    /**
     * @var TypeListInterface
     */
    private $cacheTypeList;

    /**
     * @param IndexerFactory $indexerFactory
     * @param NoDdlModeInterface $noDdlMode
     * @param \Magento\Framework\App\Config\ConfigResource\ConfigInterface $configWriter
     * @param TypeListInterface $cacheTypeList
     * @param string|null $name
     */
    public function __construct(
        IndexerFactory $indexerFactory,
        NoDdlModeInterface $noDdlMode,
        \Magento\Framework\App\Config\ConfigResource\ConfigInterface $configWriter,
        TypeListInterface $cacheTypeList,
        ?string $name = null
    ) {
        $this->indexerFactory = $indexerFactory;
        $this->noDdlMode = $noDdlMode;
        $this->configWriter = $configWriter;
        $this->cacheTypeList = $cacheTypeList;
        parent::__construct($name);
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->applyConfiguration();
        parent::configure();
    }

    /**
     * Set the command name, description, and argument definition
     *
     * @return void
     */
    private function applyConfiguration(): void
    {
        $this->setName('indexer:set-no-ddl-mode')
            ->setDescription(
                'Enable, disable, or show the status of DDL-free (flag-based) table swapping for a full reindex'
            )
            ->setDefinition([
                new InputArgument(self::ARG_INDEXER, InputArgument::REQUIRED, 'Indexer ID'),
                new InputArgument(self::ARG_MODE, InputArgument::OPTIONAL, 'enable|disable|status', self::MODE_STATUS),
            ]);
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        return $this->runCommand($input, $output);
    }

    /**
     * Run the command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    private function runCommand(InputInterface $input, OutputInterface $output): int
    {
        $indexerId = (string)$input->getArgument(self::ARG_INDEXER);
        $mode = (string)$input->getArgument(self::ARG_MODE);

        try {
            $indexer = $this->loadIndexer($indexerId);
        } catch (\InvalidArgumentException $e) {
            $output->writeln($e->getMessage());
            return Cli::RETURN_FAILURE;
        }

        switch ($mode) {
            case self::MODE_ENABLE:
                if (!$indexer->isScheduled()) {
                    $output->writeln(
                        'No-DDL reindex mode can only be enabled for indexers using "Update on Schedule" mode.'
                    );
                    return Cli::RETURN_FAILURE;
                }
                $this->setEnabled($indexerId, true);
                $output->writeln("No-DDL reindex mode enabled for '{$indexer->getTitle()}'.");
                return Cli::RETURN_SUCCESS;
            case self::MODE_DISABLE:
                $this->setEnabled($indexerId, false);
                $output->writeln("No-DDL reindex mode disabled for '{$indexer->getTitle()}'.");
                return Cli::RETURN_SUCCESS;
            case self::MODE_STATUS:
                $enabled = $this->noDdlMode->isEnabled($indexerId);
                $output->writeln(
                    "No-DDL reindex mode for '{$indexer->getTitle()}' is currently "
                    . ($enabled ? 'enabled' : 'disabled') . '.'
                );
                return Cli::RETURN_SUCCESS;
            default:
                $output->writeln('Invalid mode "' . $mode . '". Accepted values: enable, disable, status.');
                return Cli::RETURN_FAILURE;
        }
    }

    /**
     * Load an indexer model by ID
     *
     * @param string $indexerId
     * @return Indexer
     * @throws \InvalidArgumentException
     */
    private function loadIndexer(string $indexerId): Indexer
    {
        $indexer = $this->indexerFactory->create();
        $indexer->load($indexerId);

        return $indexer;
    }

    /**
     * Persist the flag and invalidate the given indexer
     *
     * @param string $indexerId
     * @param bool $value
     * @return void
     */
    private function setEnabled(string $indexerId, bool $value): void
    {
        $this->configWriter->saveConfig(
            sprintf(NoDdlModeInterface::XML_PATH_NO_DDL_REINDEX_MASK, $indexerId),
            (int)$value
        );
        $this->cacheTypeList->cleanType('config');
        $this->loadIndexer($indexerId)->invalidate();
    }
}
