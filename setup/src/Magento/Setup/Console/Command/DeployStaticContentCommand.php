<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Console\Command;

use InvalidArgumentException;
use Magento\Deploy\Console\ConsoleLoggerFactory;
use Magento\Deploy\Console\DeployStaticOptions as Options;
use Magento\Deploy\Console\InputValidator;
use Magento\Deploy\Service\DeployStaticContent;
use Magento\Framework\App\Cache;
use Magento\Framework\App\Cache\Type\Dummy as DummyCache;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\State;
use Magento\Framework\Console\Cli;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\ObjectManagerInterface;
use Magento\Setup\Exception;
use Magento\Setup\Model\ObjectManagerProvider;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Deploy static content command
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DeployStaticContentCommand extends Command
{
    const DEFAULT_LANGUAGE_VALUE = 'en_US';

    /**
     * @var InputValidator
     */
    private $inputValidator;

    /**
     * @var ConsoleLoggerFactory
     */
    private $consoleLoggerFactory;

    /**
     * @var Options
     */
    private $options;

    /**
     * Object manager to create various objects
     *
     * @var ObjectManagerInterface
     *
     */
    private $objectManager;

    /**
     * @var State
     */
    private $appState;
    /**
     * @var DirectoryList
     */
    private $directoryList;
    /**
     * @var File
     */
    private $driverFile;

    /**
     * StaticContentCommand constructor
     *
     * @param InputValidator $inputValidator
     * @param ConsoleLoggerFactory $consoleLoggerFactory
     * @param Options $options
     * @param ObjectManagerProvider $objectManagerProvider
     * @param DirectoryList $directoryList
     * @param File $driverFile
     * @throws Exception
     */
    public function __construct(
        InputValidator $inputValidator,
        ConsoleLoggerFactory $consoleLoggerFactory,
        Options $options,
        ObjectManagerProvider $objectManagerProvider,
        DirectoryList $directoryList = null,
        File $driverFile = null
    ) {
        $this->inputValidator = $inputValidator;
        $this->consoleLoggerFactory = $consoleLoggerFactory;
        $this->options = $options;
        $this->objectManager = $objectManagerProvider->get();

        parent::__construct();
        $this->directoryList = $directoryList ?: ObjectManager::getInstance()
            ->get(DirectoryList::class);
        $this->driverFile = $driverFile ?: ObjectManager::getInstance()
            ->get(File::class);
    }

    /**
     * Configuration for static content deploy
     *
     * @throws InvalidArgumentException
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function configure()
    {
        $this->setName('setup:static-content:deploy')
            ->setDescription('Deploys static view files')
            ->setDefinition($this->options->getOptionsList());

        parent::configure();
    }

    /**
     * @inheritdoc
     * @throws InvalidArgumentException
     * @throws LocalizedException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $time = microtime(true);

        if (!$input->getOption(Options::FORCE_RUN)
            && $this->getAppState()->getMode() !== State::MODE_PRODUCTION
        ) {
            throw new LocalizedException(
                __(
                    'NOTE: Manual static content deployment is not required in "default" and "developer" modes.'
                    . PHP_EOL . 'In "default" and "developer" modes static contents are being deployed '
                    . 'automatically on demand.'
                    . PHP_EOL . 'If you still want to deploy in these modes, use -f option: '
                    . "'bin/magento setup:static-content:deploy -f'"
                )
            );
        }

        $this->inputValidator->validate($input);

        $options = $input->getOptions();
        $options[Options::LANGUAGE] = $input->getArgument(Options::LANGUAGES_ARGUMENT) ?: ['all'];
        $refreshOnly = isset($options[Options::REFRESH_CONTENT_VERSION_ONLY])
            && $options[Options::REFRESH_CONTENT_VERSION_ONLY];

        $verbose = $output->getVerbosity() > 1 ? $output->getVerbosity() : OutputInterface::VERBOSITY_VERBOSE;

        $logger = $this->consoleLoggerFactory->getLogger($output, $verbose);
        if (!$refreshOnly) {
            $logger->notice(PHP_EOL . "Deploy using {$options[Options::STRATEGY]} strategy");
        }

        $this->mockCache();

        /** @var DeployStaticContent $deployService */
        $deployService = $this->objectManager->create(
            DeployStaticContent::class,
            ['logger' => $logger]
        );

        if ($this->isDeletePreviousDeploy($options)) {
            $logger->warning("Erasing previous static files...");
            $this->cleanupStaticDirectory();
        }

        $deployService->deploy($options);

        if (!$refreshOnly) {
            $logger->notice(PHP_EOL . "Execution time: " . (microtime(true) - $time));
        }

        return Cli::RETURN_SUCCESS;
    }

    /**
     * Mock Cache class with dummy implementation
     *
     * @return void
     */
    private function mockCache()
    {
        $this->objectManager->configure(
            [
                'preferences' => [
                    Cache::class => DummyCache::class
                ]
            ]
        );
    }

    /**
     * Gets App State
     *
     * @return State
     */
    private function getAppState()
    {
        if (null === $this->appState) {
            $this->appState = $this->objectManager->get(State::class);
        }
        return $this->appState;
    }

    /**
     * Checks if need to refresh only version.
     *
     * @param array $options
     * @return bool
     */
    private function isDeletePreviousDeploy(array $options)
    {
        return isset($options[$this->options->getDeletePreviousFilesKey()])
            && $options[$this->options->getDeletePreviousFilesKey()];
    }

    /**
     * Cleanup directory with static view files.
     *
     * @throws FileSystemException
     */
    private function cleanupStaticDirectory(): void
    {
        $excludePatterns = ['#.htaccess#'];
        $directoryPath = $this->directoryList->getPath(DirectoryList::STATIC_VIEW);
        if ($this->driverFile->isExists($directoryPath)) {
            $files = $this->driverFile->readDirectory($directoryPath);
            foreach ($files as $file) {
                foreach ($excludePatterns as $pattern) {
                    if (preg_match($pattern, $file)) {
                        continue 2;
                    }
                }
                if ($this->driverFile->isFile($file)) {
                    $this->driverFile->deleteFile($file);
                } else {
                    $this->driverFile->deleteDirectory($file);
                }
            }
        }
    }
}
