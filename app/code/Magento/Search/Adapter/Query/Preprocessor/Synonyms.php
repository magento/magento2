<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Adapter\Query\Preprocessor;

use Magento\Framework\Search\Adapter\Preprocessor\PreprocessorInterface;
use Magento\Search\Api\SynonymAnalyzerInterface;

class Synonyms implements PreprocessorInterface
{
    /**
     * @var SynonymAnalyzerInterface
     */
    private $synonymsAnalyzer;

    /**
     * Constructor
     * 
     * @param SynonymAnalyzerInterface $synonymsAnalyzer
     */
    public function __construct(SynonymAnalyzerInterface $synonymsAnalyzer)
    {
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
