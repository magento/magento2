<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Magento_Sitemap
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Sitemap model
 *
 * @method \Magento\Sitemap\Model\Resource\Sitemap _getResource()
 * @method \Magento\Sitemap\Model\Resource\Sitemap getResource()
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
 *
 * @category    Magento
 * @package     Magento_Sitemap
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Sitemap\Model;

class Sitemap extends \Magento\Core\Model\AbstractModel
{
    const OPEN_TAG_KEY = 'start';
    const CLOSE_TAG_KEY = 'end';
    const INDEX_FILE_PREFIX = 'sitemap';
    const TYPE_INDEX = 'sitemap';
    const TYPE_URL = 'url';

    /**
     * Real file path
     *
     * @var string
     */
    protected $_filePath;

    /**
     * File handler
     *
     * @var \Magento\Io\File
     */
    protected $_fileHandler;

    /**
     * Sitemap items
     *
     * @var array
     */
    protected $_sitemapItems = array();

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
    protected $_tags = array();

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
    private $_crlf = array("win" => "\r\n", "unix" => "\n", "mac" => "\r");

    /**
     * @var \Magento\Filesystem $filesystem
     */
    protected $_filesystem;

    /**
     * Sitemap data
     *
     * @var \Magento\Sitemap\Helper\Data
     */
    protected $_sitemapData;

    /**
     * Core data
     *
     * @var \Magento\Core\Helper\Data
     */
    protected $_coreData;

    /**
     * @var \Magento\Sitemap\Model\Resource\Catalog\CategoryFactory
     */
    protected $_categoryFactory;

    /**
     * @var \Magento\Sitemap\Model\Resource\Catalog\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Magento\Sitemap\Model\Resource\Cms\PageFactory
     */
    protected $_cmsFactory;

    /**
     * @var \Magento\Core\Model\Date
     */
    protected $_dateModel;

    /**
     * @var \Magento\App\Dir
     */
    protected $_dirModel;

    /**
     * @var \Magento\Core\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\App\RequestInterface
     */
    protected $_request;

    /**
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Sitemap\Helper\Data $sitemapData
     * @param \Magento\Core\Model\Context $context
     * @param \Magento\Filesystem $filesystem
     * @param \Magento\Core\Model\Registry $registry
     * @param \Magento\Sitemap\Model\Resource\Catalog\CategoryFactory $categoryFactory
     * @param \Magento\Sitemap\Model\Resource\Catalog\ProductFactory $productFactory
     * @param \Magento\Sitemap\Model\Resource\Cms\PageFactory $cmsFactory
     * @param \Magento\Core\Model\Date $modelDate
     * @param \Magento\App\Dir $dirModel
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\App\RequestInterface $request
     * @param \Magento\Core\Model\Resource\AbstractResource $resource
     * @param \Magento\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Core\Helper\Data $coreData,
        \Magento\Sitemap\Helper\Data $sitemapData,
        \Magento\Core\Model\Context $context,
        \Magento\Filesystem $filesystem,
        \Magento\Core\Model\Registry $registry,
        \Magento\Sitemap\Model\Resource\Catalog\CategoryFactory $categoryFactory,
        \Magento\Sitemap\Model\Resource\Catalog\ProductFactory $productFactory,
        \Magento\Sitemap\Model\Resource\Cms\PageFactory $cmsFactory,
        \Magento\Core\Model\Date $modelDate,
        \Magento\App\Dir $dirModel,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\App\RequestInterface $request,
        \Magento\Core\Model\Resource\AbstractResource $resource = null,
        \Magento\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->_coreData = $coreData;
        $this->_sitemapData = $sitemapData;
        $this->_filesystem = $filesystem;
        $this->_categoryFactory = $categoryFactory;
        $this->_productFactory = $productFactory;
        $this->_cmsFactory = $cmsFactory;
        $this->_dateModel = $modelDate;
        $this->_dirModel = $dirModel;
        $this->_storeManager = $storeManager;
        $this->_request = $request;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Init model
     */
    protected function _construct()
    {
        $this->_init('Magento\Sitemap\Model\Resource\Sitemap');
    }

    /**
     * Get file handler
     *
     * @throws \Magento\Core\Exception
     * @return \Magento\Io\File
     */
    protected function _getFileHandler()
    {
        if ($this->_fileHandler) {
            return $this->_fileHandler;
        } else {
            throw new \Magento\Core\Exception(__('File handler unreachable'));
        }
    }

    /**
     * Initialize sitemap items
     */
    protected function _initSitemapItems()
    {
        /** @var $helper \Magento\Sitemap\Helper\Data */
        $helper = $this->_sitemapData;
        $storeId = $this->getStoreId();

        $this->_sitemapItems[] = new \Magento\Object(array(
            'changefreq' => $helper->getCategoryChangefreq($storeId),
            'priority' => $helper->getCategoryPriority($storeId),
            'collection' => $this->_categoryFactory->create()->getCollection($storeId)
        ));

        $this->_sitemapItems[] = new \Magento\Object(array(
            'changefreq' => $helper->getProductChangefreq($storeId),
            'priority' => $helper->getProductPriority($storeId),
            'collection' => $this->_productFactory->create()->getCollection($storeId)
        ));

        $this->_sitemapItems[] = new \Magento\Object(array(
            'changefreq' => $helper->getPageChangefreq($storeId),
            'priority' => $helper->getPagePriority($storeId),
            'collection' => $this->_cmsFactory->create()->getCollection($storeId)
        ));

        $this->_tags = array(
            self::TYPE_INDEX => array(
                self::OPEN_TAG_KEY => '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL
                . '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL,
                self::CLOSE_TAG_KEY => '</sitemapindex>'
            ),
            self::TYPE_URL => array(
                self::OPEN_TAG_KEY => '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL
                . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"'
                . ' xmlns:content="http://www.google.com/schemas/sitemap-content/1.0"'
                . ' xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . PHP_EOL,
                self::CLOSE_TAG_KEY => '</urlset>'
            )
        );
    }

    /**
     * Check sitemap file location and permissions
     *
     * @throws \Magento\Core\Exception
     * @return \Magento\Core\Model\AbstractModel
     */
    protected function _beforeSave()
    {
        $file = $this->_getFileObject();
        $realPath = $file->getCleanPath($this->_getBaseDir() . '/' . $this->getSitemapPath());

        /**
         * Check path is allow
         */
        /** @var $helper \Magento\Sitemap\Helper\Data */
        $helper = $this->_sitemapData;
        if (!$file->allowedPath($realPath, $this->_getBaseDir())) {
            throw new \Magento\Core\Exception(__('Please define a correct path.'));
        }
        /**
         * Check exists and writeable path
         */
        if (!$file->fileExists($realPath, false)) {
            throw new \Magento\Core\Exception(__('Please create the specified folder "%1" before saving the sitemap.',
                $this->_coreData->escapeHtml($this->getSitemapPath())));
        }

        if (!$file->isWriteable($realPath)) {
            throw new \Magento\Core\Exception(__('Please make sure that "%1" is writable by the web-server.',
                $this->getSitemapPath()));
        }
        /**
         * Check allow filename
         */
        if (!preg_match('#^[a-zA-Z0-9_\.]+$#', $this->getSitemapFilename())) {
            throw new \Magento\Core\Exception(__('Please use only letters (a-z or A-Z), numbers (0-9) or underscores (_) in the filename. No spaces or other characters are allowed.'));
        }
        if (!preg_match('#\.xml$#', $this->getSitemapFilename())) {
            $this->setSitemapFilename($this->getSitemapFilename() . '.xml');
        }

        $this->setSitemapPath(
            rtrim(str_replace(str_replace('\\', '/', $this->_getBaseDir()), '', $realPath), '/') . '/');

        return parent::_beforeSave();
    }

    /**
     * Generate XML file
     *
     * @see http://www.sitemaps.org/protocol.html
     *
     * @return \Magento\Sitemap\Model\Sitemap
     */
    public function generateXml()
    {
        $this->_initSitemapItems();
        /** @var $sitemapItem \Magento\Object */
        foreach ($this->_sitemapItems as $sitemapItem) {
            $changefreq = $sitemapItem->getChangefreq();
            $priority = $sitemapItem->getPriority();
            foreach ($sitemapItem->getCollection() as $item) {
                $xml = $this->_getSitemapRow(
                    $item->getUrl(),
                    $item->getUpdatedAt(),
                    $changefreq,
                    $priority,
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
        }
        $this->_finalizeSitemap();

        if ($this->_sitemapIncrement == 1) {
            // In case when only one increment file was created use it as default sitemap
            $this->_getFileHandler()
                ->mv($this->_getCurrentSitemapFilename($this->_sitemapIncrement), $this->getSitemapFilename());
        } else {
            // Otherwise create index file with list of generated sitemaps
            $this->_createSitemapIndex();
        }

        // Push sitemap to robots.txt
        if ($this->_isEnabledSubmissionRobots()) {
            $this->_addSitemapToRobotsTxt($this->getSitemapFilename());
        }

        $this->setSitemapTime($this->_dateModel->gmtDate('Y-m-d H:i:s'));
        $this->save();

        return $this;
    }

    /**
     * Generate sitemap index XML file
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
        $date = new \Magento\Date();
        return $date->now();
    }

    /**
     * Check is split required
     *
     * @param string $row
     * @return bool
     */
    protected function _isSplitRequired($row)
    {
        /** @var $helper \Magento\Sitemap\Helper\Data */
        $helper = $this->_sitemapData;
        $storeId = $this->getStoreId();
        if ($this->_lineCount + 1 > $helper->getMaximumLinesNumber($storeId)) {
            return true;
        }

        if ($this->_fileSize + strlen($row) > $helper->getMaximumFileSize($storeId)) {
            return true;
        }

        return false;
    }

    /**
     * Get sitemap row
     *
     * Sitemap images
     * @see http://support.google.com/webmasters/bin/answer.py?hl=en&answer=178636
     *
     * Sitemap PageMap
     * @see http://support.google.com/customsearch/bin/answer.py?hl=en&answer=1628213
     *
     * @param string $url
     * @param string $lastmod
     * @param string $changefreq
     * @param string $priority
     * @param array $images
     * @return string
     */
    protected function _getSitemapRow($url, $lastmod = null, $changefreq = null, $priority = null, $images = null)
    {
        $url = $this->_getUrl($url);
        $row = '<loc>' . htmlspecialchars($url) . '</loc>';
        if ($lastmod) {
            $row .= '<lastmod>' . $this->_getFormattedLastmodDate($lastmod) . '</lastmod>';
        }
        if ($changefreq) {
            $row .= '<changefreq>' . $changefreq . '</changefreq>';
        }
        if ($priority) {
            $row .= sprintf('<priority>%.1f</priority>', $priority);
        }
        if ($images) {
            // Add Images to sitemap
            foreach ($images->getCollection() as $image) {
                $row .= '<image:image>';
                $row .= '<image:loc>' . htmlspecialchars($this->_getMediaUrl($image->getUrl())) . '</image:loc>';
                $row .= '<image:title>' . htmlspecialchars($images->getTitle()) . '</image:title>';
                if ($image->getCaption()) {
                    $row .= '<image:caption>' . htmlspecialchars($image->getCaption()) . '</image:caption>';
                }
                $row .= '</image:image>';
            }
            // Add PageMap image for Google web search
            $row .= '<PageMap xmlns="http://www.google.com/schemas/sitemap-pagemap/1.0"><DataObject type="thumbnail">';
            $row .= '<Attribute name="name" value="' . htmlspecialchars($images->getTitle()) .'"/>';
            $row .= '<Attribute name="src" value="'
                . htmlspecialchars($this->_getMediaUrl($images->getThumbnail())) . '"/>';
            $row .= '</DataObject></PageMap>';
        }

        return '<url>' . $row . '</url>';
    }

    /**
     * Get sitemap index row
     *
     * @param string $sitemapFilename
     * @param string $lastmod
     * @return string
     */
    protected function _getSitemapIndexRow($sitemapFilename, $lastmod = null)
    {
        $url = $this->getSitemapUrl($this->getSitemapPath(), $sitemapFilename);
        $row = '<loc>' . htmlspecialchars($url) . '</loc>';
        if ($lastmod) {
            $row .= '<lastmod>' . $this->_getFormattedLastmodDate($lastmod) . '</lastmod>';
        }

        return '<sitemap>' . $row . '</sitemap>';
    }

    /**
     * Create new sitemap file
     *
     * @param string $fileName
     * @param string $type
     * @return void
     */
    protected function _createSitemap($fileName = null, $type = self::TYPE_URL)
    {
        if (!$fileName) {
            $this->_sitemapIncrement++;
            $fileName = $this->_getCurrentSitemapFilename($this->_sitemapIncrement);
        }
        $this->_fileHandler = $this->_getFileObject();
        $this->_fileHandler->setAllowCreateFolders(true);

        $path = $this->_fileHandler->getCleanPath($this->_getBaseDir() . $this->getSitemapPath());
        $this->_fileHandler->open(array('path' => $path));

        if ($this->_fileHandler->fileExists($fileName) && !$this->_fileHandler->isWriteable($fileName)) {
            throw new \Magento\Core\Exception(
                __('File "%1" cannot be saved. Please, make sure the directory "%2" is writable by web server.',
                    $fileName, $path
                )
            );
        }

        $fileHeader = sprintf($this->_tags[$type][self::OPEN_TAG_KEY], $type);
        $this->_fileHandler->streamOpen($fileName);
        $this->_fileHandler->streamWrite($fileHeader);

        $this->_fileSize = strlen($fileHeader . sprintf($this->_tags[$type][self::CLOSE_TAG_KEY], $type));
    }

    /**
     * Write sitemap row
     *
     * @param string $row
     */
    protected function _writeSitemapRow($row)
    {
        $this->_getFileHandler()->streamWrite($row . PHP_EOL);
    }

    /**
     * Write closing tag and close stream
     *
     * @param string $type
     */
    protected function _finalizeSitemap($type = self::TYPE_URL)
    {
        if ($this->_fileHandler) {
            $this->_fileHandler->streamWrite(sprintf($this->_tags[$type][self::CLOSE_TAG_KEY], $type));
            $this->_fileHandler->streamClose();
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
        return self::INDEX_FILE_PREFIX . '-' . $this->getStoreId() . '-' . $index . '.xml';
    }

    /**
     * Get base dir
     *
     * @return string
     */
    protected function _getBaseDir()
    {
        return $this->_dirModel->getDir(\Magento\App\Dir::ROOT);
    }

    /**
     * Get file object
     *
     * @return \Magento\Io\File
     */
    protected function _getFileObject()
    {
        return new \Magento\Io\File();
    }

    /**
     * Get store base url
     *
     * @param string $type
     * @return string
     */
    protected function _getStoreBaseUrl($type = \Magento\Core\Model\Store::URL_TYPE_LINK)
    {
        return rtrim($this->_storeManager->getStore($this->getStoreId())->getBaseUrl($type), '/') . '/';
    }

    /**
     * Get url
     *
     * @param string $url
     * @param string $type
     * @return string
     */
    protected function _getUrl($url, $type = \Magento\Core\Model\Store::URL_TYPE_LINK)
    {
        return $this->_getStoreBaseUrl($type) . ltrim($url, '/');
    }

    /**
     * Get media url
     *
     * @param string $url
     * @return string
     */
    protected function _getMediaUrl($url)
    {
        return $this->_getUrl($url, \Magento\Core\Model\Store::URL_TYPE_MEDIA);
    }

    /**
     * Get date in correct format applicable for lastmod attribute
     *
     * @param string $date
     * @return string
     */
    protected function _getFormattedLastmodDate($date)
    {
        return date('c', strtotime($date));
    }

    /**
     * Get Document root of Magento instance
     *
     * @return string
     */
    protected function _getDocumentRoot()
    {
        return $this->_request->getServer('DOCUMENT_ROOT');
    }

    /**
     * Get domain from store base url
     *
     * @return string
     */
    protected function _getStoreBaseDomain()
    {
        $storeParsedUrl = parse_url($this->_getStoreBaseUrl());
        $url = $storeParsedUrl['scheme'] . '://' . $storeParsedUrl['host'];

        $documentRoot = trim(str_replace('\\', '/', $this->_getDocumentRoot()), '/');
        $baseDir = trim(str_replace('\\', '/', $this->_getBaseDir()), '/');

        if ($this->_getFilesystem()->isPathInDirectory($baseDir, $documentRoot)) {
            //case when basedir is in document root
            $installationFolder = trim(str_replace($documentRoot, '', $baseDir), '/');
            $storeDomain = rtrim($url . '/' . $installationFolder, '/');
        } else {
            //case when documentRoot contains symlink to basedir
            $url = $this->_getStoreBaseUrl(\Magento\Core\Model\Store::URL_TYPE_WEB);
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
     */
    protected function _isEnabledSubmissionRobots()
    {
        /** @var $helper \Magento\Sitemap\Helper\Data */
        $helper = $this->_sitemapData;
        $storeId = $this->getStoreId();
        return (bool) $helper->getEnableSubmissionRobots($storeId);
    }

    /**
     * Add sitemap file to robots.txt
     *
     * @param string $sitemapFileName
     */
    protected function _addSitemapToRobotsTxt($sitemapFileName)
    {
        $robotsSitemapLine = 'Sitemap: ' . $this->getSitemapUrl($this->getSitemapPath(), $sitemapFileName);

        $robotsFileHandler = $this->_getFileObject();
        $robotsFileName = $robotsFileHandler->getCleanPath($this->_getBaseDir() . '/robots.txt');
        $robotsFullText = '';
        if ($robotsFileHandler->fileExists($robotsFileName)) {
            $robotsFileHandler->open(array('path' => $robotsFileHandler->getDestinationFolder($robotsFileName)));
            $robotsFullText = $robotsFileHandler->read($robotsFileName);
        }

        if (strpos($robotsFullText, $robotsSitemapLine) === false) {
            if (!empty($robotsFullText)) {
                $robotsFullText .= $this->_findNewLinesDelimiter($robotsFullText);
            }
            $robotsFullText .= $robotsSitemapLine;
        }

        $robotsFileHandler->write($robotsFileName, $robotsFullText);
    }

    /**
     * Get \Magento\Filesystem object
     *
     * @return \Magento\Filesystem
     */
    protected function _getFilesystem()
    {
        return $this->_filesystem;
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
            if (strpos($text, $delimiter) !== false) {
                return $delimiter;
            }
        }

        return PHP_EOL;
    }

}
