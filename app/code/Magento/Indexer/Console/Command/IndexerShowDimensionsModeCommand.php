<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Indexer\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\ObjectManagerFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Console\Cli;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Command to show indexers dimension modes
 */
class IndexerShowDimensionsModeCommand extends AbstractIndexerCommand
{
    private const INPUT_KEY_INDEXER = 'indexer';
    private const DIMENSION_MODE_NONE = 'none';
    private const XML_PATH_DIMENSIONS_MODE_MASK = 'indexer/%s/dimensions_mode';
    /**
     * @var string
     */
    private $commandName = 'indexer:show-dimensions-mode';
    /**
     * ScopeConfigInterface
     *
     * @var ScopeConfigInterface
     */
    private $configReader;
    /**
     * @var string[]
     */
    private $indexers;
    /**
     * @var string[]
     */
    private $optionalIndexers;

    /**
     * @param ObjectManagerFactory $objectManagerFactory
     * @param ScopeConfigInterface $configReader
     * @param array $indexers
     * @param array $optionalIndexers
     */
    public function __construct(
        ObjectManagerFactory $objectManagerFactory,
        ScopeConfigInterface $configReader,
        array $indexers,
        array $optionalIndexers = []
    ) {
        $this->configReader = $configReader;
        $this->indexers = $indexers;
        $this->optionalIndexers = $optionalIndexers;
        parent::__construct($objectManagerFactory);
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName($this->commandName)
            ->setDescription('Shows Indexer Dimension Mode')
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
            $selectedIndexers = $input->getArgument(self::INPUT_KEY_INDEXER);
            if ($selectedIndexers) {
                $indexersList = (array)$selectedIndexers;
            } else {
                $indexersList = $this->indexers;
            }
            foreach ($indexersList as $indexerId) {
                $indexer->load($indexerId);
                $configPath = sprintf(self::XML_PATH_DIMENSIONS_MODE_MASK, $indexerId);
                $mode = $this->configReader->getValue($configPath) ?: self::DIMENSION_MODE_NONE;
                $output->writeln(sprintf('%-50s ', $indexer->getTitle() . ':') . $mode);
            }
        } catch (\Exception $e) {
            if (!in_array($indexerId, $this->optionalIndexers)) { /** @phpstan-ignore-line */
                $output->writeln('"' . $indexer->getTitle() . '" indexer process unknown error:' . PHP_EOL);
                $output->writeln($e->getMessage() . PHP_EOL);
                // we must have an exit code higher than zero to indicate something was wrong
                $returnValue = Cli::RETURN_FAILURE;
            }
        }

        return $returnValue;
    }

    /**
     * Get list of arguments for the command
     *
     * @return InputArgument[]
     */
    private function getInputList(): array
    {
        $optionDescription = 'Space-separated list of index types or omit to apply to all indexes';
        $arguments[] = new InputArgument(
            self::INPUT_KEY_INDEXER,
            InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
            $optionDescription . ' (' . implode(',', $this->indexers) . ')'
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
        $inputIndexer = (array)$input->getArgument(self::INPUT_KEY_INDEXER);
        $acceptedValues = array_keys($this->indexers);
        $errors = $this->validateArgument(self::INPUT_KEY_INDEXER, $inputIndexer, $acceptedValues);

        return $errors;
    }

    /**
     * Validate command argument and return errors in case if argument is invalid
     *
     * @param string $inputKey
     * @param array $inputIndexer
     * @param array $acceptedValues
     * @return array
     */
    private function validateArgument(string $inputKey, array $inputIndexer, array $acceptedValues): array
    {
        $errors = [];
        $acceptedIndexerValues = ' Accepted values for "<' . $inputKey . '>" are \'' .
            implode(',', $acceptedValues) . '\'';
        if (!empty($inputIndexer) && !\array_intersect($inputIndexer, $acceptedValues)) {
            $errors[] = 'Invalid value for "<' . $inputKey . '>" argument.' . $acceptedIndexerValues;
        }

        return $errors;
    }
}
