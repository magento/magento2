<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Config\Argument\Parser;

use Magento\Ui\Config\Argument\ParserInterface;

/**
 * This class is a composite of parsers which converts XML nodes to array
 */
class Composite implements ParserInterface
{
    /**
     * Format: array('<name>' => <instance>, ...)
     *
     * @var ParserInterface[]
     */
    private $parsers;

    /**
     * Data key that holds name of an parser to be used for that data
     *
     * @var string
     */
    private $discriminator;

    /**
     * @param ParserInterface[] $parsers
     * @param string $discriminator
     * @throws \InvalidArgumentException if parser isn't implement parser interface
     */
    public function __construct(array $parsers, $discriminator)
    {
        foreach ($parsers as $parserName => $parserInstance) {
            if (!$parserInstance instanceof ParserInterface) {
                throw new \InvalidArgumentException(
                    "Parser named '{$parserName}' is expected to be an argument parser instance."
                );
            }
        }
        $this->parsers = $parsers;
        $this->discriminator = $discriminator;
    }

    /**
     * @inheritdoc
     * @throws \InvalidArgumentException if discriminator isn't passed
     */
    public function parse(array $data, \DOMNode $node)
    {
        if (!isset($data[$this->discriminator])) {
            throw new \InvalidArgumentException(
                sprintf('Value for key "%s" is missing in the argument data.', $this->discriminator)
            );
        }
        $parserName = $data[$this->discriminator];
        $parser = $this->getParser($parserName);
        return $parser->parse($data, $node);
    }

    /**
     * Register parser instance under a given unique name
     *
     * @param string $name
     * @param ParserInterface $instance
     * @return void
     * @throws \InvalidArgumentException if parser has already been defined
     */
    public function addParser($name, ParserInterface $instance)
    {
        if (isset($this->parsers[$name])) {
            throw new \InvalidArgumentException("Argument parser named '{$name}' has already been defined.");
        }
        $this->parsers[$name] = $instance;
    }

    /**
     * Retrieve parser instance by its unique name
     *
     * @param string $name
     * @return ParserInterface
     * @throws \InvalidArgumentException if the parser hasn't already been defined
     */
    private function getParser($name)
    {
        if (!isset($this->parsers[$name])) {
            throw new \InvalidArgumentException("Argument parser named '{$name}' has not been defined.");
        }
        return $this->parsers[$name];
    }
}
