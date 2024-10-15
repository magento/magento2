<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Indexer\Console\Command;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManagerFactory;
use Magento\Framework\Console\Cli;
use Magento\Indexer\Console\Command\IndexerSetDimensionsModeCommand\ModeInputArgument;
use Magento\Indexer\Model\ModeSwitcherInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to set indexer dimensions mode
 */
class IndexerSetDimensionsModeCommand extends AbstractIndexerCommand
{
    public const INPUT_KEY_MODE = 'mode';
    public const INPUT_KEY_INDEXER = 'indexer';
    public const DIMENSION_MODE_NONE = 'none';
    public const XML_PATH_DIMENSIONS_MODE_MASK = 'indexer/%s/dimensions_mode';

    /**
     * @var string
     */
    private $commandName = 'indexer:set-dimensions-mode';

    /**
     * ScopeConfigInterface
     *
     * @var ScopeConfigInterface
     */
    private $configReader;

    /**
     * @var ModeSwitcherInterface[]
     */
    private $dimensionProviders;

    /**
     * @param ObjectManagerFactory $objectManagerFactory
     * @param ScopeConfigInterface $configReader
     * @param ModeSwitcherInterface[] $dimensionSwitchers
     */
    public function __construct(
        ObjectManagerFactory $objectManagerFactory,
        ScopeConfigInterface $configReader,
        array $dimensionSwitchers
    ) {
        $this->configReader = $configReader;
        $this->dimensionProviders = $dimensionSwitchers;
        parent::__construct($objectManagerFactory);
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName($this->commandName)
            ->setDescription('Set Indexer Dimensions Mode')
            ->setDefinition($this->getInputList());
        parent::configure();
    }

    /**
     * @inheritdoc
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $errors = $this->validate($input);
        if ($errors) {
            throw new \InvalidArgumentException(implode(PHP_EOL, $errors));
        }
        $returnValue = Cli::RETURN_SUCCESS;
        /** @var \Magento\Indexer\Model\Indexer $indexer */
        $indexer = $this->getObjectManager()->get(\Magento\Indexer\Model\Indexer::class);
        try {
            $selectedIndexer = (string)$input->getArgument(self::INPUT_KEY_INDEXER);
            if (!$selectedIndexer) {
                $this->showAvailableModes($output);
            } else {
                $indexer->load($selectedIndexer);
                $currentMode = $input->getArgument(self::INPUT_KEY_MODE);
                $configPath = sprintf(self::XML_PATH_DIMENSIONS_MODE_MASK, $selectedIndexer);
                $previousMode = $this->configReader->getValue($configPath) ?: self::DIMENSION_MODE_NONE;
                if ($previousMode !== $currentMode) {
                    /** @var ModeSwitcherInterface $modeSwitcher */
                    $modeSwitcher = $this->dimensionProviders[$selectedIndexer];
                    // Switch dimensions mode
                    $modeSwitcher->switchMode($currentMode, $previousMode);
                    $output->writeln(
                        'Dimensions mode for indexer "' . $indexer->getTitle() . '" was changed from \''
                        . $previousMode . '\' to \'' . $currentMode . '\''
                    );
                } else {
                    $output->writeln('Dimensions mode for indexer "' . $indexer->getTitle() . '" has not been changed');
                }
            }
        } catch (\Exception $e) {
            $output->writeln('"' . $indexer->getTitle() . '" indexer process unknown error:' . PHP_EOL);
            $output->writeln($e->getMessage() . PHP_EOL);
            // we must have an exit code higher than zero to indicate something was wrong
            $returnValue = Cli::RETURN_FAILURE;
        }

        return $returnValue;
    }

    /**
     * Display all available indexers and modes
     *
     * @param OutputInterface $output
     * @return void
     */
    private function showAvailableModes(OutputInterface $output)
    {
        $output->writeln(sprintf('%-50s', 'Indexer') . 'Available modes');
        foreach ($this->dimensionProviders as $indexer => $provider) {
            $availableModes = implode(',', array_keys($provider->getDimensionModes()->getDimensions()));
            $output->writeln(sprintf('%-50s', $indexer) . $availableModes);
        }
    }

    /**
     * Get list of arguments for the command
     *
     * @return InputArgument[]
     */
    private function getInputList(): array
    {
        $dimensionProvidersList = array_keys($this->dimensionProviders);
        $indexerOptionDescription = 'Indexer name [' . implode('|', $dimensionProvidersList) . ']';
        $arguments[] = new InputArgument(
            self::INPUT_KEY_INDEXER,
            InputArgument::OPTIONAL,
            $indexerOptionDescription
        );
        $modeOptionDescriptionClosure = function () {
            $modeOptionDescription = 'Indexer dimension modes' . PHP_EOL;
            foreach ($this->dimensionProviders as $indexer => $provider) {
                $availableModes = implode(',', array_keys($provider->getDimensionModes()->getDimensions()));
                $modeOptionDescription .= sprintf('%-30s ', $indexer) . $availableModes . PHP_EOL;
            }
            return $modeOptionDescription;
        };
        $arguments[] = new ModeInputArgument(
            self::INPUT_KEY_MODE,
            InputArgument::OPTIONAL,
            $modeOptionDescriptionClosure
        );
        return $arguments;
    }

    /**
     * Check if all arguments are provided
     *
     * @param InputInterface $input
     * @return string[]
     */
    private function validate(InputInterface $input): array
    {
        $errors = [];
        $inputIndexer = (string)$input->getArgument(self::INPUT_KEY_INDEXER);
        if ($inputIndexer) {
            $acceptedValues = array_keys($this->dimensionProviders);
            $errors = $this->validateArgument(self::INPUT_KEY_INDEXER, $inputIndexer, $acceptedValues);
            if (!$errors) {
                $inputIndexerDimensionMode = (string)$input->getArgument(self::INPUT_KEY_MODE);
                /** @var ModeSwitcherInterface $modeSwitcher */
                $modeSwitcher = $this->dimensionProviders[$inputIndexer];
                $acceptedValues = array_keys($modeSwitcher->getDimensionModes()->getDimensions());
                $errors = $this->validateArgument(self::INPUT_KEY_MODE, $inputIndexerDimensionMode, $acceptedValues);
            }
        }

        return $errors;
    }

    /**
     * Validate command argument and return errors in case if argument is invalid
     *
     * @param string $inputKey
     * @param string $inputIndexer
     * @param array $acceptedValues
     * @return string[]
     */
    private function validateArgument(string $inputKey, string $inputIndexer, array $acceptedValues): array
    {
        $errors = [];
        $acceptedIndexerValues = ' Accepted values for "<' . $inputKey . '>" are \'' .
            implode(',', $acceptedValues) . '\'';
        if (!$inputIndexer) {
            $errors[] = 'Missing argument "<' . $inputKey . '>".' . $acceptedIndexerValues;
        } elseif (!\in_array($inputIndexer, $acceptedValues)) {
            $errors[] = 'Invalid value for "<' . $inputKey . '>" argument.' . $acceptedIndexerValues;
        }

        return $errors;
    }
}
