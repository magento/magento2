<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Adapter\Query\Preprocessor;

use Magento\Framework\Search\Adapter\Preprocessor\PreprocessorInterface;
use Magento\Search\Api\SynonymAnalyzerInterface;

/**
 * Class \Magento\Search\Adapter\Query\Preprocessor\Synonyms
 *
 * @since 2.1.0
 */
class Synonyms implements PreprocessorInterface
{
    /**
     * @var SynonymAnalyzerInterface
     * @since 2.1.0
     */
    private $synonymsAnalyzer;

    /**
     * Constructor
     *
     * @param SynonymAnalyzerInterface $synonymsAnalyzer
     * @since 2.1.0
     */
    public function __construct(SynonymAnalyzerInterface $synonymsAnalyzer)
    {
        $this->synonymsAnalyzer = $synonymsAnalyzer;
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
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
