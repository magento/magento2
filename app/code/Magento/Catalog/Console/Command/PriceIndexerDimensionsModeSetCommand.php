<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Console\Command;

use Magento\Catalog\Model\Indexer\Product\Price\DimensionModeConfiguration;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Magento\Framework\Exception\LocalizedException;
use Magento\Indexer\Console\Command\AbstractIndexerCommand;
use Magento\Framework\App\ObjectManagerFactory;
use Magento\Catalog\Model\Indexer\Product\Price\ModeSwitcher;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\ConfigResource\ConfigInterface;
use Magento\Framework\App\Cache\TypeListInterface;

/**
 * Command to change price indexer dimensions mode
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PriceIndexerDimensionsModeSetCommand extends AbstractIndexerCommand
{
    const INPUT_KEY_MODE = 'mode';

    /**
     * ScopeConfigInterface
     *
     * @var ScopeConfigInterface
     */
    private $configReader;

    /**
     * ConfigInterface
     *
     * @var ConfigInterface
     */
    private $configWriter;

    /**
     * TypeListInterface
     *
     * @var TypeListInterface
     */
    private $cacheTypeList;

    /**
     * ModeSwitcher
     *
     * @var ModeSwitcher
     */
    private $modeSwitcher;

    /**
     * @param ObjectManagerFactory $objectManagerFactory
     * @param ScopeConfigInterface $configReader
     * @param ConfigInterface $configWriter
     * @param TypeListInterface $cacheTypeList
     * @param ModeSwitcher $modeSwitcher
     */
    public function __construct(
        ObjectManagerFactory $objectManagerFactory,
        ScopeConfigInterface $configReader,
        ConfigInterface $configWriter,
        TypeListInterface $cacheTypeList,
        ModeSwitcher $modeSwitcher
    ) {
        $this->configReader = $configReader;
        $this->configWriter = $configWriter;
        $this->cacheTypeList = $cacheTypeList;
        $this->modeSwitcher = $modeSwitcher;
        parent::__construct($objectManagerFactory);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('indexer:set-dimensions-mode:catalog_product_price')
            ->setDescription('Set Indexer Dimensions Mode')
            ->setDefinition($this->getInputList());

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $errors = $this->validate($input);

        if ($errors) {
            throw new \InvalidArgumentException(implode(PHP_EOL, $errors));
        }

        $returnValue = \Magento\Framework\Console\Cli::RETURN_SUCCESS;

        $indexer = $this->getObjectManager()->get(\Magento\Indexer\Model\Indexer::class);
        $indexer->load(\Magento\Catalog\Model\Indexer\Product\Price\Processor::INDEXER_ID);

        try {
            $currentMode = $input->getArgument(self::INPUT_KEY_MODE);
            $previousMode = $this->configReader->getValue(ModeSwitcher::XML_PATH_PRICE_DIMENSIONS_MODE) ?:
                DimensionModeConfiguration::DIMENSION_NONE;

            if ($previousMode !== $currentMode) {
                //Create new tables and move data
                $this->modeSwitcher->createTables($currentMode);
                $this->modeSwitcher->moveData($currentMode, $previousMode);

                //Change config options
                $this->configWriter->saveConfig(ModeSwitcher::XML_PATH_PRICE_DIMENSIONS_MODE, $currentMode);
                $this->cacheTypeList->cleanType('config');
                $indexer->invalidate();

                //Delete old tables
                $this->modeSwitcher->dropTables($previousMode);

                $output->writeln(
                    'Dimensions mode for indexer ' . $indexer->getTitle() . ' was changed from \''
                    . $previousMode . '\' to \'' . $currentMode . '\''
                );
            } else {
                $output->writeln('Dimensions mode for indexer ' . $indexer->getTitle() . ' has not been changed');
            }
        } catch (LocalizedException $e) {
            $output->writeln($e->getMessage() . PHP_EOL);
            // we must have an exit code higher than zero to indicate something was wrong
            $returnValue =  \Magento\Framework\Console\Cli::RETURN_FAILURE;
        } catch (\Exception $e) {
            $output->writeln($indexer->getTitle() . " indexer process unknown error:" . PHP_EOL);
            $output->writeln($e->getMessage() . PHP_EOL);
            // we must have an exit code higher than zero to indicate something was wrong
            $returnValue =  \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }

        return $returnValue;
    }

    /**
     * Get list of arguments for the command
     *
     * @return InputOption[]
     */
    public function getInputList(): array
    {
        $modeOptions[] = new InputArgument(
            self::INPUT_KEY_MODE,
            InputArgument::REQUIRED,
            'Indexer dimensions mode ['. DimensionModeConfiguration::DIMENSION_NONE
            . '|' . DimensionModeConfiguration::DIMENSION_WEBSITE
            . '|' . DimensionModeConfiguration::DIMENSION_CUSTOMER_GROUP
            . '|' . DimensionModeConfiguration::DIMENSION_WEBSITE_AND_CUSTOMER_GROUP .']'
        );
        return $modeOptions;
    }

    /**
     * Check if all admin options are provided
     *
     * @param InputInterface $input
     * @return string[]
     */
    public function validate(InputInterface $input): array
    {
        $errors = [];

        $acceptedModeValues = ' Accepted values for ' . self::INPUT_KEY_MODE . ' are \''
            . DimensionModeConfiguration::DIMENSION_NONE . '\', \''
            . DimensionModeConfiguration::DIMENSION_WEBSITE . '\', \''
            . DimensionModeConfiguration::DIMENSION_CUSTOMER_GROUP . '\', \''
            . DimensionModeConfiguration::DIMENSION_WEBSITE_AND_CUSTOMER_GROUP . '\'';

        $inputMode = $input->getArgument(self::INPUT_KEY_MODE);
        if (!$inputMode) {
            $errors[] = 'Missing argument \'' . self::INPUT_KEY_MODE .'\'.' . $acceptedModeValues;
        } elseif (!in_array(
            $inputMode,
            [
                DimensionModeConfiguration::DIMENSION_NONE,
                DimensionModeConfiguration::DIMENSION_WEBSITE,
                DimensionModeConfiguration::DIMENSION_CUSTOMER_GROUP,
                DimensionModeConfiguration::DIMENSION_WEBSITE_AND_CUSTOMER_GROUP
            ]
        )) {
            $errors[] = $acceptedModeValues;
        }
        return $errors;
    }
}
