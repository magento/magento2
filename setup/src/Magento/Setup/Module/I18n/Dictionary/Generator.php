<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\I18n\Dictionary;

use Magento\Setup\Module\I18n\Factory;
use Magento\Setup\Module\I18n\ParserInterface;

/**
 * Dictionary generator
 * @since 2.0.0
 */
class Generator
{
    /**
     * Parser
     *
     * @var \Magento\Setup\Module\I18n\ParserInterface
     * @since 2.0.0
     */
    protected $parser;

    /**
     * Contextual parser
     *
     * @var \Magento\Setup\Module\I18n\ParserInterface
     * @since 2.0.0
     */
    protected $contextualParser;

    /**
     * Domain abstract factory
     *
     * @var \Magento\Setup\Module\I18n\Factory
     * @since 2.0.0
     */
    protected $factory;

    /**
     * Generator options resolver
     *
     * @var Options\ResolverFactory
     * @since 2.0.0
     */
    protected $optionResolverFactory;

    /**
     * @var WriterInterface
     * @since 2.0.0
     */
    protected $writer;

    /**
     * Generator construct
     *
     * @param ParserInterface $parser
     * @param ParserInterface $contextualParser
     * @param Factory $factory
     * @param Options\ResolverFactory $optionsResolver
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    protected function getActualParser($withContext)
    {
        return $withContext ? $this->contextualParser : $this->parser;
    }
}
