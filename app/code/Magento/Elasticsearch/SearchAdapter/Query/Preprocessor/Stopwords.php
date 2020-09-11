<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\SearchAdapter\Query\Preprocessor;

use Magento\Elasticsearch\Model\Adapter\Index\Config\EsConfigInterface;
use Magento\Framework\App\Cache\Type\Config;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\Locale\Resolver;
use Magento\Framework\Module\Dir;
use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\Search\Adapter\Preprocessor\PreprocessorInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Elasticsearch stopwords preprocessor
 *
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
     * @var StoreManagerInterface
     * @since 100.1.0
     */
    protected $storeManager;

    /**
     * @var Resolver
     * @since 100.1.0
     */
    protected $localeResolver;

    /**
     * @var ReadFactory
     * @since 100.1.0
     */
    protected $readFactory;

    /**
     * @var Config
     * @since 100.1.0
     */
    protected $configCache;

    /**
     * @var EsConfigInterface
     * @since 100.1.0
     */
    protected $esConfig;

    /**
     * @var Reader
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
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * Initialize dependencies.
     *
     * @param StoreManagerInterface $storeManager
     * @param Resolver $localeResolver
     * @param ReadFactory $readFactory
     * @param Config $configCache
     * @param EsConfigInterface $esConfig
     * @param Reader $moduleDirReader
     * @param string $stopwordsModule
     * @param string $stopwordsDirectory
     * @param SerializerInterface|null $serializer
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        Resolver $localeResolver,
        ReadFactory $readFactory,
        Config $configCache,
        EsConfigInterface $esConfig,
        Reader $moduleDirReader,
        $stopwordsModule = '',
        $stopwordsDirectory = '',
        ?SerializerInterface $serializer = null
    ) {
        $this->storeManager = $storeManager;
        $this->localeResolver = $localeResolver;
        $this->readFactory = $readFactory;
        $this->configCache = $configCache;
        $this->esConfig = $esConfig;
        $this->moduleDirReader = $moduleDirReader;
        $this->stopwordsModule = $stopwordsModule;
        $this->stopwordsDirectory = $stopwordsDirectory;
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(SerializerInterface::class);
    }

    /**
     * @inheritDoc
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
            $stopwords = $this->serializer->unserialize($cachedValue);
        } else {
            $fileContent = $source->readFile($filename);
            $stopwords = explode("\n", $fileContent);
            $this->configCache->save($this->serializer->serialize($stopwords), self::CACHE_ID);
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
}
