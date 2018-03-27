<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\SearchAdapter\Query\Preprocessor;

use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Elasticsearch\Model\Adapter\Index\Config\EsConfigInterface;
use Magento\Framework\Search\Adapter\Preprocessor\PreprocessorInterface;
use Magento\Framework\Module\Dir;

/**
 * @api
 * @since 100.1.0
 */
class Stopwords implements PreprocessorInterface
{
    /**
     * Cache id for elasticsearch stopwords
     */
    const CACHE_ID = 'elasticsearch_stopwords';

    /**
     * Stopwords file modification time gap, seconds
     */
    const STOPWORDS_FILE_MODIFICATION_TIME_GAP = 900;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     * @since 100.1.0
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Locale\Resolver
     * @since 100.1.0
     */
    protected $localeResolver;

    /**
     * @var ReadFactory
     * @since 100.1.0
     */
    protected $readFactory;

    /**
     * @var \Magento\Framework\App\Cache\Type\Config
     * @since 100.1.0
     */
    protected $configCache;

    /**
     * @var EsConfigInterface
     * @since 100.1.0
     */
    protected $esConfig;

    /**
     * @var \Magento\Framework\Module\Dir\Reader
     * @since 100.1.0
     */
    protected $moduleDirReader;

    /**
     * @var string
     */
    private $stopwordsModule;

    /**
     * @var string
     */
    private $stopwordsDirectory;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $serializer;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Locale\Resolver $localeResolver
     * @param ReadFactory $readFactory
     * @param \Magento\Framework\App\Cache\Type\Config $configCache
     * @param EsConfigInterface $esConfig
     * @param \Magento\Framework\Module\Dir\Reader $moduleDirReader
     * @param string $stopwordsModule
     * @param string $stopwordsDirectory
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Locale\Resolver $localeResolver,
        ReadFactory $readFactory,
        \Magento\Framework\App\Cache\Type\Config $configCache,
        EsConfigInterface $esConfig,
        \Magento\Framework\Module\Dir\Reader $moduleDirReader,
        $stopwordsModule = '',
        $stopwordsDirectory = ''
    ) {
        $this->storeManager = $storeManager;
        $this->localeResolver = $localeResolver;
        $this->readFactory = $readFactory;
        $this->configCache = $configCache;
        $this->esConfig = $esConfig;
        $this->moduleDirReader = $moduleDirReader;
        $this->stopwordsModule = $stopwordsModule;
        $this->stopwordsDirectory = $stopwordsDirectory;
    }

    /**
     * {@inheritdoc}
     * @since 100.1.0
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
     * @return array
     * @since 100.1.0
     */
    protected function getStopwordsList()
    {
        $filename = $this->getStopwordsFile();
        $fileDir = $this->moduleDirReader->getModuleDir(Dir::MODULE_ETC_DIR, $this->stopwordsModule)
            . '/' . $this->stopwordsDirectory;
        $source = $this->readFactory->create($fileDir);
        $fileStats = $source->stat($filename);
        if (((time() - $fileStats['mtime']) > self::STOPWORDS_FILE_MODIFICATION_TIME_GAP)
            && ($cachedValue = $this->configCache->load(self::CACHE_ID))) {
            $stopwords = $this->getSerializer()->unserialize($cachedValue);
        } else {
            $fileContent = $source->readFile($filename);
            $stopwords = explode("\n", $fileContent);
            $this->configCache->save($this->getSerializer()->serialize($stopwords), self::CACHE_ID);
        }
        return $stopwords;
    }

    /**
     * Get stopwords file for current locale
     *
     * @return string
     * @since 100.1.0
     */
    protected function getStopwordsFile()
    {
        $stopwordsInfo = $this->esConfig->getStopwordsInfo();
        $storeId = $this->storeManager->getStore()->getId();
        $this->localeResolver->emulate($storeId);
        $locale = $this->localeResolver->getLocale();
        $stopwordsFile = isset($stopwordsInfo[$locale]) ? $stopwordsInfo[$locale] : $stopwordsInfo['default'];
        return $stopwordsFile;
    }

    /**
     * Get serializer
     *
     * @return \Magento\Framework\Serialize\SerializerInterface
     * @deprecated 100.2.0
     */
    private function getSerializer()
    {
        if (null === $this->serializer) {
            $this->serializer = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Framework\Serialize\SerializerInterface::class);
        }
        return $this->serializer;
    }
}
