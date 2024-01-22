<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\Adapter\Index;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Locale\Resolver as LocaleResolver;
use Magento\Elasticsearch\Model\Adapter\Index\Config\EsConfigInterface;
use Magento\Search\Model\ResourceModel\SynonymReader;

/**
 * Index Builder
 */
class Builder implements BuilderInterface
{
    /**
     * @var LocaleResolver
     */
    private $localeResolver;

    /**
     * @var EsConfigInterface
     */
    private $esConfig;

    /**
     * Current store ID.
     *
     * @var int
     */
    private $storeId;

    /**
     * @var SynonymReader
     */
    private $synonymReader;

    /**
     * @param LocaleResolver $localeResolver
     * @param EsConfigInterface $esConfig
     * @param SynonymReader $synonymReader
     */
    public function __construct(
        LocaleResolver $localeResolver,
        EsConfigInterface $esConfig,
        SynonymReader $synonymReader
    ) {
        $this->localeResolver = $localeResolver;
        $this->esConfig = $esConfig;
        $this->synonymReader = $synonymReader;
    }

    /**
     * @inheritdoc
     */
    public function build()
    {
        $tokenizer = $this->getTokenizer();
        $filter = $this->getFilter();
        $charFilter = $this->getCharFilter();
        $synonymFilter = $this->getSynonymFilter();

        $settings = [
            'analysis' => [
                'analyzer' => [
                    'default' => [
                        'type' => 'custom',
                        'tokenizer' => key($tokenizer),
                        'filter' => array_merge(
                            ['lowercase', 'keyword_repeat', 'asciifolding'],
                            array_keys($filter)
                        ),
                        'char_filter' => array_keys($charFilter)
                    ],
                    // this analyzer must not include keyword_repeat and stemmer filters
                    'prefix_search' => [
                        'type' => 'custom',
                        'tokenizer' => key($tokenizer),
                        'filter' => array_merge(
                            ['lowercase', 'asciifolding'],
                            array_keys($synonymFilter)
                        ),
                        'char_filter' => array_keys($charFilter)
                    ],
                    'sku' => [
                        'type' => 'custom',
                        'tokenizer' => 'keyword',
                        'filter' => array_merge(
                            ['lowercase', 'keyword_repeat', 'asciifolding'],
                            array_keys($filter)
                        ),
                    ],
                    // this analyzer must not include keyword_repeat and stemmer filters
                    'sku_prefix_search' => [
                        'type' => 'custom',
                        'tokenizer' => 'keyword',
                        'filter' => array_merge(
                            ['lowercase', 'asciifolding'],
                            array_keys($synonymFilter)
                        ),
                    ]
                ],
                'tokenizer' => $tokenizer,
                'filter' => array_merge($filter, $synonymFilter),
                'char_filter' => $charFilter,
                'normalizer' => [
                    'folding' => [
                        'type' => 'custom',
                        'filter' => ['asciifolding', 'lowercase'],
                    ],
                ],
            ],
        ];

        return $settings;
    }

    /**
     * Setter for storeId property
     *
     * @param int $storeId
     * @return void
     */
    public function setStoreId($storeId)
    {
        $this->storeId = $storeId;
    }

    /**
     * Return tokenizer configuration
     *
     * @return array
     */
    protected function getTokenizer()
    {
        return [
            'default_tokenizer' => [
                'type' => 'standard'
            ]
        ];
    }

    /**
     * Return filter configuration
     *
     * @return array
     */
    protected function getFilter()
    {
        return [
            'default_stemmer' => $this->getStemmerConfig(),
            'unique_stem' => [
                'type' => 'unique',
                'only_on_same_position' => true
            ]
        ];
    }

    /**
     * Return char filter configuration
     *
     * @return array
     */
    protected function getCharFilter()
    {
        return [
            'default_char_filter' => [
                'type' => 'html_strip',
            ],
        ];
    }

    /**
     * Return stemmer configuration
     *
     * @return array
     */
    protected function getStemmerConfig()
    {
        $stemmerInfo = $this->esConfig->getStemmerInfo();
        $this->localeResolver->emulate($this->storeId);
        $locale = $this->localeResolver->getLocale();
        if (isset($stemmerInfo[$locale])) {
            return [
                'type' => $stemmerInfo['type'],
                'language' => $stemmerInfo[$locale],
            ];
        }
        return [
            'type' => $stemmerInfo['type'],
            'language' => $stemmerInfo['default'],
        ];
    }

    /**
     * Get filter based on defined synonyms
     *
     * @throws LocalizedException
     */
    private function getSynonymFilter(): array
    {
        $synonyms = $this->synonymReader->getAllSynonymsForStoreViewId($this->storeId);
        $synonymFilter = [];

        if ($synonyms) {
            $synonymFilter = [
                'synonyms' => [
                    'type' => 'synonym_graph',
                    'synonyms' => $synonyms
                ]
            ];
        }

        return $synonymFilter;
    }
}
