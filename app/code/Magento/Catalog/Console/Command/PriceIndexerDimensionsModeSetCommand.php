<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Console\Command;

use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Magento\Indexer\Console\Command\AbstractIndexerCommand;
use Magento\Framework\App\ObjectManagerFactory;

/**
 * Command to change price indexer dimensions mode
 */
class PriceIndexerDimensionsModeSetCommand extends AbstractIndexerCommand
{
    const INPUT_KEY_MODE = 'mode';
    const INPUT_KEY_NONE = 'none';
    const INPUT_KEY_WEBSITE = 'website';
    const INPUT_KEY_CUSTOMER_GROUP = 'customer_group';
    const INPUT_KEY_WEBSITE_AND_CUSTOMER_GROUP = 'website_and_customer_group';
    const XML_PATH_PRICE_DIMENSIONS_MODE = 'indexer/catalog_product_price/dimensions_mode';

    /**
     * ScopeConfigInterface
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $configReader;

    /**
     * ConfigInterface
     *
     * @var \Magento\Framework\App\Config\ConfigResource\ConfigInterface
     */
    private $configWriter;

    /**
     * TypeListInterface
     *
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    private $cacheTypeList;

    /**
     * @param ObjectManagerFactory $objectManagerFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $configReader
     * @param \Magento\Framework\App\Config\ConfigResource\ConfigInterface $configWriter
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     */
    public function __construct(
        ObjectManagerFactory $objectManagerFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $configReader,
        \Magento\Framework\App\Config\ConfigResource\ConfigInterface $configWriter,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
    ) {
        $this->configReader = $configReader;
        $this->configWriter = $configWriter;
        $this->cacheTypeList = $cacheTypeList;
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
            throw new \InvalidArgumentException(implode("\n", $errors));
        }

        $returnValue = \Magento\Framework\Console\Cli::RETURN_SUCCESS;

        $indexer = $this->getObjectManager()->get(\Magento\Indexer\Model\Indexer::class);
        $indexer->load(\Magento\Catalog\Model\Indexer\Product\Price\Processor::INDEXER_ID);

        try {
            $currentMode = $input->getArgument(self::INPUT_KEY_MODE);
            $previousMode = $this->configReader->getValue(self::XML_PATH_PRICE_DIMENSIONS_MODE) ?: self::INPUT_KEY_NONE;

            if ($previousMode !== $currentMode) {
                $this->configWriter->saveConfig(self::XML_PATH_PRICE_DIMENSIONS_MODE, $currentMode);
                //Create new tables and move data
                $this->cacheTypeList->cleanType('config');
                //Delete old tables
            }

            if ($previousMode !== $currentMode) {
                $this->configWriter->saveConfig(self::XML_PATH_PRICE_DIMENSIONS_MODE, $currentMode);
                $output->writeln(
                    'Dimensions mode for indexer ' . $indexer->getTitle() . ' was changed from \''
                    . $previousMode . '\' to \'' . $currentMode . '\''
                );
                $indexer->invalidate();
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
    public function getInputList()
    {
        $modeOptions[] = new InputArgument(
            self::INPUT_KEY_MODE,
            InputArgument::REQUIRED,
            'Indexer dimensions mode ['. self::INPUT_KEY_NONE . '|' . self::INPUT_KEY_WEBSITE
            . '|' . self::INPUT_KEY_CUSTOMER_GROUP
            . '|' . self::INPUT_KEY_WEBSITE_AND_CUSTOMER_GROUP .']'
        );
        return $modeOptions;
    }

    /**
     * Check if all admin options are provided
     *
     * @param InputInterface $input
     * @return string[]
     */
    public function validate(InputInterface $input)
    {
        $errors = [];

        $acceptedModeValues = ' Accepted values for ' . self::INPUT_KEY_MODE . ' are \''
            . self::INPUT_KEY_NONE . '\', \''
            . self::INPUT_KEY_WEBSITE . '\', \''
            . self::INPUT_KEY_CUSTOMER_GROUP . '\', \''
            . self::INPUT_KEY_WEBSITE_AND_CUSTOMER_GROUP . '\'';

        $inputMode = $input->getArgument(self::INPUT_KEY_MODE);
        if (!$inputMode) {
            $errors[] = 'Missing argument \'' . self::INPUT_KEY_MODE .'\'.' . $acceptedModeValues;
        } elseif (!in_array(
            $inputMode,
            [
                self::INPUT_KEY_NONE,
                self::INPUT_KEY_WEBSITE,
                self::INPUT_KEY_CUSTOMER_GROUP,
                self::INPUT_KEY_WEBSITE_AND_CUSTOMER_GROUP
            ]
        )) {
            $errors[] = $acceptedModeValues;
        }
        return $errors;
    }
}
