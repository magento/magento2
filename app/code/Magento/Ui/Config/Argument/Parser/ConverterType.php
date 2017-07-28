<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Config\Argument\Parser;

use Magento\Ui\Config\Argument\ParserInterface;
use Magento\Ui\Config\ConverterInterface;

/**
 * This class convert node with custom converter according to type
 * @since 2.2.0
 */
class ConverterType implements ParserInterface
{
    /**
     * @var ConverterInterface
     * @since 2.2.0
     */
    private $converter;

    /**
     * @param ConverterInterface $converter
     * @since 2.2.0
     */
    public function __construct(ConverterInterface $converter)
    {
        $this->converter = $converter;
    }

    /**
     * @inheritdoc
     * @throws \InvalidArgumentException if some input argument isn't passed
     * @since 2.2.0
     */
    public function parse(array $data, \DOMNode $node)
    {
        $result = [];
        $domXPath = new \DOMXPath($node->ownerDocument);
        $nodeList = $domXPath->query(trim($data['value']), $node);
        foreach ($nodeList as $itemNode) {
            $result = $this->converter->convert($itemNode, $data);
        }

        if ($result && isset($data['name'])) {
            $result = array_merge($result, ['name' => $data['name']]);
        }

        return $result;
    }
}
