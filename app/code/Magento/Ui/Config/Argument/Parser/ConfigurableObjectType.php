<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Config\Argument\Parser;

use Magento\Ui\Config\Argument\ParserInterface;

/**
 * This class parse array arguments according to own type
 */
class ConfigurableObjectType implements ParserInterface
{
    /**
     * @var ParserInterface
     */
    private $argumentParser;

    /**
     * @param ParserInterface $argumentParser
     */
    public function __construct(ParserInterface $argumentParser)
    {
        $this->argumentParser = $argumentParser;
    }

    /**
     * @inheritdoc
     * @throws \InvalidArgumentException if array arguments isn't passed
     */
    public function parse(array $data, \DOMNode $node)
    {
        $arguments = isset($data['argument']) ? $data['argument'] : [];
        if (!is_array($arguments)) {
            throw new \InvalidArgumentException('Array arguments are expected.');
        }
        $result = [];
        foreach ($arguments as $argumentKey => $argumentData) {
            $parserResult = $this->argumentParser->parse($argumentData, $node);
            if ($parserResult) {
                $result[$argumentKey] = $parserResult;
            }
        }

        if ($result) {
            $data['argument'] = $result;
            return $data;
        } else {
            return $result;
        }
    }
}
