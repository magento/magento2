<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Console\Command\Deploy;

use Magento\Deploy\Console\InputValidator;
use Magento\Deploy\Console\ConsoleLoggerFactory;
use Magento\Deploy\Console\Command\DeployStaticOptions as Options;
use Magento\Framework\App\State;
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
 * Deploy static content command
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StaticContentCommand extends Command
{
    /**
     * Default language value
     */
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
     * @var \Magento\Framework\App\State
     */
    private $appState;

    /**
     * StaticContentCommand constructor
     *
     * @param InputValidator        $inputValidator
     * @param ConsoleLoggerFactory  $consoleLoggerFactory
     * @param Options               $options
     * @param ObjectManagerProvider $objectManagerProvider
     */
    public function __construct(
        InputValidator $inputValidator,
        ConsoleLoggerFactory $consoleLoggerFactory,
        Options $options,
        ObjectManagerProvider $objectManagerProvider
    ) {
        $this->inputValidator = $inputValidator;
        $this->consoleLoggerFactory = $consoleLoggerFactory;
        $this->options = $options;
        $this->objectManager = $objectManagerProvider->get();

        parent::__construct();
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     * @throws \InvalidArgumentException
     * @throws LocalizedException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $time = microtime(true);

        if (!$input->getOption(Options::FORCE_RUN) && $this->getAppState()->getMode() !== State::MODE_PRODUCTION) {
            throw new LocalizedException(
                __(
                    'NOTE: Manual static content deployment is not required in "default" and "developer" modes.'
                    . PHP_EOL . 'In "default" and "developer" modes static contents are being deployed '
                    . 'automatically on demand.'
                    . PHP_EOL . 'If you still want to deploy in these modes, use -f option: '
                    . "'bin/magento setup:deploy:static-content -f'"
                )
            );
        }

        $this->inputValidator->validate($input);

        $options = $input->getOptions();
        $options[Options::LANGUAGE] = $input->getArgument(Options::LANGUAGES_ARGUMENT) ?: ['all'];

        $verbose = $output->getVerbosity() > 1 ? $output->getVerbosity() : OutputInterface::VERBOSITY_VERBOSE;

        $logger = $this->consoleLoggerFactory->getLogger($output, $verbose);
        $logger->alert(PHP_EOL . "Deploy using {$options[Options::STRATEGY]} strategy");

        $this->mockCache();

        /** @var DeployStaticContent $deployService */
        $deployService = $this->objectManager->create(DeployStaticContent::class, [
            'logger' => $logger
        ]);

        $deployService->deploy($options);

        $logger->alert(PHP_EOL . "Execution time: " . (microtime(true) - $time));

        return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
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

    /**
     * @return State
     */
    private function getAppState()
    {
        if (null === $this->appState) {
            $this->appState = $this->objectManager->get(State::class);
        }
        return $this->appState;
    }
}
