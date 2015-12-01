<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\Adapter\Index\Config;

use Magento\Framework\Config\ConverterInterface;

class Converter implements ConverterInterface
{
    /**
     * {@inheritdoc}
     */
    public function convert($source)
    {
        $output = [];
        foreach ($source->getElementsByTagName('*') as $node) {
            if (!in_array($node->nodeName, ['config', 'stemmer'])) {
                $output[$node->nodeName]= $node->textContent;
            }
        }
        return $output;
    }
}
