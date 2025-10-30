<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Ui\Config\Argument\Parser;

use Magento\Ui\Config\Argument\ParserInterface;

/**
 * This class convert xml node to array as is
 */
class XpathType implements ParserInterface
{
    /**
     * @inheritdoc
     * @throws \InvalidArgumentException if type isn't passed
     */
    public function parse(array $data, \DOMNode $node)
    {
        $result = null;
        $type = isset($data['type']) ? $data['type'] : null;
        if (!$type) {
            throw new \InvalidArgumentException('Type attribute are expected.');
        }
        unset($data['type']);
        $domXPath = new \DOMXPath($node->ownerDocument);
        $nodeList = $domXPath->query(trim($data['value'] ?? ''), $node);
        if ($nodeList->length == 1) {
            $nodeItem = $nodeList->item(0);
            $data['xsi:type'] = $type;

            $nodeValue = trim($nodeItem->nodeValue);
            if ($nodeValue !== '') {
                $data['value'] = $nodeValue;
            } else {
                unset($data['value']);
            }

            $result = $data;
        }

        return $result;
    }
}
