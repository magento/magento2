<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sitemap\Test\Unit\Model;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SitemapTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Sitemap\Helper\Data
     */
    protected $_helperMockSitemap;

    /**
     * @var \Magento\Sitemap\Model\ResourceModel\Sitemap
     */
    protected $_resourceMock;

    /**
     * @var \Magento\Sitemap\Model\ResourceModel\Catalog\Category
     */
    protected $_sitemapCategoryMock;

    /**
     * @var \Magento\Sitemap\Model\ResourceModel\Catalog\Product
     */
    protected $_sitemapProductMock;

    /**
     * @var \Magento\Sitemap\Model\ResourceModel\Cms\Page
     */
    protected $_sitemapCmsPageMock;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $_filesystemMock;

    /**
     * @var \Magento\Framework\Filesystem\Directory\Write
     */
    protected $_directoryMock;

    /**
     * @var \Magento\Framework\Filesystem\File\Write
     */
    protected $_fileMock;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    /**
     * Set helper mocks, create resource model mock
     */
    protected function setUp()
    {
        $this->_sitemapCategoryMock = $this->getMockBuilder(
            \Magento\Sitemap\Model\ResourceModel\Catalog\Category::class
        )->disableOriginalConstructor()->getMock();
        $this->_sitemapProductMock = $this->getMockBuilder(
            \Magento\Sitemap\Model\ResourceModel\Catalog\Product::class
        )->disableOriginalConstructor()->getMock();
        $this->_sitemapCmsPageMock = $this->getMockBuilder(
            \Magento\Sitemap\Model\ResourceModel\Cms\Page::class
        )->disableOriginalConstructor()->getMock();
        $this->_helperMockSitemap = $this->createPartialMock(\Magento\Sitemap\Helper\Data::class, [
                'getCategoryChangefreq',
                'getProductChangefreq',
                'getPageChangefreq',
                'getCategoryPriority',
                'getProductPriority',
                'getPagePriority',
                'getMaximumLinesNumber',
                'getMaximumFileSize',
                'getEnableSubmissionRobots'
            ]);
        $this->_helperMockSitemap->expects(
            $this->any()
        )->method(
            'getCategoryChangefreq'
        )->will(
            $this->returnValue('daily')
        );
        $this->_helperMockSitemap->expects(
            $this->any()
        )->method(
            'getProductChangefreq'
        )->will(
            $this->returnValue('monthly')
        );
        $this->_helperMockSitemap->expects(
            $this->any()
        )->method(
            'getPageChangefreq'
        )->will(
            $this->returnValue('daily')
        );
        $this->_helperMockSitemap->expects($this->any())->method('getCategoryPriority')->will($this->returnValue('1'));
        $this->_helperMockSitemap->expects(
            $this->any()
        )->method(
            'getProductPriority'
        )->will(
            $this->returnValue('0.5')
        );
        $this->_helperMockSitemap->expects($this->any())->method('getPagePriority')->will($this->returnValue('0.25'));

        $this->_resourceMock = $this->getMockBuilder(
            \Magento\Sitemap\Model\ResourceModel\Sitemap::class
        )->setMethods(
            ['_construct', 'beginTransaction', 'rollBack', 'save', 'addCommitCallback', 'commit', '__wakeup']
        )->disableOriginalConstructor()->getMock();
        $this->_resourceMock->expects($this->any())->method('addCommitCallback')->will($this->returnSelf());

        $this->_fileMock = $this->getMockBuilder(
            \Magento\Framework\Filesystem\File\Write::class
        )->disableOriginalConstructor()->setMethods(['write', 'close'])->getMock();

        $this->_directoryMock = $this->getMockBuilder(
            \Magento\Framework\Filesystem\Directory\Write::class
        )->disableOriginalConstructor()->setMethods(
            ['write', 'openFile', 'isExist', 'renameFile', 'readFile', 'isWritable']
        )->getMock();
        $this->_directoryMock->expects($this->any())->method('openFile')->will($this->returnValue($this->_fileMock));

        $this->_filesystemMock = $this->getMockBuilder(
            \Magento\Framework\Filesystem::class
        )->setMethods(
            ['getDirectoryWrite']
        )->disableOriginalConstructor()->getMock();
        $this->_filesystemMock->expects(
            $this->any()
        )->method(
            'getDirectoryWrite'
        )->will(
            $this->returnValue($this->_directoryMock)
        );
    }

    /**
     * Check not allowed sitemap path validation
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Please define a correct path.
     */
    public function testNotAllowedPath()
    {
        $model = $this->_getModelMock();
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
        $this->_directoryMock->expects($this->once())->method('isExist')->will($this->returnValue(false));

        $model = $this->_getModelMock();
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
        $this->_directoryMock->expects($this->once())->method('isExist')->will($this->returnValue(true));
        $this->_directoryMock->expects($this->once())->method('isWritable')->will($this->returnValue(false));

        $model = $this->_getModelMock();
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
        $this->_directoryMock->expects($this->once())->method('isExist')->will($this->returnValue(true));
        $this->_directoryMock->expects($this->once())->method('isWritable')->will($this->returnValue(true));

        $model = $this->_getModelMock();
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
            [50000, 264, $expectedMultiFile, 18]
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
        $model = $this->_prepareSitemapModelMock(
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
        $model = $this->_prepareSitemapModelMock(
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
     * @return \Magento\Sitemap\Model\Sitemap|\PHPUnit_Framework_MockObject_MockObject
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareSitemapModelMock(
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
        $this->_fileMock->expects(
            $this->exactly($expectedWrites)
        )->method(
            'write'
        )->will(
            $this->returnCallback($streamWriteCallback)
        );

        // Check that all expected file descriptors were created
        $this->_directoryMock->expects($this->exactly(count($expectedFile)))->method('openFile')->will(
            $this->returnCallback(
                function ($file) use (&$currentFile) {
                    $currentFile = $file;
                }
            )
        );

        // Check that all file descriptors were closed
        $this->_fileMock->expects($this->exactly(count($expectedFile)))->method('close');

        if (count($expectedFile) == 1) {
            $this->_directoryMock->expects($this->once())->method('renameFile')->will(
                $this->returnCallback(
                    function ($from, $to) {
                        \PHPUnit\Framework\Assert::assertEquals('/sitemap-1-1.xml', $from);
                        \PHPUnit\Framework\Assert::assertEquals('/sitemap.xml', $to);
                    }
                )
            );
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
        $this->_directoryMock->expects($this->any())->method('readFile')->will($this->returnValue($robotsStart));
        $this->_directoryMock->expects(
            $this->any()
        )->method(
            'write'
        )->with(
            $this->equalTo('robots.txt'),
            $this->equalTo($robotsFinish)
        );

        // Mock helper methods
        $pushToRobots = 0;
        if (isset($robotsInfo['pushToRobots'])) {
            $pushToRobots = (int)$robotsInfo['pushToRobots'];
        }
        $this->_helperMockSitemap->expects(
            $this->any()
        )->method(
            'getMaximumLinesNumber'
        )->will(
            $this->returnValue($maxLines)
        );
        $this->_helperMockSitemap->expects(
            $this->any()
        )->method(
            'getMaximumFileSize'
        )->will(
            $this->returnValue($maxFileSize)
        );
        $this->_helperMockSitemap->expects(
            $this->any()
        )->method(
            'getEnableSubmissionRobots'
        )->will(
            $this->returnValue($pushToRobots)
        );

        $model = $this->_getModelMock(true);

        $storeMock = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->setMethods(['isFrontUrlSecure', 'getBaseUrl'])
            ->disableOriginalConstructor()
            ->getMock();
        $storeMock->expects($this->atLeastOnce())->method('isFrontUrlSecure')->willReturn(false);
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
     * @return \Magento\Sitemap\Model\Sitemap|PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getModelMock($mockBeforeSave = false)
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

        $this->_sitemapCategoryMock->expects(
            $this->any()
        )->method(
            'getCollection'
        )->will(
            $this->returnValue(
                [
                    new \Magento\Framework\DataObject(
                        ['url' => 'category.html', 'updated_at' => '2012-12-21 00:00:00']
                    ),
                    new \Magento\Framework\DataObject(
                        ['url' => '/category/sub-category.html', 'updated_at' => '2012-12-21 00:00:00']
                    ),
                ]
            )
        );

        $storeBaseMediaUrl = 'http://store.com/pub/media/catalog/product/cache/c9e0b0ef589f3508e5ba515cde53c5ff/';
        $this->_sitemapProductMock->expects(
            $this->any()
        )->method(
            'getCollection'
        )->will(
            $this->returnValue(
                [
                    new \Magento\Framework\DataObject(
                        ['url' => 'product.html', 'updated_at' => '2012-12-21 00:00:00']
                    ),
                    new \Magento\Framework\DataObject(
                        [
                            'url' => 'product2.html',
                            'updated_at' => '2012-12-21 00:00:00',
                            'images' => new \Magento\Framework\DataObject(
                                [
                                    'collection' => [
                                        new \Magento\Framework\DataObject(
                                            [
                                                'url' => $storeBaseMediaUrl.'i/m/image1.png',
                                                'caption' => 'caption & > title < "'
                                            ]
                                        ),
                                        new \Magento\Framework\DataObject(
                                            ['url' => $storeBaseMediaUrl.'i/m/image_no_caption.png', 'caption' => null]
                                        ),
                                    ],
                                    'thumbnail' => $storeBaseMediaUrl.'t/h/thumbnail.jpg',
                                    'title' => 'Product & > title < "',
                                ]
                            ),
                        ]
                    ),
                ]
            )
        );
        $this->_sitemapCmsPageMock->expects($this->any())->method('getCollection')->will($this->returnValue([]));

        /** @var $model \Magento\Sitemap\Model\Sitemap */
        $model = $this->getMockBuilder(
            \Magento\Sitemap\Model\Sitemap::class
        )->setMethods(
            $methods
        )->setConstructorArgs(
            $this->_getModelConstructorArgs()
        )->getMock();

        $model->expects($this->any())->method('_getResource')->will($this->returnValue($this->_resourceMock));
        $model->expects(
            $this->any()
        )->method(
            '_getCurrentDateTime'
        )->will(
            $this->returnValue('2012-12-21T00:00:00-08:00')
        );
        $model->expects($this->any())->method('_getDocumentRoot')->will($this->returnValue('/project'));

        $model->setSitemapFilename('sitemap.xml');
        $model->setStoreId(1);
        $model->setSitemapPath('/');

        return $model;
    }

    /**
     * @return array
     */
    protected function _getModelConstructorArgs()
    {
        $categoryFactory = $this->getMockBuilder(
            \Magento\Sitemap\Model\ResourceModel\Catalog\CategoryFactory::class
        )->setMethods(
            ['create']
        )->disableOriginalConstructor()->getMock();
        $categoryFactory->expects(
            $this->any()
        )->method(
            'create'
        )->will(
            $this->returnValue($this->_sitemapCategoryMock)
        );

        $productFactory = $this->getMockBuilder(
            \Magento\Sitemap\Model\ResourceModel\Catalog\ProductFactory::class
        )->setMethods(
            ['create']
        )->disableOriginalConstructor()->getMock();
        $productFactory->expects($this->any())->method('create')->will($this->returnValue($this->_sitemapProductMock));

        $cmsFactory = $this->getMockBuilder(
            \Magento\Sitemap\Model\ResourceModel\Cms\PageFactory::class
        )->setMethods(
            ['create']
        )->disableOriginalConstructor()->getMock();
        $cmsFactory->expects($this->any())->method('create')->will($this->returnValue($this->_sitemapCmsPageMock));

        $this->storeManagerMock = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->setMethods(['getStore'])
            ->getMockForAbstractClass();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $constructArguments = $objectManager->getConstructArguments(
            \Magento\Sitemap\Model\Sitemap::class,
            [
                'categoryFactory' => $categoryFactory,
                'productFactory' => $productFactory,
                'cmsFactory' => $cmsFactory,
                'storeManager' => $this->storeManagerMock,
                'sitemapData' => $this->_helperMockSitemap,
                'filesystem' => $this->_filesystemMock
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
        /** @var $model \Magento\Sitemap\Model\Sitemap */
        $model = $this->getMockBuilder(
            \Magento\Sitemap\Model\Sitemap::class
        )->setMethods(
            ['_getStoreBaseUrl', '_getDocumentRoot', '_getBaseDir', '_construct']
        )->setConstructorArgs(
            $this->_getModelConstructorArgs()
        )->getMock();

        $model->expects($this->any())->method('_getStoreBaseUrl')->will($this->returnValue($storeBaseUrl));

        $model->expects($this->any())->method('_getDocumentRoot')->will($this->returnValue($documentRoot));

        $model->expects($this->any())->method('_getBaseDir')->will($this->returnValue($baseDir));

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
