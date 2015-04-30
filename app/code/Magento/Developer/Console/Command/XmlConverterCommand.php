<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Developer\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Developer\Model\Tools\Formatter;

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
     * @var \DOMDocument
     */
    private $domXml;

    /**
     * @var \DOMDocument
     */
    private $domXsl;

    /**
     * @var \XSLTProcessor
     */
    private $xsltProcessor;

    /**
     * Inject dependencies
     *
     * @param Formatter $formatter
     * @param \DOMDocument $domXml
     * @param \DOMDocument $domXsl
     * @param \XSLTProcessor $xsltProcessor
     */
    public function __construct(
        Formatter $formatter,
        \DOMDocument $domXml,
        \DOMDocument $domXsl,
        \XSLTProcessor $xsltProcessor
    ) {
        $this->formatter = $formatter;
        $this->domXml = $domXml;
        $this->domXsl = $domXsl;
        $this->xsltProcessor = $xsltProcessor;

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
            $xmlFile = $input->getArgument(self::XML_FILE_ARGUMENT);
            $this->domXml->preserveWhiteSpace = true;
            $this->domXml->load($xmlFile);

            $this->domXsl->preserveWhiteSpace = true;
            $this->domXsl->load($input->getArgument(self::PROCESSOR_ARGUMENT));

            $this->xsltProcessor->registerPHPFunctions();
            $this->xsltProcessor->importStylesheet($this->domXsl);
            $transformedDoc = $this->xsltProcessor->transformToXml($this->domXml);
            $result = $this->formatter->format($transformedDoc);

            if ($input->getOption(self::OVERWRITE_OPTION)) {
                file_put_contents($input->getArgument(self::XML_FILE_ARGUMENT), $result);
                $output->writeln("<info>You saved converted XML into $xmlFile</info>");
            } else {
                $output->write($result);
            }

            return;
        } catch (\Exception $exception) {
            $errorMessage = $exception->getMessage();
            $output->writeln("<error>$errorMessage</error>");
            return;
        }
    }
}
