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
 * @package     Mage_Sitemap
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Sitemap_Model_SitemapTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Sitemap_Model_Resource_Sitemap
     */
    protected $_resourceMock;

    /**
     * Set helper mocks, create resource model mock
     */
    protected function setUp()
    {
        $helperMockCore = $this->getMock('Mage_Core_Helper_Data', array('__'));
        $helperMockCore->expects($this->any())
            ->method('__')
            ->will($this->returnArgument(0));
        Mage::register('_helper/Mage_Core_Helper_Data', $helperMockCore, true);

        $helperMockSitemap = $this->getMock('Mage_Sitemap_Helper_Data', array(
            '__',
            'getCategoryChangefreq',
            'getProductChangefreq',
            'getPageChangefreq',
            'getCategoryPriority',
            'getProductPriority',
            'getPagePriority',
            'getMaximumLinesNumber',
            'getMaximumFileSize',
            'getEnableSubmissionRobots'
        ));
        $helperMockSitemap->expects($this->any())
            ->method('__')
            ->will($this->returnArgument(0));
        $helperMockSitemap->expects($this->any())
            ->method('getCategoryChangefreq')
            ->will($this->returnValue('daily'));
        $helperMockSitemap->expects($this->any())
            ->method('getProductChangefreq')
            ->will($this->returnValue('monthly'));
        $helperMockSitemap->expects($this->any())
            ->method('getPageChangefreq')
            ->will($this->returnValue('daily'));
        $helperMockSitemap->expects($this->any())
            ->method('getCategoryPriority')
            ->will($this->returnValue('1'));
        $helperMockSitemap->expects($this->any())
            ->method('getProductPriority')
            ->will($this->returnValue('0.5'));
        $helperMockSitemap->expects($this->any())
            ->method('getPagePriority')
            ->will($this->returnValue('0.25'));
        Mage::register('_helper/Mage_Sitemap_Helper_Data', $helperMockSitemap, true);

        $this->_resourceMock = $this->getMockBuilder('Mage_Sitemap_Model_Resource_Sitemap')
            ->setMethods(array('_construct', 'beginTransaction', 'rollBack', 'save', 'addCommitCallback', 'commit'))
            ->disableOriginalConstructor()
            ->getMock();
        $this->_resourceMock->expects($this->any())
            ->method('addCommitCallback')
            ->will($this->returnSelf());
    }

    /**
     * Unset helpers from registry
     */
    protected function tearDown()
    {
        Mage::unregister('_helper/Mage_Core_Helper_Data');
        Mage::unregister('_helper/Mage_Sitemap_Helper_Data');
        unset($this->_resourceMock);
    }

    /**
     * Check not allowed sitemap path validation
     *
     * @expectedException Mage_Core_Exception
     * @expectedExceptionMessage Please define correct path
     */
    public function testNotAllowedPath()
    {
        $fileMock = $this->getMockBuilder('Varien_Io_File')
            ->setMethods(array('allowedPath', 'getCleanPath'))
            ->getMock();
        $fileMock->expects($this->once())
            ->method('allowedPath')
            ->will($this->returnValue(false));

        $fileMock->expects($this->any())
            ->method('getCleanPath')
            ->will($this->returnArgument(0));

        $model = $this->_getModelMock($fileMock);
        $model->save();
    }

    /**
     * Check not exists sitemap path validation
     *
     * @expectedException Mage_Core_Exception
     * @expectedExceptionMessage Please create the specified folder "%s" before saving the sitemap.
     */
    public function testPathNotExists()
    {
        $fileMock = $this->getMockBuilder('Varien_Io_File')
            ->setMethods(array('allowedPath', 'getCleanPath', 'fileExists'))
            ->getMock();
        $fileMock->expects($this->once())
            ->method('allowedPath')
            ->will($this->returnValue(true));
        $fileMock->expects($this->any())
            ->method('getCleanPath')
            ->will($this->returnArgument(0));
        $fileMock->expects($this->once())
            ->method('fileExists')
            ->will($this->returnValue(false));

        $model = $this->_getModelMock($fileMock);
        $model->save();
    }

    /**
     * Check not writable sitemap path validation
     *
     * @expectedException Mage_Core_Exception
     * @expectedExceptionMessage Please make sure that "%s" is writable by web-server.
     */
    public function testPathNotWritable()
    {
        $fileMock = $this->getMockBuilder('Varien_Io_File')
            ->setMethods(array('allowedPath', 'getCleanPath', 'fileExists', 'isWriteable'))
            ->getMock();
        $fileMock->expects($this->once())
            ->method('allowedPath')
            ->will($this->returnValue(true));
        $fileMock->expects($this->any())
            ->method('getCleanPath')
            ->will($this->returnArgument(0));
        $fileMock->expects($this->once())
            ->method('fileExists')
            ->will($this->returnValue(true));
        $fileMock->expects($this->once())
            ->method('isWriteable')
            ->will($this->returnValue(false));

        /** @var $model Mage_Sitemap_Model_Sitemap */
        $model = $this->_getModelMock($fileMock);
        $model->save();
    }

    //@codingStandardsIgnoreStart
    /**
     * Check invalid chars in sitemap filename validation
     *
     * @expectedException Mage_Core_Exception
     * @expectedExceptionMessage Please use only letters (a-z or A-Z), numbers (0-9) or underscore (_) in the filename. No spaces or other characters are allowed.
     */
    //@codingStandardsIgnoreEnd
    public function testFilenameInvalidChars()
    {
        $fileMock = $this->getMockBuilder('Varien_Io_File')
            ->setMethods(array('allowedPath', 'getCleanPath', 'fileExists', 'isWriteable'))
            ->getMock();
        $fileMock->expects($this->once())
            ->method('allowedPath')
            ->will($this->returnValue(true));
        $fileMock->expects($this->any())
            ->method('getCleanPath')
            ->will($this->returnArgument(0));
        $fileMock->expects($this->once())
            ->method('fileExists')
            ->will($this->returnValue(true));
        $fileMock->expects($this->once())
            ->method('isWriteable')
            ->will($this->returnValue(true));

        $model = $this->_getModelMock($fileMock);
        $model->setSitemapFilename('*sitemap?.xml');
        $model->save();
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
        $expectedSingleFile = array(
            'sitemap-1-1.xml' => __DIR__ . '/_files/sitemap-single.xml'
        );

        $expectedMultiFile = array(
            'sitemap-1-1.xml' => __DIR__ . '/_files/sitemap-1-1.xml',
            'sitemap-1-2.xml' => __DIR__ . '/_files/sitemap-1-2.xml',
            'sitemap-1-3.xml' => __DIR__ . '/_files/sitemap-1-3.xml',
            'sitemap-1-4.xml' => __DIR__ . '/_files/sitemap-1-4.xml',
            'sitemap.xml'     => __DIR__ . '/_files/sitemap-index.xml'
        );

        return array(
            array(50000, 10485760, $expectedSingleFile, 6),
            array(1, 10485760, $expectedMultiFile, 18),
            array(50000, 264, $expectedMultiFile, 18)
        );
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
        $actualData = array();
        $model = $this->_prepareSitemapModelMock($actualData, $maxLines, $maxFileSize,
            $expectedFile, $expectedWrites, null);
        $model->generateXml();

        $this->assertCount(count($expectedFile), $actualData, 'Number of generated files is incorrect');
        foreach ($expectedFile as $expectedFileName => $expectedFilePath) {
            $this->assertArrayHasKey($expectedFileName, $actualData,
                sprintf('File %s was not generated', $expectedFileName));
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
        $expectedSingleFile = array(
            'sitemap-1-1.xml' => __DIR__ . '/_files/sitemap-single.xml'
        );

        $expectedMultiFile = array(
            'sitemap-1-1.xml' => __DIR__ . '/_files/sitemap-1-1.xml',
            'sitemap-1-2.xml' => __DIR__ . '/_files/sitemap-1-2.xml',
            'sitemap-1-3.xml' => __DIR__ . '/_files/sitemap-1-3.xml',
            'sitemap-1-4.xml' => __DIR__ . '/_files/sitemap-1-4.xml',
            'sitemap.xml'     => __DIR__ . '/_files/sitemap-index.xml'
        );

        return array(
            array(50000, 10485760, $expectedSingleFile, 6, array(
                'robotsStart'  => '',
                'robotsFinish' => 'Sitemap: http://store.com/sitemap.xml',
                'pushToRobots' => 1,
            )), // empty robots file
            array(50000, 10485760, $expectedSingleFile, 6, array(
                'robotsStart'  => "User-agent: *",
                'robotsFinish' => "User-agent: *"
                    . PHP_EOL . 'Sitemap: http://store.com/sitemap.xml',
                'pushToRobots' => 1,
            )), // not empty robots file EOL
            array(1, 10485760, $expectedMultiFile, 18, array(
                'robotsStart'  => "User-agent: *\r\n",
                'robotsFinish' => "User-agent: *\r\n\r\nSitemap: http://store.com/sitemap.xml",
                'pushToRobots' => 1,
            )), // not empty robots file WIN
            array(50000, 264, $expectedMultiFile, 18, array(
                'robotsStart'  => "User-agent: *\n",
                'robotsFinish' => "User-agent: *\n\nSitemap: http://store.com/sitemap.xml",
                'pushToRobots' => 1,
            )), // not empty robots file UNIX
            array(50000, 10485760, $expectedSingleFile, 6, array(
                'robotsStart'  => '',
                'robotsFinish' => '',
                'pushToRobots' => 0,
            )), // empty robots file
        );
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
        $actualData = array();
        $model = $this->_prepareSitemapModelMock($actualData, $maxLines, $maxFileSize, $expectedFile, $expectedWrites,
            $robotsInfo);
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
     * @return Mage_Sitemap_Model_Sitemap|PHPUnit_Framework_MockObject_MockObject
     */
    protected function _prepareSitemapModelMock(&$actualData, $maxLines, $maxFileSize,
        $expectedFile, $expectedWrites, $robotsInfo
    ) {
        $dateMock = $this->getMockBuilder('Mage_Core_Model_Date')
            ->disableOriginalConstructor()
            ->getMock();
        Mage::register('_singleton/Mage_Core_Model_Date', $dateMock, true);

        $fileMock = $this->getMockBuilder('Varien_Io_File')
            ->setMethods(array('streamWrite', 'open', 'streamOpen', 'streamClose',
                'allowedPath', 'getCleanPath', 'fileExists', 'isWriteable', 'mv', 'read', 'write'))
            ->getMock();
        $this->_prepareValidFileMock($fileMock);

        // Check that all $expectedWrites lines were written
        $actualData = array();
        $currentFile = '';
        $streamWriteCallback = function ($str) use (&$actualData, &$currentFile) {
            if (!array_key_exists($currentFile, $actualData)) {
                $actualData[$currentFile] = '';
            }
            $actualData[$currentFile] .= $str;
        };

        // Check that all expected lines were written
        $fileMock->expects($this->exactly($expectedWrites))
            ->method('streamWrite')
            ->will($this->returnCallback($streamWriteCallback));

        // Check that all expected file descriptors were created
        $fileMock->expects($this->exactly(count($expectedFile)))
            ->method('streamOpen')
            ->will($this->returnCallback(
                function ($file) use (&$currentFile) {
                    $currentFile = $file;
                }
            ));

        // Check that all file descriptors were closed
        $fileMock->expects($this->exactly(count($expectedFile)))
            ->method('streamClose');

        if (count($expectedFile) == 1) {
            $fileMock->expects($this->once())
                ->method('mv')
                ->will($this->returnCallback(
                    function ($from, $to) {
                        PHPUnit_Framework_Assert::assertEquals('sitemap-1-1.xml', $from);
                        PHPUnit_Framework_Assert::assertEquals('sitemap.xml', $to);
                    }
                ));
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
        $fileMock->expects($this->any())
            ->method('read')
            ->will($this->returnValue($robotsStart));
        $fileMock->expects($this->any())
            ->method('write')
            ->with($this->equalTo('/project/robots.txt'), $this->equalTo($robotsFinish));


        // Mock helper methods
        $pushToRobots = 0;
        if (isset($robotsInfo['pushToRobots'])) {
            $pushToRobots = (int) $robotsInfo['pushToRobots'];
        }
        $helperMock = Mage::registry('_helper/Mage_Sitemap_Helper_Data');
        $helperMock->expects($this->any())
            ->method('getMaximumLinesNumber')
            ->will($this->returnValue($maxLines));
        $helperMock->expects($this->any())
            ->method('getMaximumFileSize')
            ->will($this->returnValue($maxFileSize));
        $helperMock->expects($this->any())
            ->method('getEnableSubmissionRobots')
            ->will($this->returnValue($pushToRobots));

        $model = $this->_getModelMock($fileMock, true);

        return $model;
    }

    /**
     * Prepare file io mock with all validation passed
     *
     * @param $fileMock
     */
    protected function _prepareValidFileMock($fileMock)
    {
        $fileMock->expects($this->any())
            ->method('allowedPath')
            ->will($this->returnValue(true));
        $fileMock->expects($this->any())
            ->method('getCleanPath')
            ->will($this->returnArgument(0));
        $fileMock->expects($this->any())
            ->method('fileExists')
            ->will($this->returnValue(true));
        $fileMock->expects($this->any())
            ->method('isWriteable')
            ->will($this->returnValue(true));
    }

    /**
     * Get model mock object
     *
     * @param Varien_Io_File|PHPUnit_Framework_MockObject_MockObject $fileIoMock
     * @param bool $mockBeforeSave
     * @return Mage_Sitemap_Model_Sitemap|PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getModelMock($fileIoMock, $mockBeforeSave = false)
    {
        $methods = array('_construct', '_getResource', '_getBaseDir', '_getFileObject', '_afterSave',
            '_getStoreBaseUrl', '_getCurrentDateTime', '_getCategoryItemsCollection', '_getProductItemsCollection',
            '_getPageItemsCollection', '_getDocumentRoot');
        if ($mockBeforeSave) {
            $methods[] = '_beforeSave';
        }
        /** @var $model Mage_Sitemap_Model_Sitemap */
        $model = $this->getMockBuilder('Mage_Sitemap_Model_Sitemap')
            ->setMethods($methods)
            ->disableOriginalConstructor()
            ->getMock();
        $model->expects($this->any())
            ->method('_getResource')
            ->will($this->returnValue($this->_resourceMock));
        $model->expects($this->any())
            ->method('_getBaseDir')
            ->will($this->returnValue('/project'));
        $model->expects($this->any())
            ->method('_getStoreBaseUrl')
            ->will($this->returnValue('http://store.com/'));
        $model->expects($this->any())
            ->method('_getFileObject')
            ->will($this->returnValue($fileIoMock));
        $model->expects($this->any())
            ->method('_getCategoryItemsCollection')
            ->will($this->returnValue(array(
                new Varien_Object(array(
                    'url' => 'category.html',
                    'updated_at' => '2012-12-21 00:00:00'
                )),
                new Varien_Object(array(
                    'url' => '/category/sub-category.html',
                    'updated_at' => '2012-12-21 00:00:00'
                ))
            )));
        $model->expects($this->any())
            ->method('_getProductItemsCollection')
            ->will($this->returnValue(array(
                new Varien_Object(array(
                    'url' => 'product.html',
                    'updated_at' => '2012-12-21 00:00:00'
                )),
                new Varien_Object(array(
                    'url' => 'product2.html',
                    'updated_at' => '2012-12-21 00:00:00',
                    'images' => new Varien_Object(array(
                        'collection' => array(
                            new Varien_Object(array(
                                'url' => 'image1.png',
                                'caption' => 'caption & > title < "'
                            )),
                            new Varien_Object(array(
                                'url' => 'image_no_caption.png',
                                'caption' => null
                            ))
                        ),
                        'thumbnail' => 'thumbnail.jpg',
                        'title' => 'Product & > title < "'
                    ))
                ))
            )));
        $model->expects($this->any())
            ->method('_getPageItemsCollection')
            ->will($this->returnValue(array()));
        $model->expects($this->any())
            ->method('_getCurrentDateTime')
            ->will($this->returnValue('2012-12-21T00:00:00-08:00'));

        $model->expects($this->any())
            ->method('_getDocumentRoot')
            ->will($this->returnValue('/project'));

        $model->setSitemapFilename('sitemap.xml');
        $model->setStoreId(1);
        $model->setSitemapPath('/');

        return $model;
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
        /** @var $model Mage_Sitemap_Model_Sitemap */
        $model = $this->getMockBuilder('Mage_Sitemap_Model_Sitemap')
            ->setMethods(array('_getStoreBaseUrl', '_getDocumentRoot', '_getBaseDir'))
            ->disableOriginalConstructor()
            ->getMock();

        $model->expects($this->any())
            ->method('_getStoreBaseUrl')
            ->will($this->returnValue($storeBaseUrl));

        $model->expects($this->any())
            ->method('_getDocumentRoot')
            ->will($this->returnValue($documentRoot));

        $model->expects($this->any())
            ->method('_getBaseDir')
            ->will($this->returnValue($baseDir));

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
        return array(
            array(
                'http://store.com',
                'c:\\http\\mage2\\', 'c:\\http\\mage2\\',
                '/', 'sitemap.xml',
                'http://store.com/sitemap.xml'
            ),
            array(
                'http://store.com/store2',
                'c:\\http\\mage2\\', 'c:\\http\\mage2\\',
                '/sitemaps/store2', 'sitemap.xml',
                'http://store.com/sitemaps/store2/sitemap.xml'
            ),
            array(
                'http://store.com/store2',
                'c:\\http\\mage2\\', 'c:\\http\\mage2\\store2',
                '/sitemaps/store2', 'sitemap.xml',
                'http://store.com/store2/sitemaps/store2/sitemap.xml'
            ),
            array(
                'http://store2.store.com',
                'c:\\http\\mage2\\', 'c:\\http\\mage2\\',
                '/sitemaps/store2', 'sitemap.xml',
                'http://store2.store.com/sitemaps/store2/sitemap.xml'
            ),
            array(
                'http://store.com',
                '/var/www/store/', '/var/www/store/',
                '/', 'sitemap.xml',
                'http://store.com/sitemap.xml'
            ),
            array(
                'http://store.com/store2',
                '/var/www/store/', '/var/www/store/store2/',
                '/sitemaps/store2', 'sitemap.xml',
                'http://store.com/store2/sitemaps/store2/sitemap.xml'
            ),
        );
    }
}
