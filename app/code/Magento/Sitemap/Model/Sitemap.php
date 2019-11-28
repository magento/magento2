<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sitemap\Model;

use Magento\Config\Model\Config\Reader\Source\Deployed\DocumentRoot;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\UrlInterface;
use Magento\Robots\Model\Config\Value;
use Magento\Sitemap\Model\ItemProvider\ItemProviderInterface;
use Magento\Sitemap\Model\ResourceModel\Sitemap as SitemapResource;

/**
 * Sitemap model.
 *
 * @method string getSitemapType()
 * @method \Magento\Sitemap\Model\Sitemap setSitemapType(string $value)
 * @method string getSitemapFilename()
 * @method \Magento\Sitemap\Model\Sitemap setSitemapFilename(string $value)
 * @method string getSitemapPath()
 * @method \Magento\Sitemap\Model\Sitemap setSitemapPath(string $value)
 * @method string getSitemapTime()
 * @method \Magento\Sitemap\Model\Sitemap setSitemapTime(string $value)
 * @method int getStoreId()
 * @method \Magento\Sitemap\Model\Sitemap setStoreId(int $value)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @api
 * @since 100.0.2
 */
class Sitemap extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
    const OPEN_TAG_KEY = 'start';

    const CLOSE_TAG_KEY = 'end';

    const INDEX_FILE_PREFIX = 'sitemap';

    const TYPE_INDEX = 'sitemap';

    const TYPE_URL = 'url';

    /**
     * Last mode date min value
     */
    const LAST_MOD_MIN_VAL = '0000-01-01 00:00:00';

    /**
     * Real file path
     *
     * @var string
     */
    protected $_filePath;

    /**
     * Sitemap items
     *
     * @var array
     */
    protected $_sitemapItems = [];

    /**
     * Current sitemap increment
     *
     * @var int
     */
    protected $_sitemapIncrement = 0;

    /**
     * Sitemap start and end tags
     *
     * @var array
     */
    protected $_tags = [];

    /**
     * Number of lines in sitemap
     *
     * @var int
     */
    protected $_lineCount = 0;

    /**
     * Current sitemap file size
     *
     * @var int
     */
    protected $_fileSize = 0;

    /**
     * New line possible symbols
     *
     * @var array
     */
    private $_crlf = ["win" => "\r\n", "unix" => "\n", "mac" => "\r"];

    /**
     * @var \Magento\Framework\Filesystem\Directory\Write
     */
    protected $_directory;

    /**
     * @var \Magento\Framework\Filesystem\File\Write
     */
    protected $_stream;

    /**
     * Sitemap data
     *
     * @var \Magento\Sitemap\Helper\Data
     */
    protected $_sitemapData;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $_escaper;

    /**
     * @var \Magento\Sitemap\Model\ResourceModel\Catalog\CategoryFactory
     */
    protected $_categoryFactory;

    /**
     * @var \Magento\Sitemap\Model\ResourceModel\Catalog\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Magento\Sitemap\Model\ResourceModel\Cms\PageFactory
     */
    protected $_cmsFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_dateModel;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * @inheritdoc
     *
     * @since 100.1.5
     */
    protected $_cacheTag = [Value::CACHE_TAG];

    /**
     * Item resolver
     *
     * @var ItemProviderInterface
     */
    private $itemProvider;

    /**
     * Sitemap config reader
     *
     * @var SitemapConfigReaderInterface
     */
    private $configReader;

    /**
     * Sitemap Item Factory
     *
     * @var \Magento\Sitemap\Model\SitemapItemInterfaceFactory
     */
    private $sitemapItemFactory;

    /**
     * Last mode min timestamp value
     *
     * @var int
     */
    private $lastModMinTsVal;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Sitemap\Helper\Data $sitemapData
     * @param \Magento\Framework\Filesystem $filesystem
     * @param ResourceModel\Catalog\CategoryFactory $categoryFactory
     * @param ResourceModel\Catalog\ProductFactory $productFactory
     * @param ResourceModel\Cms\PageFactory $cmsFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $modelDate
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     * @param DocumentRoot|null $documentRoot
     * @param ItemProviderInterface|null $itemProvider
     * @param SitemapConfigReaderInterface|null $configReader
     * @param \Magento\Sitemap\Model\SitemapItemInterfaceFactory|null $sitemapItemFactory
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Escaper $escaper,
        \Magento\Sitemap\Helper\Data $sitemapData,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Sitemap\Model\ResourceModel\Catalog\CategoryFactory $categoryFactory,
        \Magento\Sitemap\Model\ResourceModel\Catalog\ProductFactory $productFactory,
        \Magento\Sitemap\Model\ResourceModel\Cms\PageFactory $cmsFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $modelDate,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [],
        \Magento\Config\Model\Config\Reader\Source\Deployed\DocumentRoot $documentRoot = null,
        ItemProviderInterface $itemProvider = null,
        SitemapConfigReaderInterface $configReader = null,
        \Magento\Sitemap\Model\SitemapItemInterfaceFactory $sitemapItemFactory = null
    ) {
        $this->_escaper = $escaper;
        $this->_sitemapData = $sitemapData;
        $documentRoot = $documentRoot ?: ObjectManager::getInstance()->get(DocumentRoot::class);
        $this->_directory = $filesystem->getDirectoryWrite($documentRoot->getPath());
        $this->_categoryFactory = $categoryFactory;
        $this->_productFactory = $productFactory;
        $this->_cmsFactory = $cmsFactory;
        $this->_dateModel = $modelDate;
        $this->_storeManager = $storeManager;
        $this->_request = $request;
        $this->dateTime = $dateTime;
        $this->itemProvider = $itemProvider ?: ObjectManager::getInstance()->get(ItemProviderInterface::class);
        $this->configReader = $configReader ?: ObjectManager::getInstance()->get(SitemapConfigReaderInterface::class);
        $this->sitemapItemFactory = $sitemapItemFactory ?: ObjectManager::getInstance()->get(
            \Magento\Sitemap\Model\SitemapItemInterfaceFactory::class
        );
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Init model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(SitemapResource::class);
    }

    /**
     * Get file handler
     *
     * @return \Magento\Framework\Filesystem\File\WriteInterface
     * @throws LocalizedException
     */
    protected function _getStream()
    {
        if ($this->_stream) {
            return $this->_stream;
        } else {
            throw new LocalizedException(__('File handler unreachable'));
        }
    }

    /**
     * Add a sitemap item to the array of sitemap items
     *
     * @param DataObject $sitemapItem
     * @return $this
     * @deprecated 100.3.0
     * @see ItemProviderInterface
     * @since 100.2.0
     */
    public function addSitemapItem(DataObject $sitemapItem)
    {
        $this->_sitemapItems[] = $sitemapItem;

        return $this;
    }

    /**
     * Collect all sitemap items
     *
     * @return void
     * @deprecated 100.3.0
     * @see ItemProviderInterface
     * @since 100.2.0
     */
    public function collectSitemapItems()
    {
        /** @var $helper \Magento\Sitemap\Helper\Data */
        $helper = $this->_sitemapData;
        $storeId = $this->getStoreId();

        $this->addSitemapItem(
            new DataObject(
                [
                    'changefreq' => $helper->getCategoryChangefreq($storeId),
                    'priority' => $helper->getCategoryPriority($storeId),
                    'collection' => $this->_categoryFactory->create()->getCollection($storeId),
                ]
            )
        );

        $this->addSitemapItem(
            new DataObject(
                [
                    'changefreq' => $helper->getProductChangefreq($storeId),
                    'priority' => $helper->getProductPriority($storeId),
                    'collection' => $this->_productFactory->create()->getCollection($storeId),
                ]
            )
        );

        $this->addSitemapItem(
            new DataObject(
                [
                    'changefreq' => $helper->getPageChangefreq($storeId),
                    'priority' => $helper->getPagePriority($storeId),
                    'collection' => $this->_cmsFactory->create()->getCollection($storeId),
                ]
            )
        );
    }

    /**
     * Initialize sitemap
     *
     * @return void
     */
    protected function _initSitemapItems()
    {
        $sitemapItems = $this->itemProvider->getItems($this->getStoreId());
        $mappedItems = $this->mapToSitemapItem();
        $this->_sitemapItems = array_merge($sitemapItems, $mappedItems);

        $this->_tags = [
            self::TYPE_INDEX => [
                self::OPEN_TAG_KEY => '<?xml version="1.0" encoding="UTF-8"?>' .
                    PHP_EOL .
                    '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' .
                    PHP_EOL,
                self::CLOSE_TAG_KEY => '</sitemapindex>',
            ],
            self::TYPE_URL => [
                self::OPEN_TAG_KEY => '<?xml version="1.0" encoding="UTF-8"?>' .
                    PHP_EOL .
                    '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' .
                    ' xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' .
                    PHP_EOL,
                self::CLOSE_TAG_KEY => '</urlset>',
            ],
        ];
    }

    /**
     * Check sitemap file location and permissions
     *
     * @return \Magento\Framework\Model\AbstractModel
     * @throws LocalizedException
     */
    public function beforeSave()
    {
        $path = $this->getSitemapPath();

        /**
         * Check path is allow
         */
        if ($path && preg_match('#\.\.[\\\/]#', $path)) {
            throw new LocalizedException(__('Please define a correct path.'));
        }
        /**
         * Check exists and writable path
         */
        if (!$this->_directory->isExist($path)) {
            throw new LocalizedException(
                __(
                    'Please create the specified folder "%1" before saving the sitemap.',
                    $this->_escaper->escapeHtml($this->getSitemapPath())
                )
            );
        }

        if (!$this->_directory->isWritable($path)) {
            throw new LocalizedException(
                __('Please make sure that "%1" is writable by the web-server.', $this->getSitemapPath())
            );
        }
        /**
         * Check allow filename
         */
        if (!preg_match('#^[a-zA-Z0-9_\.]+$#', $this->getSitemapFilename())) {
            throw new LocalizedException(
                __(
                    'Please use only letters (a-z or A-Z), numbers (0-9) or underscores (_) in the filename.'
                    . ' No spaces or other characters are allowed.'
                )
            );
        }
        if (!preg_match('#\.xml$#', $this->getSitemapFilename())) {
            $this->setSitemapFilename($this->getSitemapFilename() . '.xml');
        }

        $this->setSitemapPath(rtrim(str_replace(str_replace('\\', '/', $this->_getBaseDir()), '', $path), '/') . '/');

        return parent::beforeSave();
    }

    /**
     * Generate XML file
     *
     * @see http://www.sitemaps.org/protocol.html
     *
     * @return $this
     */
    public function generateXml()
    {
        $this->_initSitemapItems();

        /** @var $item SitemapItemInterface */
        foreach ($this->_sitemapItems as $item) {
            $xml = $this->_getSitemapRow(
                $item->getUrl(),
                $item->getUpdatedAt(),
                $item->getChangeFrequency(),
                $item->getPriority(),
                $item->getImages()
            );

            if ($this->_isSplitRequired($xml) && $this->_sitemapIncrement > 0) {
                $this->_finalizeSitemap();
            }

            if (!$this->_fileSize) {
                $this->_createSitemap();
            }

            $this->_writeSitemapRow($xml);
            // Increase counters
            $this->_lineCount++;
            $this->_fileSize += strlen($xml);
        }

        $this->_finalizeSitemap();

        if ($this->_sitemapIncrement == 1) {
            // In case when only one increment file was created use it as default sitemap
            $path = rtrim(
                $this->getSitemapPath(),
                '/'
            ) . '/' . $this->_getCurrentSitemapFilename(
                $this->_sitemapIncrement
            );
            $destination = rtrim($this->getSitemapPath(), '/') . '/' . $this->getSitemapFilename();

            $this->_directory->renameFile($path, $destination);
        } else {
            // Otherwise create index file with list of generated sitemaps
            $this->_createSitemapIndex();
        }

        $this->setSitemapTime($this->_dateModel->gmtDate('Y-m-d H:i:s'));
        $this->save();

        return $this;
    }

    /**
     * Generate sitemap index XML file
     *
     * @return void
     */
    protected function _createSitemapIndex()
    {
        $this->_createSitemap($this->getSitemapFilename(), self::TYPE_INDEX);
        for ($i = 1; $i <= $this->_sitemapIncrement; $i++) {
            $xml = $this->_getSitemapIndexRow($this->_getCurrentSitemapFilename($i), $this->_getCurrentDateTime());
            $this->_writeSitemapRow($xml);
        }
        $this->_finalizeSitemap(self::TYPE_INDEX);
    }

    /**
     * Get current date time
     *
     * @return string
     */
    protected function _getCurrentDateTime()
    {
        return (new \DateTime())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);
    }

    /**
     * Check is split required
     *
     * @param string $row
     * @return bool
     */
    protected function _isSplitRequired($row)
    {
        $storeId = $this->getStoreId();
        if ($this->_lineCount + 1 > $this->configReader->getMaximumLinesNumber($storeId)) {
            return true;
        }

        if ($this->_fileSize + strlen($row) > $this->configReader->getMaximumFileSize($storeId)) {
            return true;
        }

        return false;
    }

    /**
     * Get sitemap row
     *
     * @param string $url
     * @param null|string $lastmod
     * @param null|string $changefreq
     * @param null|string $priority
     * @param null|array|\Magento\Framework\DataObject $images
     * @return string
     * Sitemap images
     * @see http://support.google.com/webmasters/bin/answer.py?hl=en&answer=178636
     *
     * Sitemap PageMap
     * @see http://support.google.com/customsearch/bin/answer.py?hl=en&answer=1628213
     */
    protected function _getSitemapRow($url, $lastmod = null, $changefreq = null, $priority = null, $images = null)
    {
        $url = $this->_getUrl($url);
        $row = '<loc>' . $this->_escaper->escapeUrl($url) . '</loc>';
        if ($lastmod) {
            $row .= '<lastmod>' . $this->_getFormattedLastmodDate($lastmod) . '</lastmod>';
        }
        if ($changefreq) {
            $row .= '<changefreq>' . $this->_escaper->escapeHtml($changefreq) . '</changefreq>';
        }
        if ($priority) {
            $row .= sprintf('<priority>%.1f</priority>', $this->_escaper->escapeHtml($priority));
        }
        if ($images) {
            // Add Images to sitemap
            foreach ($images->getCollection() as $image) {
                $row .= '<image:image>';
                $row .= '<image:loc>' . $this->_escaper->escapeUrl($image->getUrl()) . '</image:loc>';
                $row .= '<image:title>' . $this->_escaper->escapeHtml($images->getTitle()) . '</image:title>';
                if ($image->getCaption()) {
                    $row .= '<image:caption>' . $this->_escaper->escapeHtml($image->getCaption()) . '</image:caption>';
                }
                $row .= '</image:image>';
            }
            // Add PageMap image for Google web search
            $row .= '<PageMap xmlns="http://www.google.com/schemas/sitemap-pagemap/1.0"><DataObject type="thumbnail">';
            $row .= '<Attribute name="name" value="' . $this->_escaper->escapeHtml($images->getTitle()) . '"/>';
            $row .= '<Attribute name="src" value="' . $this->_escaper->escapeUrl($images->getThumbnail()) . '"/>';
            $row .= '</DataObject></PageMap>';
        }

        return '<url>' . $row . '</url>';
    }

    /**
     * Get sitemap index row
     *
     * @param string $sitemapFilename
     * @param null|string $lastmod
     * @return string
     */
    protected function _getSitemapIndexRow($sitemapFilename, $lastmod = null)
    {
        $url = $this->getSitemapUrl($this->getSitemapPath(), $sitemapFilename);
        $row = '<loc>' . $this->_escaper->escapeUrl($url) . '</loc>';
        if ($lastmod) {
            $row .= '<lastmod>' . $this->_getFormattedLastmodDate($lastmod) . '</lastmod>';
        }

        return '<sitemap>' . $row . '</sitemap>';
    }

    /**
     * Create new sitemap file
     *
     * @param null|string $fileName
     * @param string $type
     * @return void
     * @throws LocalizedException
     */
    protected function _createSitemap($fileName = null, $type = self::TYPE_URL)
    {
        if (!$fileName) {
            $this->_sitemapIncrement++;
            $fileName = $this->_getCurrentSitemapFilename($this->_sitemapIncrement);
        }

        $path = rtrim($this->getSitemapPath(), '/') . '/' . $fileName;
        $this->_stream = $this->_directory->openFile($path);

        $fileHeader = sprintf($this->_tags[$type][self::OPEN_TAG_KEY], $type);
        $this->_stream->write($fileHeader);
        $this->_fileSize = strlen($fileHeader . sprintf($this->_tags[$type][self::CLOSE_TAG_KEY], $type));
    }

    /**
     * Write sitemap row
     *
     * @param string $row
     * @return void
     */
    protected function _writeSitemapRow($row)
    {
        $this->_getStream()->write($row . PHP_EOL);
    }

    /**
     * Write closing tag and close stream
     *
     * @param string $type
     * @return void
     */
    protected function _finalizeSitemap($type = self::TYPE_URL)
    {
        if ($this->_stream) {
            $this->_stream->write(sprintf($this->_tags[$type][self::CLOSE_TAG_KEY], $type));
            $this->_stream->close();
        }

        // Reset all counters
        $this->_lineCount = 0;
        $this->_fileSize = 0;
    }

    /**
     * Get current sitemap filename
     *
     * @param int $index
     * @return string
     */
    protected function _getCurrentSitemapFilename($index)
    {
        return str_replace('.xml', '', $this->getSitemapFilename()) . '-' . $this->getStoreId() . '-' . $index . '.xml';
    }

    /**
     * Get base dir
     *
     * @return string
     */
    protected function _getBaseDir()
    {
        return $this->_directory->getAbsolutePath();
    }

    /**
     * Get store base url
     *
     * @param string $type
     * @return string
     */
    protected function _getStoreBaseUrl($type = UrlInterface::URL_TYPE_LINK)
    {
        /** @var \Magento\Store\Model\Store $store */
        $store = $this->_storeManager->getStore($this->getStoreId());
        $isSecure = $store->isUrlSecure();
        return rtrim($store->getBaseUrl($type, $isSecure), '/') . '/';
    }

    /**
     * Get url
     *
     * @param string $url
     * @param string $type
     * @return string
     */
    protected function _getUrl($url, $type = UrlInterface::URL_TYPE_LINK)
    {
        return $this->_getStoreBaseUrl($type) . ltrim($url, '/');
    }

    /**
     * Get media url
     *
     * @param string $url
     * @return string
     * @deprecated 100.2.0 No longer used, as we're generating product image URLs inside collection instead
     * @see \Magento\Sitemap\Model\ResourceModel\Catalog\Product::_loadProductImages()
     */
    protected function _getMediaUrl($url)
    {
        return $this->_getUrl($url, UrlInterface::URL_TYPE_MEDIA);
    }

    /**
     * Get date in correct format applicable for lastmod attribute
     *
     * @param string $date
     * @return string
     */
    protected function _getFormattedLastmodDate($date)
    {
        if ($this->lastModMinTsVal === null) {
            $this->lastModMinTsVal = strtotime(self::LAST_MOD_MIN_VAL);
        }
        $timestamp = max(strtotime($date), $this->lastModMinTsVal);
        return date('c', $timestamp);
    }

    /**
     * Get Document root of Magento instance
     *
     * @return string
     */
    protected function _getDocumentRoot()
    {
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        return realpath($this->_request->getServer('DOCUMENT_ROOT'));
    }

    /**
     * Get domain from store base url
     *
     * @return string
     */
    protected function _getStoreBaseDomain()
    {
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $storeParsedUrl = parse_url($this->_getStoreBaseUrl());
        $url = $storeParsedUrl['scheme'] . '://' . $storeParsedUrl['host'];

        $documentRoot = trim(str_replace('\\', '/', $this->_getDocumentRoot()), '/');
        $baseDir = trim(str_replace('\\', '/', $this->_getBaseDir()), '/');

        if (strpos($baseDir, (string) $documentRoot) === 0) {
            //case when basedir is in document root
            $installationFolder = trim(str_replace($documentRoot, '', $baseDir), '/');
            $storeDomain = rtrim($url . '/' . $installationFolder, '/');
        } else {
            //case when documentRoot contains symlink to basedir
            $url = $this->_getStoreBaseUrl(UrlInterface::URL_TYPE_WEB);
            $storeDomain = rtrim($url, '/');
        }

        return $storeDomain;
    }

    /**
     * Get sitemap.xml URL according to all config options
     *
     * @param string $sitemapPath
     * @param string $sitemapFileName
     * @return string
     */
    public function getSitemapUrl($sitemapPath, $sitemapFileName)
    {
        return $this->_getStoreBaseDomain() . str_replace('//', '/', $sitemapPath . '/' . $sitemapFileName);
    }

    /**
     * Check is enabled submission to robots.txt
     *
     * @return bool
     * @deprecated 100.1.5 Because the robots.txt file is not generated anymore,
     *             this method is not needed and will be removed in major release.
     */
    protected function _isEnabledSubmissionRobots()
    {
        $storeId = $this->getStoreId();
        return (bool)$this->configReader->getEnableSubmissionRobots($storeId);
    }

    /**
     * Add sitemap file to robots.txt
     *
     * @param string $sitemapFileName
     * @return void
     * @deprecated 100.1.5 Because the robots.txt file is not generated anymore,
     *             this method is not needed and will be removed in major release.
     */
    protected function _addSitemapToRobotsTxt($sitemapFileName)
    {
        $robotsSitemapLine = 'Sitemap: ' . $this->getSitemapUrl($this->getSitemapPath(), $sitemapFileName);

        $filename = 'robots.txt';
        $content = '';
        if ($this->_directory->isExist($filename)) {
            $content = $this->_directory->readFile($filename);
        }

        if (strpos($content, (string) $robotsSitemapLine) === false) {
            if (!empty($content)) {
                $content .= $this->_findNewLinesDelimiter($content);
            }
            $content .= $robotsSitemapLine;
        }

        $this->_directory->writeFile($filename, $content);
    }

    /**
     * Find new lines delimiter
     *
     * @param string $text
     * @return string
     */
    private function _findNewLinesDelimiter($text)
    {
        foreach ($this->_crlf as $delimiter) {
            if (strpos($text, (string) $delimiter) !== false) {
                return $delimiter;
            }
        }

        return PHP_EOL;
    }

    /**
     * Sitemap item mapper for backwards compatibility
     *
     * @return array
     */
    private function mapToSitemapItem()
    {
        $items = [];

        foreach ($this->_sitemapItems as $data) {
            foreach ($data->getCollection() as $item) {
                $items[] = $this->sitemapItemFactory->create(
                    [
                        'url' => $item->getUrl(),
                        'updatedAt' => $item->getUpdatedAt(),
                        'images' => $item->getImages(),
                        'priority' => $data->getPriority(),
                        'changeFrequency' => $data->getChangeFrequency(),
                    ]
                );
            }
        }

        return $items;
    }

    /**
     * Get unique page cache identities
     *
     * @return array
     * @since 100.1.5
     */
    public function getIdentities()
    {
        return [
            Value::CACHE_TAG . '_' . $this->getStoreId(),
        ];
    }
}
