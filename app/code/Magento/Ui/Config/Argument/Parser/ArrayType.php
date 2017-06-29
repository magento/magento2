<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Config\Argument\Parser;

use Magento\Ui\Config\Argument\ParserInterface;

/**
 * This class parse array items according to own type
 */
class ArrayType implements ParserInterface
{
    /**
     * @var ParserInterface
     */
    private $itemParser;

    /**
     * @param ParserInterface $itemParser
     */
    public function __construct(ParserInterface $itemParser)
    {
        $this->itemParser = $itemParser;
    }

    /**
     * @inheritdoc
     * @throws \InvalidArgumentException if array items isn't passed
     */
    public function parse(array $data, \DOMNode $node)
    {
        $items = isset($data['item']) ? $data['item'] : [];
        if (!is_array($items)) {
            throw new \InvalidArgumentException('Array items are expected.');
        }
        $result = [];
        foreach ($items as $itemKey => $itemData) {
            $parserResult = $this->itemParser->parse($itemData, $node);
            if ($parserResult) {
                $result[$itemKey] = $parserResult;
            }
        }
        if (!empty($result)) {
            $data['item'] = $result;
            return $data;
        }

        return $result;
    }
}
