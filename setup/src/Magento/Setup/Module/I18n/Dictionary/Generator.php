<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\I18n\Dictionary;

use Magento\Setup\Module\I18n\Factory;
use Magento\Setup\Module\I18n\ParserInterface;

/**
 * Dictionary generator
 */
class Generator
{
    /**
     * Parser
     *
     * @var \Magento\Setup\Module\I18n\ParserInterface
     */
    protected $parser;

    /**
     * Contextual parser
     *
     * @var \Magento\Setup\Module\I18n\ParserInterface
     */
    protected $contextualParser;

    /**
     * Domain abstract factory
     *
     * @var \Magento\Setup\Module\I18n\Factory
     */
    protected $factory;

    /**
     * Generator options resolver
     *
     * @var Options\ResolverFactory
     */
    protected $optionResolverFactory;

    /**
     * @var WriterInterface
     */
    protected $writer;

    /**
     * Generator construct
     *
     * @param ParserInterface $parser
     * @param ParserInterface $contextualParser
     * @param Factory $factory
     * @param Options\ResolverFactory $optionsResolver
     */
    public function __construct(
        ParserInterface $parser,
        ParserInterface $contextualParser,
        Factory $factory,
        Options\ResolverFactory $optionsResolver
    ) {
        $this->parser = $parser;
        $this->contextualParser = $contextualParser;
        $this->factory = $factory;
        $this->optionResolverFactory = $optionsResolver;
    }

    /**
     * Generate dictionary
     *
     * @param string $directory
     * @param string $outputFilename
     * @param bool $withContext
     * @throws \UnexpectedValueException
     * @return void
     */
    public function generate($directory, $outputFilename, $withContext = false)
    {
        $optionResolver = $this->optionResolverFactory->create($directory, $withContext);

        $parser = $this->getActualParser($withContext);
        $parser->parse($optionResolver->getOptions());

        $phraseList = $parser->getPhrases();
        if (!count($phraseList)) {
            throw new \UnexpectedValueException('No phrases found in the specified dictionary file.');
        }
        foreach ($phraseList as $phrase) {
            $this->getDictionaryWriter($outputFilename)->write($phrase);
        }
        $this->writer = null;
    }

    /**
     * @param string $outputFilename
     * @return WriterInterface
     */
    protected function getDictionaryWriter($outputFilename)
    {
        if (null === $this->writer) {
            $this->writer = $this->factory->createDictionaryWriter($outputFilename);
        }
        return $this->writer;
    }

    /**
     * Get actual parser
     *
     * @param bool $withContext
     * @return \Magento\Setup\Module\I18n\ParserInterface
     */
    protected function getActualParser($withContext)
    {
        return $withContext ? $this->contextualParser : $this->parser;
    }
}
