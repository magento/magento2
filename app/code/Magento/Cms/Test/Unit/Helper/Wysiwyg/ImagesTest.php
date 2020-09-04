<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Cms\Test\Unit\Helper\Wysiwyg;

use Magento\Backend\Helper\Data;
use Magento\Cms\Helper\Wysiwyg\Images;
use Magento\Cms\Model\Wysiwyg\Config as WysiwygConfig;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Write;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Url\EncoderInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Theme\Helper\Storage;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ImagesTest extends TestCase
{
    /**
     * @var Images
     */
    protected $imagesHelper;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var Filesystem|MockObject
     */
    protected $filesystemMock;

    /**
     * @var Write|MockObject
     */
    protected $directoryWriteMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    protected $storeManagerMock;

    /**
     * @var Store|MockObject
     */
    protected $storeMock;

    /**
     * @var Context|MockObject
     */
    protected $contextMock;

    /**
     * @var ManagerInterface|MockObject
     */
    protected $eventManagerMock;

    /**
     * @var RequestInterface|MockObject
     */
    protected $requestMock;

    /**
     * @var EncoderInterface|MockObject
     */
    protected $urlEncoderMock;

    /**
     * @var Data|MockObject
     */
    protected $backendDataMock;

    /**
     * @var Escaper|MockObject
     */
    protected $escaperMock;

    /**
     * @var string
     */
    protected $path;

    protected function setUp(): void
    {
        $this->path = 'PATH';
        $this->objectManager = new ObjectManager($this);

        $this->eventManagerMock = $this->getMockForAbstractClass(ManagerInterface::class);

        $this->requestMock = $this->getMockForAbstractClass(RequestInterface::class);

        $this->urlEncoderMock = $this->getMockForAbstractClass(EncoderInterface::class);

        $this->backendDataMock = $this->createMock(Data::class);

        $this->contextMock = $this->createMock(Context::class);
        $this->contextMock->expects($this->any())
            ->method('getEventManager')
            ->willReturn($this->eventManagerMock);
        $this->contextMock->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->requestMock);
        $this->contextMock->expects($this->any())
            ->method('getUrlEncoder')
            ->willReturn($this->urlEncoderMock);

        $this->directoryWriteMock = $this->getMockBuilder(Write::class)
            ->setConstructorArgs(['path' => $this->path])
            ->disableOriginalConstructor()
            ->getMock();
        $this->directoryWriteMock->expects($this->any())
            ->method('getAbsolutePath')
            ->willReturnMap(
                [
                    [WysiwygConfig::IMAGE_DIRECTORY, null, $this->getAbsolutePath(WysiwygConfig::IMAGE_DIRECTORY)],
                    [null, null, $this->getAbsolutePath(null)],
                    ['', null, $this->getAbsolutePath('')],
                ]
            );

        $this->filesystemMock = $this->createMock(Filesystem::class);
        $this->filesystemMock->expects($this->once())
            ->method('getDirectoryWrite')
            ->willReturn($this->directoryWriteMock);

        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->setMethods(
                [
                    'clearWebsiteCache', 'getDefaultStoreView', 'getGroup', 'getGroups',
                    'getStore', 'getStores', 'getWebsite', 'getWebsites', 'hasSingleStore',
                    'isSingleStoreMode', 'reinitStores', 'setCurrentStore', 'setIsSingleStoreModeAllowed',
                ]
            )
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->storeMock = $this->createMock(Store::class);

        $this->escaperMock = $this->createMock(Escaper::class);

        $this->imagesHelper = $this->objectManager->getObject(
            Images::class,
            [
                'context' => $this->contextMock,
                'filesystem' => $this->filesystemMock,
                'storeManager' => $this->storeManagerMock,
                'backendData' => $this->backendDataMock,
                'escaper' => $this->escaperMock,
            ]
        );
    }

    protected function tearDown(): void
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
        $this->escaperMock = null;
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
            $this->getAbsolutePath(''),
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
            ->with(UrlInterface::URL_TYPE_MEDIA);
        $this->imagesHelper->getBaseUrl();
    }

    public function testGetTreeNodeName()
    {
        $this->assertEquals('node', $this->imagesHelper->getTreeNodeName());
    }

    public function testConvertPathToId()
    {
        $pathOne = '/test_path';
        $pathTwo = $this->getAbsolutePath('') . '/test_path';
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
            ['/test_path', 'L3Rlc3RfcGF0aA--'],
        ];
    }

    public function testConvertIdToPathNodeRoot()
    {
        $pathId = Storage::NODE_ROOT;
        $this->assertEquals($this->imagesHelper->getStorageRoot(), $this->imagesHelper->convertIdToPath($pathId));
    }

    public function testConvertIdToPathInvalid()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Path is invalid');
        $this->imagesHelper->convertIdToPath('Ly4uLy4uLy4uLy4uLy4uL3dvcms-');
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
            ['test', 20, 'test'],
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
            ['Some text for this unit test', 'Some text for this u...'],
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
        $checkResult = new \stdClass();
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
            [false],
        ];
    }

    /**
     * @param string $pathId
     * @param string $expectedPath
     * @param bool $isExist
     * @dataProvider providerGetCurrentPath
     */
    public function testGetCurrentPath($pathId, $subDir, $expectedPath, $isExist)
    {
        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->willReturnMap(
                [
                    ['node', null, $pathId],
                    ['current_tree_path', null, $subDir],
                ]
            );

        $this->directoryWriteMock->expects($this->any())
            ->method('isDirectory')
            ->willReturnMap(
                [
                    ['/../test_path', true],
                    ['/../my.jpg', false],
                    ['.', true],
                ]
            );
        $this->directoryWriteMock->expects($this->any())
            ->method('getRelativePath')
            ->willReturnMap(
                [
                    ['PATH/test_path', '/../test_path'],
                    ['PATH/my.jpg', '/../my.jpg'],
                    ['PATH', '.'],
                ]
            );

        if ($subDir) {
            $this->directoryWriteMock->expects($this->once())
                ->method('isExist')
                ->willReturn($isExist);
            $this->directoryWriteMock->expects($this->any())
                ->method('create')
                ->with($this->directoryWriteMock->getRelativePath($expectedPath));
        }

        $this->assertEquals($expectedPath, $this->imagesHelper->getCurrentPath());
    }

    public function testGetCurrentPathThrowException()
    {
        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->willReturn('PATH');

        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage(
            'Can\'t create SUBDIR as subdirectory of PATH, you might have some permission issue.'
        );

        $this->directoryWriteMock->expects($this->any())
            ->method('getRelativePath')
            ->willReturn('SUBDIR');
        $this->directoryWriteMock->expects($this->once())
            ->method('isExist')
            ->willReturn(false);
        $this->directoryWriteMock->expects($this->any())
            ->method('create')
            ->willThrowException(
                new FileSystemException(__('Could not create a directory.'))
            );

        $this->imagesHelper->getCurrentPath();

        $this->fail('An expected exception has not been raised.');
    }

    /**
     * @return array
     */
    public function providerGetCurrentPath()
    {
        return [
            ['L3Rlc3RfcGF0aA--', 'L3Rlc3RfcGF0aA--', 'PATH/test_path', true],
            ['L215LmpwZw--', '', 'PATH', true],
            [null, '', 'PATH', true],
            ['L3Rlc3RfcGF0aA--', 'L3Rlc3RfcGF0aA--', 'PATH/test_path', false],
            ['L215LmpwZw--', '', 'PATH', false],
            [null, '', 'PATH', false],
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
            ->with(UrlInterface::URL_TYPE_MEDIA)
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
     * @param string|null $escapedValue
     * @param string $expectedHtml
     * @dataProvider providerGetImageHtmlDeclarationRenderingAsTag
     */
    public function testGetImageHtmlDeclarationRenderingAsTag(
        $baseUrl,
        $fileName,
        $isUsingStaticUrls,
        $escapedValue,
        $expectedHtml
    ) {
        $this->generalSettingsGetImageHtmlDeclaration($baseUrl, $isUsingStaticUrls, $escapedValue);
        $this->assertEquals($expectedHtml, $this->imagesHelper->getImageHtmlDeclaration($fileName, true));
    }

    /**
     * @return array
     */
    public function providerGetImageHtmlDeclarationRenderingAsTag()
    {
        return [
            [
                'http://localhost',
                'test.png',
                true,
                null,
                '<img src="http://localhost/test.png" alt="" />',
            ],
            [
                'http://localhost',
                'test.png',
                false,
                '{{media url=&quot;/test.png&quot;}}',
                '<img src="{{media url=&quot;/test.png&quot;}}" alt="" />',
            ],
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
            ->with('cms/wysiwyg/directive', ['___directive' => $directive, '_escape_params' => false])
            ->willReturn($directive);

        $this->assertEquals($expectedHtml, $this->imagesHelper->getImageHtmlDeclaration($fileName));
    }

    /**
     * @return array
     */
    public function providerGetImageHtmlDeclaration()
    {
        return [
            ['http://localhost', 'test.png', true, 'http://localhost/test.png'],
            ['http://localhost', 'test.png', false, '{{media url="/test.png"}}'],
        ];
    }

    /**
     * @param string $baseUrl
     * @param bool $isUsingStaticUrls
     * @param string|null $escapedValue
     */
    protected function generalSettingsGetImageHtmlDeclaration($baseUrl, $isUsingStaticUrls, $escapedValue = null)
    {
        $storeId = 1;
        $this->imagesHelper->setStoreId($storeId);

        $this->storeMock->expects($this->any())
            ->method('getBaseUrl')
            ->with(UrlInterface::URL_TYPE_MEDIA)
            ->willReturn($baseUrl);
        $this->storeManagerMock->expects($this->any())
            ->method('getStore')
            ->willReturn($this->storeMock);

        if ($escapedValue) {
            $this->escaperMock->expects($this->once())->method('escapeHtml')->willReturn($escapedValue);
        }

        $this->generalSettingsIsUsingStaticUrlsAllowed($isUsingStaticUrls);
    }
}
