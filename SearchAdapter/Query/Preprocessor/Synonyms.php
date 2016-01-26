<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\SearchAdapter\Query\Preprocessor;

use Magento\Search\Api\SynonymAnalyzerInterface;
use Magento\Framework\Search\Adapter\Preprocessor\PreprocessorInterface;

class Synonyms implements PreprocessorInterface
{
    /**
     * @var SynonymAnalyzerInterface
     */
    private $synonymsAnalyzer;

    /**
     * @param SynonymAnalyzerInterface $synonymsAnalyzer
     */
    public function __construct(
        SynonymAnalyzerInterface $synonymsAnalyzer
    ) {
        $this->synonymsAnalyzer = $synonymsAnalyzer;
    }

    /**
     * {@inheritdoc}
     */
    public function process($query)
    {
        $synonyms = [];
        $synonymsArray = $this->synonymsAnalyzer->getSynonymsForPhrase($query);
        if (count($synonymsArray) > 0) {
            foreach ($synonymsArray as $synonymPart) {
                $synonyms [] = implode(' ', $synonymPart);
            }
            $query = implode(' ', $synonyms);
        }
        return $query;
    }
}
