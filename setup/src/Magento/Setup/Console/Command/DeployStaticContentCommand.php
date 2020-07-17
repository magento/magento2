<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Console\Command;

use Magento\Deploy\Console\InputValidator;
use Magento\Deploy\Console\ConsoleLoggerFactory;
use Magento\Deploy\Console\DeployStaticOptions as Options;
use Magento\Framework\App\State;
use Magento\Framework\Console\Cli;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Setup\Model\ObjectManagerProvider;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\Cache;
use Magento\Framework\App\Cache\Type\Dummy as DummyCache;
use Magento\Framework\Exception\LocalizedException;
use Magento\Deploy\Service\DeployStaticContent;

/**
 * Command to perform static content deploy
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DeployStaticContentCommand extends Command
{
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
     * StaticContentCommand constructor
     *
     * @param InputValidator $inputValidator
     * @param ConsoleLoggerFactory $consoleLoggerFactory
     * @param Options $options
     * @param ObjectManagerProvider $objectManagerProvider
     * @param State $appState
     */
    public function __construct(
        InputValidator $inputValidator,
        ConsoleLoggerFactory $consoleLoggerFactory,
        Options $options,
        ObjectManagerProvider $objectManagerProvider,
        State $appState
    ) {
        $this->inputValidator = $inputValidator;
        $this->consoleLoggerFactory = $consoleLoggerFactory;
        $this->options = $options;
        $this->objectManager = $objectManagerProvider->get();
        $this->appState = $appState;

        parent::__construct();
    }

    /**
     * @inheritdoc
     * @throws \InvalidArgumentException
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
     * @throws \InvalidArgumentException
     * @throws LocalizedException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $time = microtime(true);

        $this->checkAppMode($input);
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

        $exitCode = Cli::RETURN_SUCCESS;
        try {
            /** @var DeployStaticContent $deployService */
            $deployService = $this->objectManager->create(DeployStaticContent::class, [
                'logger' => $logger
            ]);
            $deployService->deploy($options);
        } catch (\Throwable $e) {
            $logger->error('Error happened during deploy process: ' . $e->getMessage());
            $exitCode = Cli::RETURN_FAILURE;
        }

        if (!$refreshOnly) {
            $logLevel = $exitCode === Cli::RETURN_SUCCESS ? LogLevel::NOTICE : LogLevel::WARNING;
            $logger->log($logLevel, PHP_EOL . 'Execution time: ' . (microtime(true) - $time));
        }

        return $exitCode;
    }

    /**
     * Check application mode
     *
     * @param InputInterface $input
     * @throws LocalizedException
     */
    private function checkAppMode(InputInterface $input): void
    {
        if (!$input->getOption(Options::FORCE_RUN) && $this->appState->getMode() !== State::MODE_PRODUCTION) {
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
    }

    /**
     * Mock Cache class with dummy implementation
     *
     * @return void
     */
    private function mockCache()
    {
        $this->objectManager->configure([
            'preferences' => [
                Cache::class => DummyCache::class
            ]
        ]);
    }
}
