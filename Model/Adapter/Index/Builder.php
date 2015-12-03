<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\Adapter\Index;

use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Api\StoreConfigManagerInterface;
use Magento\Elasticsearch\Model\Adapter\Index\Config\EsConfigInterface;

class Builder implements BuilderInterface
{
    /**
     * @var StoreRepositoryInterface
     */
    protected $storeRepository;

    /**
     * @var StoreConfigManagerInterface
     */
    protected $storeConfig;

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
     * @param StoreRepositoryInterface $storeRepository
     * @param StoreConfigManagerInterface $storeConfig
     * @param EsConfigInterface $esConfig
     */
    public function __construct(
        StoreRepositoryInterface $storeRepository,
        StoreConfigManagerInterface $storeConfig,
        EsConfigInterface $esConfig
    ) {
        $this->storeRepository = $storeRepository;
        $this->storeConfig = $storeConfig;
        $this->esConfig = $esConfig;
    }

    /**
     * {@inheritdoc}
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
                            ['lowercase'],
                            array_keys($filter)
                        ),
                        'char_filter' => array_keys($charFilter),
                    ],
                ],
                'tokenizer' => $tokenizer,
                'filter' => $filter,
                'char_filter' => $charFilter,
            ],
        ];

        return $settings;
    }

    /**
     * {@inheritdoc}
     */
    public function setStoreId($storeId)
    {
        $this->storeId = $storeId;
    }

    /**
     * @return array
     */
    protected function getTokenizer()
    {
        $tokenizer = [
            'default_tokenizer' => [
                'type' => 'standard',
            ],
        ];
        return $tokenizer;
    }

    /**
     * @return array
     */
    protected function getFilter()
    {
        $filter = [
            'default_stemmer' => $this->getStemmerConfig(),
        ];
        return $filter;
    }

    /**
     * @return array
     */
    protected function getCharFilter()
    {
        $charFilter = [
            'default_char_filter' => [
                'type' => 'html_strip',
            ],
        ];
        return $charFilter;
    }

    /**
     * @return string
     */
    protected function getStoreLocale()
    {
        $store = $this->storeRepository->getById($this->storeId);
        $storeConfigs = $this->storeConfig->getStoreConfigs([$store->getCode()]);
        $storeConfig = array_shift($storeConfigs);
        return $storeConfig->getLocale();
    }

    /**
     * @return array
     */
    protected function getStemmerConfig()
    {
        $stemmerInfo = $this->esConfig->getStemmerInfo();
        $locale = $this->getStoreLocale();
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
