<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Developer\Console\Command;

use Magento\Developer\Model\XmlCatalog\Format\FormatInterface;
use Magento\Framework\App\Utility\Files;
use Magento\Framework\Config\Dom\UrnResolver;
use Magento\Framework\Console\Cli;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class XmlCatalogGenerateCommand Generates dictionary of URNs for the IDE
 *
 * @SuppressWarnings(PMD.CouplingBetweenObjects)
 *
 * @api
 * @since 100.0.2
 */
class XmlCatalogGenerateCommand extends Command
{
    /**
     * Option for the type of IDE
     */
    public const IDE_OPTION = 'ide';

    /**
     * Argument for the path to IDE config file
     */
    public const IDE_FILE_PATH_ARGUMENT = 'path';

    /**
     * @var Files
     */
    private $filesUtility;

    /**
     * @var UrnResolver
     */
    private $urnResolver;

    /**
     * @var ReadFactory
     */
    private $readFactory;

    /**
     * Supported formats
     *
     * @var FormatInterface[]
     */
    private $formats;

    /**
     * @param Files $filesUtility
     * @param UrnResolver $urnResolver
     * @param ReadFactory $readFactory
     * @param FormatInterface[] $formats
     */
    public function __construct(
        Files $filesUtility,
        UrnResolver $urnResolver,
        ReadFactory $readFactory,
        array $formats = []
    ) {
        $this->filesUtility = $filesUtility;
        $this->urnResolver = $urnResolver;
        $this->formats = $formats;
        $this->readFactory = $readFactory;
        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('dev:urn-catalog:generate')
            ->setDescription('Generates the catalog of URNs to *.xsd mappings for the IDE to highlight xml.')
            ->setDefinition([
                new InputOption(
                    self::IDE_OPTION,
                    null,
                    InputOption::VALUE_REQUIRED,
                    'Format in which catalog will be generated. Supported: [' .
                    implode(', ', $this->getSupportedFormats()) . ']',
                    'phpstorm'
                ),
                new InputArgument(
                    self::IDE_FILE_PATH_ARGUMENT,
                    InputArgument::REQUIRED,
                    'Path to file to output the catalog. For PhpStorm use .idea/misc.xml'
                )
            ]);

        parent::configure();
    }

    /**
     * Get an array of URNs
     *
     * @param OutputInterface $output
     * @return array
     */
    private function getUrnDictionary(OutputInterface $output)
    {
        $files = $this->filesUtility->getXmlCatalogFiles('*.xml');
        $files = array_merge($files, $this->filesUtility->getXmlCatalogFiles('*.xsd'));

        $urns = [];
        foreach ($files as $file) {
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            $fileDir = dirname($file[0]);
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            $fileName = basename($file[0]);
            $read = $this->readFactory->create($fileDir);
            $content = $read->readFile($fileName);
            $matches = [];
            preg_match_all('/schemaLocation="(urn\:magento\:[^"]*)"/i', $content, $matches);
            if (isset($matches[1])) {
                $urns[] = $matches[1];
            }
        }
        $urns = array_unique(array_merge([], ...$urns));
        $paths = [];
        foreach ($urns as $urn) {
            try {
                $paths[$urn] = $this->urnResolver->getRealPath($urn);
            } catch (\Exception $e) {
                // don't add unsupported element to array
                if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                    $output->writeln($e->getMessage());
                }
            }
        }
        return $paths;
    }

    /**
     * @inheritdoc
     *
     * @throws \InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $ideName = $input->getOption(self::IDE_OPTION);

        $ideFilePath = $input->getArgument(self::IDE_FILE_PATH_ARGUMENT);
        $urnDictionary = $this->getUrnDictionary($output);

        $formatter = $this->getFormatters($ideName);
        if (!$formatter instanceof FormatInterface) {
            throw new InputException(__("Format for IDE '%1' is not supported", $ideName));
        }

        $formatter->generateCatalog($urnDictionary, $ideFilePath);

        return Cli::RETURN_SUCCESS;
    }

    /**
     * Get formatter based on format
     *
     * @param string $format
     * @return FormatInterface|false
     */
    private function getFormatters($format)
    {
        $format = $format === null ? '' : strtolower($format);
        if (!isset($this->formats[$format])) {
            return false;
        }
        return $this->formats[$format];
    }

    /**
     * Get registered formatter aliases
     *
     * @return string[]
     */
    public function getSupportedFormats()
    {
        return array_keys($this->formats);
    }
}
