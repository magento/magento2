<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\SearchAdapter\Query\Preprocessor;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Locale\Resolver as LocaleResolver;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Elasticsearch\Model\Adapter\Index\Config\EsConfigInterface;

class Stopwords implements PreprocessorInterface
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var LocaleResolver
     */
    protected $localeResolver;

    /**
     * @var ReadFactory
     */
    protected $readFactory;

    /**
     * @var EsConfigInterface
     */
    protected $esConfig;

    /**
     * @var string
     */
    protected $fileDir;

    /**
     * @param StoreManagerInterface $storeManager
     * @param LocaleResolver $localeResolver
     * @param ReadFactory $readFactory
     * @param EsConfigInterface $esConfig
     * @param string $fileDir
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        LocaleResolver $localeResolver,
        ReadFactory $readFactory,
        EsConfigInterface $esConfig,
        $fileDir = ''
    ) {
        $this->storeManager = $storeManager;
        $this->localeResolver = $localeResolver;
        $this->readFactory = $readFactory;
        $this->esConfig = $esConfig;
        $this->fileDir = $fileDir;
    }

    /**
     * {@inheritdoc}
     */
    public function process($query)
    {
        $stopwords = $this->getStopwordsList();
        $queryParts = explode(' ', $query);
        $query = implode(' ', array_diff($queryParts, $stopwords));
        return trim($query);
    }

    /**
     * Get stopwords list for current locale
     *
     * return array
     */
    protected function getStopwordsList()
    {
        $filename = $this->getStopwordsFile();
        $source = $this->readFactory->create($this->fileDir);
        //$fileStats = $source->stat($filename);
        $fileContent = $source->readFile($filename);
        $stopwords = explode("\n", $fileContent);
        return $stopwords;
    }

    /**
     * Get stopwords file for current locale
     *
     * return string
     */
    protected function getStopwordsFile()
    {
        $stopwordsInfo = $this->esConfig->getStopwordsInfo();
        $storeId = $this->storeManager->getStore()->getId();
        $this->localeResolver->emulate($storeId);
        $locale = $this->localeResolver->getLocale();
        $stopwordsFile = isset( $stopwordsInfo[$locale]) ?  $stopwordsInfo[$locale] : $stopwordsInfo['default'];
        return $stopwordsFile;
    }
}
