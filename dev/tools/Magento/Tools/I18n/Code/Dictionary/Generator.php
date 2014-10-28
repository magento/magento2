<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Tools\I18n\Code\Dictionary;

use Magento\Tools\I18n\Code\Factory;
use Magento\Tools\I18n\Code\ParserInterface;

/**
 * Dictionary generator
 */
class Generator
{
    /**
     * Parser
     *
     * @var \Magento\Tools\I18n\Code\ParserInterface
     */
    protected $parser;

    /**
     * Contextual parser
     *
     * @var \Magento\Tools\I18n\Code\ParserInterface
     */
    protected $contextualParser;

    /**
     * Domain abstract factory
     *
     * @var \Magento\Tools\I18n\Code\Factory
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
     * @return \Magento\Tools\I18n\Code\ParserInterface
     */
    protected function getActualParser($withContext)
    {
        return $withContext ? $this->contextualParser : $this->parser;
    }
}
