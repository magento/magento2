<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Developer\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Developer\Model\Tools\Formatter;
use Magento\Framework\DomDocument\DomDocumentFactory;
use Magento\Framework\XsltProcessor\XsltProcessorFactory;

/**
 * Class XmlConverterCommand
 * Converts XML file using XSL style sheets
 */
class XmlConverterCommand extends Command
{
    /**
     * XML file argument name constant
     */
    const XML_FILE_ARGUMENT = 'xml-file';

    /**
     * Processor argument constant
     */
    const PROCESSOR_ARGUMENT = 'processor';

    /**
     * Overwrite option constant
     */
    const OVERWRITE_OPTION = 'overwrite';

    /**
     * @var Formatter
     */
    private $formatter;

    /**
     * @var DomDocumentFactory
     */
    private $domFactory;

    /**
     * @var XsltProcessorFactory
     */
    private $xsltProcessorFactory;

    /**
     * Inject dependencies
     *
     * @param Formatter $formatter
     * @param DomDocumentFactory $domFactory
     * @param XsltProcessorFactory $xsltProcessorFactory
     */
    public function __construct(
        Formatter $formatter,
        DomDocumentFactory $domFactory,
        XsltProcessorFactory $xsltProcessorFactory
    ) {
        $this->formatter = $formatter;
        $this->domFactory = $domFactory;
        $this->xsltProcessorFactory = $xsltProcessorFactory;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('dev:xml:convert')
            ->setDescription('Converts XML file using XSL style sheets')
            ->setDefinition([
                new InputArgument(
                    self::XML_FILE_ARGUMENT,
                    InputArgument::REQUIRED,
                    'Path to XML file that going to be transformed'
                ),
                new InputArgument(
                    self::PROCESSOR_ARGUMENT,
                    InputArgument::REQUIRED,
                    'Path to XSL style sheet that going to be applied to XML file'
                ),
                new InputOption(
                    self::OVERWRITE_OPTION,
                    '-o',
                    InputOption::VALUE_NONE,
                    'Overwrite XML file'
                ),

            ]);

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $domXml = $this->domFactory->create();
            $domXsl = $this->domFactory->create();
            $xsltProcessor = $this->xsltProcessorFactory->create();

            $xmlFile = $input->getArgument(self::XML_FILE_ARGUMENT);
            $domXml->preserveWhiteSpace = true;
            $domXml->load($xmlFile);

            $domXsl->preserveWhiteSpace = true;
            $domXsl->load($input->getArgument(self::PROCESSOR_ARGUMENT));

            $xsltProcessor->registerPHPFunctions();
            $xsltProcessor->importStylesheet($domXsl);
            $transformedDoc = $xsltProcessor->transformToXml($domXml);
            $result = $this->formatter->format($transformedDoc);

            if ($input->getOption(self::OVERWRITE_OPTION)) {
                file_put_contents($input->getArgument(self::XML_FILE_ARGUMENT), $result);
                $output->writeln("<info>You saved converted XML into $xmlFile</info>");
            } else {
                $output->write($result);
            }

            return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
        } catch (\Exception $exception) {
            $errorMessage = $exception->getMessage();
            $output->writeln("<error>$errorMessage</error>");
            // we must have an exit code higher than zero to indicate something was wrong
            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }
    }
}
