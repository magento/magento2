<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ImportExport\Model\Import\Source\FileParser;

/**
 * File Parser Composite
 *
 */
class CompositeParserFactory implements ParserFactoryInterface
{
    /** @var ParserFactoryInterface[] */
    private $parserFactories;

    public function __construct(array $parserFactories = [])
    {
        $this->parserFactories = [];

        foreach ($parserFactories as $parserFactory) {
            $this->addParserFactory($parserFactory);
        }
    }

    public function create($path, array $options = [])
    {
        foreach ($this->parserFactories as $parserFactory) {
            $parser = $this->createParserIfSupported($parserFactory, $path, $options);

            if ($parser === false) {
                continue;
            }

            return $parser;
        }

        $this->thereWasNoParserFound($path);
    }

    public function addParserFactory(ParserFactoryInterface $parserFactory)
    {
        $this->parserFactories[] = $parserFactory;
    }

    private function thereWasNoParserFound($path)
    {
        throw new UnsupportedPathException($path);
    }

    private function createParserIfSupported(ParserFactoryInterface $parserFactory, $path, array $options)
    {
        try {
            $parser = $parserFactory->create($path, $options);
        } catch (UnsupportedPathException $e) {
            return false;
        }

        return $parser;
    }
}
