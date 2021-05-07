<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\Adapter\Index;

use Magento\Framework\Locale\Resolver as LocaleResolver;
use Magento\Elasticsearch\Model\Adapter\Index\Config\EsConfigInterface;

/**
 * Index Builder
 */
class Builder implements BuilderInterface
{
    /**
     * @var LocaleResolver
     */
    protected $localeResolver;

    /**
     * @var EsConfigInterface
     */
    protected $esConfig;

    /**
     * Current store ID.
     *
     * @var int
     */
    protected $storeId;

    /**
     * @param LocaleResolver $localeResolver
     * @param EsConfigInterface $esConfig
     */
    public function __construct(
        LocaleResolver $localeResolver,
        EsConfigInterface $esConfig
    ) {
        $this->localeResolver = $localeResolver;
        $this->esConfig = $esConfig;
    }

    /**
     * @inheritdoc
     */
    public function build()
    {
        $tokenizer = $this->getTokenizer();
        $filter = $this->getFilter();
        $charFilter = $this->getCharFilter();

        $settings = [
            'analysis' => [
                'analyzer' => [
                    'default' => [
                        'type' => 'custom',
                        'tokenizer' => key($tokenizer),
                        'filter' => array_merge(
                            ['lowercase', 'keyword_repeat'],
                            array_keys($filter)
                        ),
                        'char_filter' => array_keys($charFilter)
                    ],
                    // this analyzer must not include stemmer filter
                    'prefix_search' => [
                        'type' => 'custom',
                        'tokenizer' => key($tokenizer),
                        'filter' => array_merge(
                            ['lowercase', 'keyword_repeat']
                        ),
                        'char_filter' => array_keys($charFilter)
                    ],
                    'sku' => [
                        'type' => 'custom',
                        'tokenizer' => 'keyword',
                        'filter' => array_merge(
                            ['lowercase', 'keyword_repeat'],
                            array_keys($filter)
                        ),
                    ],
                    // this analyzer must not include stemmer filter
                    'sku_prefix_search' => [
                        'type' => 'custom',
                        'tokenizer' => 'keyword',
                        'filter' => array_merge(
                            ['lowercase', 'keyword_repeat']
                        ),
                    ]
                ],
                'tokenizer' => $tokenizer,
                'filter' => $filter,
                'char_filter' => $charFilter,
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
}
