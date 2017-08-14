<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sitemap\Test\Unit\Model;

use Magento\Framework\DataObject;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Write as DirectoryWrite;
use Magento\Framework\Filesystem\File\Write;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sitemap\Helper\Data;
use Magento\Sitemap\Model\ItemResolver\ConfigReaderInterface;
use Magento\Sitemap\Model\ItemResolver\ItemResolverInterface;
use Magento\Sitemap\Model\ResourceModel\Catalog\Category;
use Magento\Sitemap\Model\ResourceModel\Catalog\CategoryFactory;
use Magento\Sitemap\Model\ResourceModel\Catalog\Product;
use Magento\Sitemap\Model\ResourceModel\Catalog\ProductFactory;
use Magento\Sitemap\Model\ResourceModel\Cms\Page;
use Magento\Sitemap\Model\ResourceModel\Cms\PageFactory;
use Magento\Sitemap\Model\ResourceModel\Sitemap as SitemapResource;
use Magento\Sitemap\Model\Sitemap;
use Magento\Sitemap\Model\SitemapConfigReaderInterface;
use Magento\Sitemap\Model\SitemapItem;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SitemapTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Data
     */
    private $helperMockSitemap;

    /**
     * @var SitemapResource
     */
    private $resourceMock;

    /**
     * @var Category
     */
    private $sitemapCategoryMock;

    /**
     * @var Product
     */
    private $sitemapProductMock;

    /**
     * @var Page
     */
    private $sitemapCmsPageMock;

    /**
     * @var Filesystem
     */
    private $filesystemMock;

    /**
     * @var DirectoryWrite
     */
    private $directoryMock;

    /**
     * @var Write
     */
    private $fileMock;

    /**
     * @var StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    /**
     * @var ItemResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $itemResolverMock;

    /**
     * @var ConfigReaderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configReaderMock;

    /**
     * Set helper mocks, create resource model mock
     */
    protected function setUp()
    {
        $this->sitemapCategoryMock = $this->getMockBuilder(Category::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->sitemapProductMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->sitemapCmsPageMock = $this->getMockBuilder(Page::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->helperMockSitemap = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->getMock();

        $resourceMethods = [
            '_construct',
            'beginTransaction',
            'rollBack',
            'save',
            'addCommitCallback',
            'commit',
            '__wakeup',
        ];

        $this->resourceMock = $this->getMockBuilder(SitemapResource::class)
            ->setMethods($resourceMethods)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resourceMock->expects($this->any())
            ->method('addCommitCallback')
            ->willReturnSelf();

        $this->fileMock = $this->getMockBuilder(Write::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->directoryMock = $this->getMockBuilder(DirectoryWrite::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->directoryMock->expects($this->any())
            ->method('openFile')
            ->willReturn($this->fileMock);

        $this->filesystemMock = $this->getMockBuilder(Filesystem::class)
            ->setMethods(['getDirectoryWrite'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->filesystemMock->expects($this->any())
            ->method('getDirectoryWrite')
            ->willReturn($this->directoryMock);

        $this->configReaderMock = $this->getMockForAbstractClass(SitemapConfigReaderInterface::class);
        $this->itemResolverMock = $this->getMockForAbstractClass(ItemResolverInterface::class);
    }

    /**
     * Check not allowed sitemap path validation
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Please define a correct path.
     */
    public function testNotAllowedPath()
    {
        $model = $this->getModelMock();
        $model->setSitemapPath('../');
        $model->beforeSave();
    }

    /**
     * Check not exists sitemap path validation
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Please create the specified folder "" before saving the sitemap.
     */
    public function testPathNotExists()
    {
        $this->directoryMock->expects($this->once())
            ->method('isExist')
            ->willReturn(false);

        $model = $this->getModelMock();
        $model->beforeSave();
    }

    /**
     * Check not writable sitemap path validation
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Please make sure that "/" is writable by the web-server.
     */
    public function testPathNotWritable()
    {
        $this->directoryMock->expects($this->once())
            ->method('isExist')
            ->willReturn(true);

        $this->directoryMock->expects($this->once())
            ->method('isWritable')
            ->willReturn(false);

        $model = $this->getModelMock();
        $model->beforeSave();
    }

    //@codingStandardsIgnoreStart
    /**
     * Check invalid chars in sitemap filename validation
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Please use only letters (a-z or A-Z), numbers (0-9) or underscores (_) in the filename.
     * No spaces or other characters are allowed.
     */
    //@codingStandardsIgnoreEnd
    public function testFilenameInvalidChars()
    {
        $this->directoryMock->expects($this->once())
            ->method('isExist')
            ->willReturn(true);

        $this->directoryMock->expects($this->once())
            ->method('isWritable')
            ->willReturn(true);

        $model = $this->getModelMock();
        $model->setSitemapFilename('*sitemap?.xml');
        $model->beforeSave();
    }

    /**
     * Data provider for sitemaps
     *
     * 1) Limit set to 50000 urls and 10M per sitemap file (single file)
     * 2) Limit set to 1 url and 10M per sitemap file (multiple files, 1 record per file)
     * 3) Limit set to 50000 urls and 264 bytes per sitemap file (multiple files, 1 record per file)
     *
     * @static
     * @return array
     */
    public static function sitemapDataProvider()
    {
        $expectedSingleFile = ['/sitemap-1-1.xml' => __DIR__ . '/_files/sitemap-single.xml'];

        $expectedMultiFile = [
            '/sitemap-1-1.xml' => __DIR__ . '/_files/sitemap-1-1.xml',
            '/sitemap-1-2.xml' => __DIR__ . '/_files/sitemap-1-2.xml',
            '/sitemap-1-3.xml' => __DIR__ . '/_files/sitemap-1-3.xml',
            '/sitemap-1-4.xml' => __DIR__ . '/_files/sitemap-1-4.xml',
            '/sitemap.xml' => __DIR__ . '/_files/sitemap-index.xml',
        ];

        return [
            [50000, 10485760, $expectedSingleFile, 6],
            [1, 10485760, $expectedMultiFile, 18],
            [50000, 264, $expectedMultiFile, 18],
        ];
    }

    /**
     * Check generation of sitemaps
     *
     * @param int $maxLines
     * @param int $maxFileSize
     * @param array $expectedFile
     * @param int $expectedWrites
     * @dataProvider sitemapDataProvider
     */
    public function testGenerateXml($maxLines, $maxFileSize, $expectedFile, $expectedWrites)
    {
        $actualData = [];
        $model = $this->prepareSitemapModelMock(
            $actualData,
            $maxLines,
            $maxFileSize,
            $expectedFile,
            $expectedWrites,
            null
        );
        $model->generateXml();

        $this->assertCount(count($expectedFile), $actualData, 'Number of generated files is incorrect');
        foreach ($expectedFile as $expectedFileName => $expectedFilePath) {
            $this->assertArrayHasKey(
                $expectedFileName,
                $actualData,
                sprintf('File %s was not generated', $expectedFileName)
            );
            $this->assertXmlStringEqualsXmlFile($expectedFilePath, $actualData[$expectedFileName]);
        }
    }

    /**
     * Data provider for robots.txt
     *
     * @static
     * @return array
     */
    public static function robotsDataProvider()
    {
        $expectedSingleFile = ['/sitemap-1-1.xml' => __DIR__ . '/_files/sitemap-single.xml'];

        $expectedMultiFile = [
            '/sitemap-1-1.xml' => __DIR__ . '/_files/sitemap-1-1.xml',
            '/sitemap-1-2.xml' => __DIR__ . '/_files/sitemap-1-2.xml',
            '/sitemap-1-3.xml' => __DIR__ . '/_files/sitemap-1-3.xml',
            '/sitemap-1-4.xml' => __DIR__ . '/_files/sitemap-1-4.xml',
            '/sitemap.xml' => __DIR__ . '/_files/sitemap-index.xml',
        ];

        return [
            [
                50000,
                10485760,
                $expectedSingleFile,
                6,
                [
                    'robotsStart' => '',
                    'robotsFinish' => 'Sitemap: http://store.com/sitemap.xml',
                    'pushToRobots' => 1
                ],
            ], // empty robots file
            [
                50000,
                10485760,
                $expectedSingleFile,
                6,
                [
                    'robotsStart' => "User-agent: *",
                    'robotsFinish' => "User-agent: *" . PHP_EOL . 'Sitemap: http://store.com/sitemap.xml',
                    'pushToRobots' => 1
                ]
            ], // not empty robots file EOL
            [
                1,
                10485760,
                $expectedMultiFile,
                18,
                [
                    'robotsStart' => "User-agent: *\r\n",
                    'robotsFinish' => "User-agent: *\r\n\r\nSitemap: http://store.com/sitemap.xml",
                    'pushToRobots' => 1
                ]
            ], // not empty robots file WIN
            [
                50000,
                264,
                $expectedMultiFile,
                18,
                [
                    'robotsStart' => "User-agent: *\n",
                    'robotsFinish' => "User-agent: *\n\nSitemap: http://store.com/sitemap.xml",
                    'pushToRobots' => 1
                ]
            ], // not empty robots file UNIX
            [
                50000,
                10485760,
                $expectedSingleFile,
                6,
                ['robotsStart' => '', 'robotsFinish' => '', 'pushToRobots' => 0]
            ] // empty robots file
        ];
    }

    /**
     * Check pushing of sitemaps to robots.txt
     *
     * @param int $maxLines
     * @param int $maxFileSize
     * @param array $expectedFile
     * @param int $expectedWrites
     * @param array $robotsInfo
     * @dataProvider robotsDataProvider
     */
    public function testAddSitemapToRobotsTxt($maxLines, $maxFileSize, $expectedFile, $expectedWrites, $robotsInfo)
    {
        $actualData = [];
        $model = $this->prepareSitemapModelMock(
            $actualData,
            $maxLines,
            $maxFileSize,
            $expectedFile,
            $expectedWrites,
            $robotsInfo
        );
        $model->generateXml();
    }

    /**
     * Prepare mock of Sitemap model
     *
     * @param array $actualData
     * @param int $maxLines
     * @param int $maxFileSize
     * @param array $expectedFile
     * @param int $expectedWrites
     * @param array $robotsInfo
     * @return Sitemap|PHPUnit_Framework_MockObject_MockObject
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function prepareSitemapModelMock(
        &$actualData,
        $maxLines,
        $maxFileSize,
        $expectedFile,
        $expectedWrites,
        $robotsInfo
    ) {
        // Check that all $expectedWrites lines were written
        $actualData = [];
        $currentFile = '';
        $streamWriteCallback = function ($str) use (&$actualData, &$currentFile) {
            if (!array_key_exists($currentFile, $actualData)) {
                $actualData[$currentFile] = '';
            }
            $actualData[$currentFile] .= $str;
        };

        // Check that all expected lines were written
        $this->fileMock->expects($this->exactly($expectedWrites))
            ->method('write')
            ->willReturnCallback($streamWriteCallback);

        $checkFileCallback = function ($file) use (&$currentFile) {
            $currentFile = $file;
        };

        // Check that all expected file descriptors were created
        $this->directoryMock->expects($this->exactly(count($expectedFile)))
            ->method('openFile')
            ->willReturnCallback($checkFileCallback);

        // Check that all file descriptors were closed
        $this->fileMock->expects($this->exactly(count($expectedFile)))
            ->method('close');

        if (count($expectedFile) == 1) {
            $this->directoryMock->expects($this->once())
                ->method('renameFile')
                ->willReturnCallback(function ($from, $to) {
                    \PHPUnit_Framework_Assert::assertEquals('/sitemap-1-1.xml', $from);
                    \PHPUnit_Framework_Assert::assertEquals('/sitemap.xml', $to);
                });
        }

        // Check robots txt
        $robotsStart = '';
        if (isset($robotsInfo['robotsStart'])) {
            $robotsStart = $robotsInfo['robotsStart'];
        }
        $robotsFinish = 'Sitemap: http://store.com/sitemap.xml';
        if (isset($robotsInfo['robotsFinish'])) {
            $robotsFinish = $robotsInfo['robotsFinish'];
        }
        $this->directoryMock->expects($this->any())
            ->method('readFile')
            ->willReturn($robotsStart);

        $this->directoryMock->expects($this->any())
            ->method('write')
            ->with(
                $this->equalTo('robots.txt'),
                $this->equalTo($robotsFinish)
            );

        // Mock helper methods
        $pushToRobots = 0;
        if (isset($robotsInfo['pushToRobots'])) {
            $pushToRobots = (int)$robotsInfo['pushToRobots'];
        }
        $this->configReaderMock->expects($this->any())
            ->method('getMaximumLinesNumber')
            ->willReturn($maxLines);

        $this->configReaderMock->expects($this->any())
            ->method('getMaximumFileSize')
            ->willReturn($maxFileSize);

        $this->configReaderMock->expects($this->any())
            ->method('getEnableSubmissionRobots')
            ->willReturn($pushToRobots);

        $model = $this->getModelMock(true);

        $storeMock = $this->getMockBuilder(Store::class)
            ->setMethods(['isFrontUrlSecure', 'getBaseUrl'])
            ->disableOriginalConstructor()
            ->getMock();

        $storeMock->expects($this->atLeastOnce())
            ->method('isFrontUrlSecure')
            ->willReturn(false);

        $storeMock->expects($this->atLeastOnce())
            ->method('getBaseUrl')
            ->with($this->isType('string'), false)
            ->willReturn('http://store.com/');

        $this->storeManagerMock->expects($this->atLeastOnce())
            ->method('getStore')
            ->with(1)
            ->willReturn($storeMock);

        return $model;
    }

    /**
     * Get model mock object
     *
     * @param bool $mockBeforeSave
     * @return Sitemap|PHPUnit_Framework_MockObject_MockObject
     */
    protected function getModelMock($mockBeforeSave = false)
    {
        $methods = [
            '_construct',
            '_getResource',
            '_getBaseDir',
            '_getFileObject',
            '_afterSave',
            '_getCurrentDateTime',
            '_getCategoryItemsCollection',
            '_getProductItemsCollection',
            '_getPageItemsCollection',
            '_getDocumentRoot',
        ];
        if ($mockBeforeSave) {
            $methods[] = 'beforeSave';
        }

        $storeBaseMediaUrl = 'http://store.com/pub/media/catalog/product/cache/c9e0b0ef589f3508e5ba515cde53c5ff/';

        $this->itemResolverMock->expects($this->any())
            ->method('getItems')
            ->willReturn([
                new SitemapItem('category.html', '1.0', 'daily', '2012-12-21 00:00:00'),
                new SitemapItem('/category/sub-category.html', '1.0', 'daily', '2012-12-21 00:00:00'),
                new SitemapItem('product.html', '0.5', 'monthly', '2012-12-21 00:00:00'),
                new SitemapItem(
                    'product2.html',
                    '0.5',
                    'monthly',
                    '2012-12-21 00:00:00',
                    new DataObject([
                        'collection' => [
                            new DataObject(
                                [
                                    'url' => $storeBaseMediaUrl . 'i/m/image1.png',
                                    'caption' => 'caption & > title < "'
                                ]
                            ),
                            new DataObject(
                                ['url' => $storeBaseMediaUrl . 'i/m/image_no_caption.png', 'caption' => null]
                            ),
                        ],
                        'thumbnail' => $storeBaseMediaUrl . 't/h/thumbnail.jpg',
                        'title' => 'Product & > title < "',
                    ])
                )
            ]);

        /** @var $model Sitemap */
        $model = $this->getMockBuilder(Sitemap::class)
            ->setMethods($methods)
            ->setConstructorArgs($this->getModelConstructorArgs())
            ->getMock();

        $model->expects($this->any())
            ->method('_getResource')
            ->willReturn($this->resourceMock);

        $model->expects($this->any())
            ->method('_getCurrentDateTime')
            ->willReturn('2012-12-21T00:00:00-08:00');

        $model->expects($this->any())
            ->method('_getDocumentRoot')
            ->willReturn('/project');

        $model->setSitemapFilename('sitemap.xml');
        $model->setStoreId(1);
        $model->setSitemapPath('/');

        return $model;
    }

    /**
     * @return array
     */
    private function getModelConstructorArgs()
    {
        $categoryFactory = $this->getMockBuilder(CategoryFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $productFactory = $this->getMockBuilder(ProductFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $cmsFactory = $this->getMockBuilder(PageFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->setMethods(['getStore'])
            ->getMockForAbstractClass();

        $objectManager = new ObjectManager($this);
        $constructArguments = $objectManager->getConstructArguments(
            Sitemap::class,
            [
                'categoryFactory' => $categoryFactory,
                'productFactory' => $productFactory,
                'cmsFactory' => $cmsFactory,
                'storeManager' => $this->storeManagerMock,
                'sitemapData' => $this->helperMockSitemap,
                'filesystem' => $this->filesystemMock,
                'itemResolver' => $this->itemResolverMock,
                'configReader' => $this->configReaderMock,
            ]
        );
        $constructArguments['resource'] = null;
        return $constructArguments;
    }

    /**
     * Check site URL getter
     *
     * @param string $storeBaseUrl
     * @param string $documentRoot
     * @param string $baseDir
     * @param string $sitemapPath
     * @param string $sitemapFileName
     * @param string $result
     * @dataProvider siteUrlDataProvider
     */
    public function testGetSitemapUrl($storeBaseUrl, $documentRoot, $baseDir, $sitemapPath, $sitemapFileName, $result)
    {
        /** @var $model Sitemap */
        $model = $this->getMockBuilder(Sitemap::class)
            ->setMethods(
                [
                    '_getStoreBaseUrl',
                    '_getDocumentRoot',
                    '_getBaseDir',
                    '_construct',
                ]
            )
            ->setConstructorArgs($this->getModelConstructorArgs())
            ->getMock();

        $model->expects($this->any())
            ->method('_getStoreBaseUrl')
            ->willReturn($storeBaseUrl);

        $model->expects($this->any())
            ->method('_getDocumentRoot')
            ->willReturn($documentRoot);

        $model->expects($this->any())
            ->method('_getBaseDir')
            ->willReturn($baseDir);

        $this->assertEquals($result, $model->getSitemapUrl($sitemapPath, $sitemapFileName));
    }

    /**
     * Data provider for Check site URL getter
     *
     * @static
     * @return array
     */
    public static function siteUrlDataProvider()
    {
        return [
            [
                'http://store.com',
                'c:\\http\\mage2\\',
                'c:\\http\\mage2\\',
                '/',
                'sitemap.xml',
                'http://store.com/sitemap.xml',
            ],
            [
                'http://store.com/store2',
                'c:\\http\\mage2\\',
                'c:\\http\\mage2\\',
                '/sitemaps/store2',
                'sitemap.xml',
                'http://store.com/sitemaps/store2/sitemap.xml'
            ],
            [
                'http://store.com/builds/regression/ee/',
                '/var/www/html',
                '/opt/builds/regression/ee',
                '/',
                'sitemap.xml',
                'http://store.com/builds/regression/ee/sitemap.xml'
            ],
            [
                'http://store.com/store2',
                'c:\\http\\mage2\\',
                'c:\\http\\mage2\\store2',
                '/sitemaps/store2',
                'sitemap.xml',
                'http://store.com/store2/sitemaps/store2/sitemap.xml'
            ],
            [
                'http://store2.store.com',
                'c:\\http\\mage2\\',
                'c:\\http\\mage2\\',
                '/sitemaps/store2',
                'sitemap.xml',
                'http://store2.store.com/sitemaps/store2/sitemap.xml'
            ],
            [
                'http://store.com',
                '/var/www/store/',
                '/var/www/store/',
                '/',
                'sitemap.xml',
                'http://store.com/sitemap.xml'
            ],
            [
                'http://store.com/store2',
                '/var/www/store/',
                '/var/www/store/store2/',
                '/sitemaps/store2',
                'sitemap.xml',
                'http://store.com/store2/sitemaps/store2/sitemap.xml'
            ]
        ];
    }
}
