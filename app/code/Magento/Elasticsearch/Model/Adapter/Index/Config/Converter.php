<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
        $stemmer = $source->getElementsByTagName('stemmer');
        $stemmerInfo = [];
        foreach ($stemmer as $stemmerItem) {
            foreach ($stemmerItem->childNodes as $childNode) {
                if ($childNode->nodeType === XML_ELEMENT_NODE) {
                    $stemmerInfo[$childNode->localName]= $childNode->textContent;
                }
            }
        }

        $stopwords = $source->getElementsByTagName('stopwords_file');
        $stopwordsInfo = [];
        foreach ($stopwords as $stopwordsItem) {
            foreach ($stopwordsItem->childNodes as $childNode) {
                if ($childNode->nodeType === XML_ELEMENT_NODE) {
                    $stopwordsInfo[$childNode->localName]= $childNode->textContent;
                }
            }
        }

        return ['stemmerInfo' => $stemmerInfo, 'stopwordsInfo' => $stopwordsInfo];
    }
}
