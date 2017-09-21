<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Test\Unit\Helper\Wysiwyg;

use Magento\Cms\Model\Wysiwyg\Config as WysiwygConfig;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ImagesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Cms\Helper\Wysiwyg\Images
     */
    protected $imagesHelper;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $filesystemMock;

    /**
     * @var \Magento\Framework\Filesystem\Directory\Write|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $directoryWriteMock;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \Magento\Store\Model\Store|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeMock;

    /**
     * @var \Magento\Framework\App\Helper\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManagerMock;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Framework\Url\EncoderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlEncoderMock;

    /**
     * @var \Magento\Backend\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $backendDataMock;

    /**
     * @var string
     */
    protected $path;

    protected function setUp()
    {
        $this->path = 'PATH/';
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->eventManagerMock = $this->createMock(\Magento\Framework\Event\ManagerInterface::class);

        $this->requestMock = $this->createMock(\Magento\Framework\App\RequestInterface::class);

        $this->urlEncoderMock = $this->createMock(\Magento\Framework\Url\EncoderInterface::class);

        $this->backendDataMock = $this->createMock(\Magento\Backend\Helper\Data::class);

        $this->contextMock = $this->createMock(\Magento\Framework\App\Helper\Context::class);
        $this->contextMock->expects($this->any())
            ->method('getEventManager')
            ->willReturn($this->eventManagerMock);
        $this->contextMock->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->requestMock);
        $this->contextMock->expects($this->any())
            ->method('getUrlEncoder')
            ->willReturn($this->urlEncoderMock);

        $this->directoryWriteMock = $this->getMockBuilder(\Magento\Framework\Filesystem\Directory\Write::class)
            ->setConstructorArgs(['path' => $this->path])
            ->disableOriginalConstructor()
            ->getMock();
        $this->directoryWriteMock->expects($this->any())
            ->method('getAbsolutePath')
            ->willReturnMap(
                [
                    [WysiwygConfig::IMAGE_DIRECTORY, null, $this->getAbsolutePath(WysiwygConfig::IMAGE_DIRECTORY)],
                    [null, null, $this->getAbsolutePath(null)]
                ]
            );

        $this->filesystemMock = $this->createMock(\Magento\Framework\Filesystem::class);
        $this->filesystemMock->expects($this->once())
            ->method('getDirectoryWrite')
            ->willReturn($this->directoryWriteMock);

        $this->storeManagerMock = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->setMethods(
                [
                    'clearWebsiteCache', 'getDefaultStoreView', 'getGroup', 'getGroups',
                    'getStore', 'getStores', 'getWebsite', 'getWebsites', 'hasSingleStore',
                    'isSingleStoreMode', 'reinitStores', 'setCurrentStore', 'setIsSingleStoreModeAllowed'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeMock = $this->createMock(\Magento\Store\Model\Store::class);

        $this->imagesHelper = $this->objectManager->getObject(
            \Magento\Cms\Helper\Wysiwyg\Images::class,
            [
                'context' => $this->contextMock,
                'filesystem' => $this->filesystemMock,
                'storeManager' => $this->storeManagerMock,
                'backendData' => $this->backendDataMock
            ]
        );
    }

    protected function tearDown()
    {
        $this->objectManager = null;
        $this->directoryWriteMock = null;
        $this->filesystemMock = null;
        $this->storeManagerMock = null;
        $this->storeMock = null;
        $this->imagesHelper = null;
        $this->contextMock = null;
        $this->eventManagerMock = null;
        $this->requestMock = null;
        $this->urlEncoderMock = null;
        $this->backendDataMock = null;
    }

    /**
     * @param string $path
     * @return string
     */
    protected function getAbsolutePath($path)
    {
        return $this->path . $path;
    }

    public function testSetStoreId()
    {
        $this->assertEquals($this->imagesHelper, $this->imagesHelper->setStoreId(1));
    }

    public function testGetStorageRoot()
    {
        $this->assertEquals(
            $this->getAbsolutePath(WysiwygConfig::IMAGE_DIRECTORY),
            $this->imagesHelper->getStorageRoot()
        );
    }

    public function testGetBaseUrl()
    {
        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($this->storeMock);
        $this->storeMock->expects($this->once())
            ->method('getBaseUrl')
            ->with(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $this->imagesHelper->getBaseUrl();
    }

    public function testGetTreeNodeName()
    {
        $this->assertEquals('node', $this->imagesHelper->getTreeNodeName());
    }

    public function testConvertPathToId()
    {
        $pathOne = '/test_path';
        $pathTwo = $this->getAbsolutePath(WysiwygConfig::IMAGE_DIRECTORY) . '/test_path';
        $this->assertEquals(
            $this->imagesHelper->convertPathToId($pathOne),
            $this->imagesHelper->convertPathToId($pathTwo)
        );
    }

    /**
     * @param string $path
     * @param string $pathId
     * @dataProvider providerConvertIdToPath
     */
    public function testConvertIdToPathAnyId($path, $pathId)
    {
        $pathOne = $this->imagesHelper->getStorageRoot() . $path;
        $pathTwo = $this->imagesHelper->convertIdToPath($pathId);
        $this->assertEquals($pathOne, $pathTwo);
    }

    /**
     * @return array
     */
    public function providerConvertIdToPath()
    {
        return [
            ['', ''],
            ['/test_path', 'L3Rlc3RfcGF0aA--']
        ];
    }

    public function testConvertIdToPathNodeRoot()
    {
        $pathId = \Magento\Theme\Helper\Storage::NODE_ROOT;
        $this->assertEquals($this->imagesHelper->getStorageRoot(), $this->imagesHelper->convertIdToPath($pathId));
    }

    /**
     * @param string $fileName
     * @param int $maxLength
     * @param string $expectedFilename
     * @dataProvider providerShortFilename
     */
    public function testGetShortFilename($fileName, $maxLength, $expectedFilename)
    {
        $this->assertEquals($expectedFilename, $this->imagesHelper->getShortFilename($fileName, $maxLength));
    }

    /**
     * @return array
     */
    public function providerShortFilename()
    {
        return [
            ['test', 3, 'tes...'],
            ['test', 4, 'test'],
            ['test', 20, 'test']
        ];
    }

    /**
     * @param string $fileName
     * @param string $expectedFilename
     * @dataProvider providerShortFilenameDefaultMaxLength
     */
    public function testGetShortFilenameDefaultMaxLength($fileName, $expectedFilename)
    {
        $this->assertEquals($expectedFilename, $this->imagesHelper->getShortFilename($fileName));
    }

    /**
     * @return array
     */
    public function providerShortFilenameDefaultMaxLength()
    {
        return [
            ['Mini text', 'Mini text'],
            ['20 symbols are here', '20 symbols are here'],
            ['Some text for this unit test', 'Some text for this u...']
        ];
    }

    /**
     * @param bool $allowedValue
     * @dataProvider providerIsUsingStaticUrlsAllowed
     */
    public function testIsUsingStaticUrlsAllowed($allowedValue)
    {
        $this->generalSettingsIsUsingStaticUrlsAllowed($allowedValue);
        $this->assertEquals($allowedValue, $this->imagesHelper->isUsingStaticUrlsAllowed());
    }

    /**
     * @param bool $allowedValue
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function generalSettingsIsUsingStaticUrlsAllowed($allowedValue)
    {
        $storeId = 1;
        $this->imagesHelper->setStoreId($storeId);
        $checkResult = new \StdClass();
        $checkResult->isAllowed = false;
        $this->eventManagerMock->expects($this->any())
            ->method('dispatch')
            ->with('cms_wysiwyg_images_static_urls_allowed', ['result' => $checkResult, 'store_id' => $storeId])
            ->willReturnCallback(function ($str, $arr) use ($allowedValue) {
                $arr['result']->isAllowed = $allowedValue;
            });
    }

    /**
     * @return array
     */
    public function providerIsUsingStaticUrlsAllowed()
    {
        return [
            [true],
            [false]
        ];
    }

    /**
     * @param string $pathId
     * @param string $expectedPath
     * @param bool $isExist
     * @dataProvider providerGetCurrentPath
     */
    public function testGetCurrentPath($pathId, $expectedPath, $isExist)
    {
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->willReturn($pathId);

        $this->directoryWriteMock->expects($this->any())
            ->method('isDirectory')
            ->willReturnMap(
                [
                    ['/../wysiwyg/test_path', true],
                    ['/../wysiwyg/my.jpg', false],
                    ['/../wysiwyg', true]
                ]
            );
        $this->directoryWriteMock->expects($this->any())
            ->method('getRelativePath')
            ->willReturnMap(
                [
                    ['PATH/wysiwyg/test_path', '/../wysiwyg/test_path'],
                    ['PATH/wysiwyg/my.jpg', '/../wysiwyg/my.jpg'],
                    ['PATH/wysiwyg', '/../wysiwyg'],
                ]
            );
        $this->directoryWriteMock->expects($this->once())
            ->method('isExist')
            ->willReturn($isExist);
        $this->directoryWriteMock->expects($this->any())
            ->method('create')
            ->with($this->directoryWriteMock->getRelativePath($expectedPath));

        $this->assertEquals($expectedPath, $this->imagesHelper->getCurrentPath());
    }

    public function testGetCurrentPathThrowException()
    {
        $this->expectException(
            \Magento\Framework\Exception\LocalizedException::class,
            'The directory PATH/wysiwyg is not writable by server.'
        );

        $this->directoryWriteMock->expects($this->once())
            ->method('isExist')
            ->willReturn(false);
        $this->directoryWriteMock->expects($this->any())
            ->method('create')
            ->willThrowException(
                new \Magento\Framework\Exception\FileSystemException(__('Could not create a directory.'))
            );

        $this->imagesHelper->getCurrentPath();

        $this->fail('An expected exception has not been raised.');
    }

    public function providerGetCurrentPath()
    {
        return [
            ['L3Rlc3RfcGF0aA--', 'PATH/wysiwyg/test_path', true],
            ['L215LmpwZw--', 'PATH/wysiwyg', true],
            [null, 'PATH/wysiwyg', true],
            ['L3Rlc3RfcGF0aA--', 'PATH/wysiwyg/test_path', false],
            ['L215LmpwZw--', 'PATH/wysiwyg', false],
            [null, 'PATH/wysiwyg', false]
        ];
    }

    public function testGetCurrentUrl()
    {
        $storeId = 1;
        $baseUrl = 'http://localhost';
        $relativePath = '/../wysiwyg';

        $this->imagesHelper->setStoreId($storeId);

        $this->storeMock->expects($this->once())
            ->method('getBaseUrl')
            ->with(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA)
            ->willReturn($baseUrl);
        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($this->storeMock);
        $this->directoryWriteMock->expects($this->any())
            ->method('getRelativePath')
            ->willReturn($relativePath);

        $this->assertEquals($baseUrl . $relativePath . '/', $this->imagesHelper->getCurrentUrl());
    }

    /**
     * @param string $baseUrl
     * @param string $fileName
     * @param bool $isUsingStaticUrls
     * @param string $expectedHtml
     * @dataProvider providerGetImageHtmlDeclarationRenderingAsTag
     */
    public function testGetImageHtmlDeclarationRenderingAsTag($baseUrl, $fileName, $isUsingStaticUrls, $expectedHtml)
    {
        $this->generalSettingsGetImageHtmlDeclaration($baseUrl, $isUsingStaticUrls);
        $this->assertEquals($expectedHtml, $this->imagesHelper->getImageHtmlDeclaration($fileName, true));
    }

    public function providerGetImageHtmlDeclarationRenderingAsTag()
    {
        return [
            ['http://localhost', 'test.png', true, '<img src="http://localhost/test.png" alt="" />'],
            ['http://localhost', 'test.png', false, '<img src="{{media url="/test.png"}}" alt="" />']
        ];
    }

    /**
     * @param string $baseUrl
     * @param string $fileName
     * @param bool $isUsingStaticUrls
     * @param string $expectedHtml
     * @dataProvider providerGetImageHtmlDeclaration
     */
    public function testGetImageHtmlDeclaration($baseUrl, $fileName, $isUsingStaticUrls, $expectedHtml)
    {
        $directive = '{{media url="/' . $fileName . '"}}';

        $this->generalSettingsGetImageHtmlDeclaration($baseUrl, $isUsingStaticUrls);

        $this->urlEncoderMock->expects($this->any())
            ->method('encode')
            ->with($directive)
            ->willReturn($directive);

        $this->backendDataMock->expects($this->any())
            ->method('getUrl')
            ->with('cms/wysiwyg/directive', ['___directive' => $directive])
            ->willReturn($directive);

        $this->assertEquals($expectedHtml, $this->imagesHelper->getImageHtmlDeclaration($fileName));
    }

    public function providerGetImageHtmlDeclaration()
    {
        return [
            ['http://localhost', 'test.png', true, 'http://localhost/test.png'],
            ['http://localhost', 'test.png', false, '{{media url="/test.png"}}']
        ];
    }

    /**
     * @param string $baseUrl
     * @param bool $isUsingStaticUrls
     */
    protected function generalSettingsGetImageHtmlDeclaration($baseUrl, $isUsingStaticUrls)
    {
        $storeId = 1;
        $this->imagesHelper->setStoreId($storeId);

        $this->storeMock->expects($this->any())
            ->method('getBaseUrl')
            ->with(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA)
            ->willReturn($baseUrl);
        $this->storeManagerMock->expects($this->any())
            ->method('getStore')
            ->willReturn($this->storeMock);

        $this->generalSettingsIsUsingStaticUrlsAllowed($isUsingStaticUrls);
    }
}
