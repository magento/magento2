<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sitemap\Model\Batch;

use Magento\Config\Model\Config\Reader\Source\Deployed\DocumentRoot;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Sitemap\Helper\Data;
use Magento\Sitemap\Model\ItemProvider\Category;
use Magento\Sitemap\Model\ItemProvider\CmsPage;
use Magento\Sitemap\Model\ItemProvider\ItemProviderInterface;
use Magento\Sitemap\Model\ItemProvider\ProductConfigReader;
use Magento\Sitemap\Model\ItemProvider\StoreUrl;
use Magento\Sitemap\Model\ResourceModel\Catalog\ProductFactory as BaseProductFactory;
use Magento\Sitemap\Model\ResourceModel\Catalog\Batch\ProductFactory;
use Magento\Sitemap\Model\ResourceModel\Catalog\CategoryFactory;
use Magento\Sitemap\Model\ResourceModel\Cms\PageFactory;
use Magento\Sitemap\Model\Sitemap as BaseSitemap;
use Magento\Sitemap\Model\SitemapConfigReaderInterface;
use Magento\Sitemap\Model\SitemapItemInterface;
use Magento\Sitemap\Model\SitemapItemInterfaceFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Memory-optimized sitemap model using batch processing for large catalogs
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Sitemap extends BaseSitemap
{
    /**
     * @var Category|null
     */
    private ?Category $categoryProvider;

    /**
     * @var CmsPage|null
     */
    private ?CmsPage $cmsPageProvider;

    /**
     * @var StoreUrl|null
     */
    private ?StoreUrl $storeUrlProvider;

    /**
     * @var ProductFactory|null
     */
    private ?ProductFactory $batchProductFactory;

    /**
     * @var Filesystem\Directory\Write
     */
    private Filesystem\Directory\Write $tmpDirectory;

    /**
     * @var SitemapItemInterfaceFactory|null
     */
    private ?SitemapItemInterfaceFactory $sitemapItemFactory;

    /**
     * @var ProductConfigReader|null
     */
    private ?ProductConfigReader $productConfigReader;

    /**
     * Batch Sitemap constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param Escaper $escaper
     * @param Data $sitemapData
     * @param Filesystem $filesystem
     * @param CategoryFactory $categoryFactory
     * @param BaseProductFactory $productFactory
     * @param PageFactory $cmsFactory
     * @param DateTime $modelDate
     * @param StoreManagerInterface $storeManager
     * @param RequestInterface $request
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     * @param DocumentRoot|null $documentRoot
     * @param ItemProviderInterface|null $itemProvider
     * @param SitemapConfigReaderInterface|null $configReader
     * @param SitemapItemInterfaceFactory|null $sitemapItemFactory
     * @param Category|null $categoryProvider
     * @param CmsPage|null $cmsPageProvider
     * @param StoreUrl|null $storeUrlProvider
     * @param ProductFactory|null $batchProductFactory
     * @param ProductConfigReader|null $productConfigReader
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context                            $context,
        Registry                           $registry,
        Escaper                            $escaper,
        Data                               $sitemapData,
        Filesystem                         $filesystem,
        CategoryFactory                    $categoryFactory,
        BaseProductFactory                 $productFactory,
        PageFactory                        $cmsFactory,
        DateTime                           $modelDate,
        StoreManagerInterface              $storeManager,
        RequestInterface                   $request,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        ?AbstractResource                  $resource = null,
        ?AbstractDb                        $resourceCollection = null,
        array                              $data = [],
        ?DocumentRoot                      $documentRoot = null,
        ?ItemProviderInterface             $itemProvider = null,
        ?SitemapConfigReaderInterface      $configReader = null,
        ?SitemapItemInterfaceFactory       $sitemapItemFactory = null,
        ?Category                          $categoryProvider = null,
        ?CmsPage                           $cmsPageProvider = null,
        ?StoreUrl                          $storeUrlProvider = null,
        ?ProductFactory                    $batchProductFactory = null,
        ?ProductConfigReader               $productConfigReader = null
    ) {
        $this->categoryProvider = $categoryProvider ?? ObjectManager::getInstance()->get(Category::class);
        $this->cmsPageProvider = $cmsPageProvider ?? ObjectManager::getInstance()->get(CmsPage::class);
        $this->storeUrlProvider = $storeUrlProvider ?? ObjectManager::getInstance()->get(StoreUrl::class);
        $this->batchProductFactory = $batchProductFactory ??
            ObjectManager::getInstance()->get(ProductFactory::class);
        $this->productConfigReader = $productConfigReader ??
            ObjectManager::getInstance()->get(ProductConfigReader::class);
        $this->sitemapItemFactory = $sitemapItemFactory ??
            ObjectManager::getInstance()->get(SitemapItemInterfaceFactory::class);

        parent::__construct(
            $context,
            $registry,
            $escaper,
            $sitemapData,
            $filesystem,
            $categoryFactory,
            $productFactory,
            $cmsFactory,
            $modelDate,
            $storeManager,
            $request,
            $dateTime,
            $resource,
            $resourceCollection,
            $data,
            $documentRoot,
            $itemProvider,
            $configReader,
            $sitemapItemFactory
        );
    }

    /**
     * Initialize sitemap items using batch processing
     *
     * @return void
     */
    protected function _initSitemapItems()
    {
        // Only collect non-product items
        $sitemapItems = [];

        $storeUrlItems = $this->storeUrlProvider->getItems($this->getStoreId());
        $sitemapItems = array_merge($sitemapItems, $storeUrlItems);

        $categoryItems = $this->categoryProvider->getItems($this->getStoreId());
        $sitemapItems = array_merge($sitemapItems, $categoryItems);

        $cmsPageItems = $this->cmsPageProvider->getItems($this->getStoreId());
        $sitemapItems = array_merge($sitemapItems, $cmsPageItems);

        // Store only non-product items (small collections)
        $this->_sitemapItems = $sitemapItems;

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
     * Generate XML sitemap using streaming for products to avoid memory issues
     *
     * @return $this
     * @throws FileSystemException|\Exception
     */
    public function generateXml()
    {
        $this->_initSitemapItems();

        // First process all non-product items (stored in _sitemapItems)
        foreach ($this->_sitemapItems as $item) {
            $this->processSitemapItem($item);
        }

        // Then stream products using batch processing
        $this->streamProducts();

        $this->_finalizeSitemap();

        if ($this->_sitemapIncrement == 1) {
            // In case when only one increment file was created use it as default sitemap
            $path = $this->getFilePath($this->_getCurrentSitemapFilename($this->_sitemapIncrement));
            $destination = $this->getFilePath($this->getSitemapFilename());
            $this->tmpDirectory->renameFile($path, $destination, $this->_directory);
        } else {
            // Otherwise create index file with list of generated sitemaps
            $this->_createSitemapIndex();
        }

        $this->setSitemapTime($this->_dateModel->gmtDate('Y-m-d H:i:s'));
        $this->save();

        return $this;
    }

    /**
     * Stream products using batch processing to avoid loading all into memory
     *
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws \Zend_Db_Statement_Exception
     */
    private function streamProducts(): void
    {
        $batchProductFactory = $this->batchProductFactory;
        $itemFactory = $this->sitemapItemFactory;
        $configReader = $this->productConfigReader;

        $batchProductResource = $batchProductFactory->create();
        $productCollection = $batchProductResource->getCollection($this->getStoreId());

        if ($productCollection === false) {
            return;
        }

        foreach ($productCollection as $product) {
            $sitemapItem = $itemFactory->create(
                [
                'url' => $product->getUrl(),
                'updatedAt' => $product->getUpdatedAt(),
                'images' => $product->getImages(),
                'priority' => $configReader->getPriority($this->getStoreId()),
                'changeFrequency' => $configReader->getChangeFrequency($this->getStoreId()),
                ]
            );

            $this->processSitemapItem($sitemapItem);

            unset($sitemapItem, $product);
        }
    }

    /**
     * Process a single sitemap item
     *
     * @param  SitemapItemInterface $item
     * @return void
     * @throws LocalizedException
     */
    private function processSitemapItem($item): void
    {
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

        $this->_lineCount++;
        $this->_fileSize += strlen($xml);
    }
}
