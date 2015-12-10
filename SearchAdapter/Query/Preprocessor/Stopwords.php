<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\SearchAdapter\Query\Preprocessor;

class Stopwords implements PreprocessorInterface
{
    /**
     * {@inheritdoc}
     */
    public function process($query)
    {
        $stopwords = ['and', 'or', 'the'];
        return trim(
            str_replace(
                $stopwords,
                '',
                $query
            )
        );
    }
}
