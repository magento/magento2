<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Helper;

use Magento\Backend\Model\Session;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Write;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Url\DecoderInterface;
use Magento\Framework\Url\EncoderInterface;
use Magento\Framework\View\Design\Theme\Customization;
use Magento\Framework\View\Design\Theme\FlyweightFactory;
use Magento\Theme\Helper\Storage;
use Magento\Theme\Model\Theme;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Storage helper test.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StorageTest extends TestCase
{
    /**
     * @var Filesystem|MockObject
     */
    protected $filesystem;

    /**
     * @var Session|MockObject
     */
    protected $session;

    /**
     * @var FlyweightFactory|MockObject
     */
    protected $themeFactory;

    /**
     * @var MockObject
     */
    protected $request;

    /**
     * @var Storage
     */
    protected $helper;

    /**
     * @var string
     */
    protected $customizationPath;

    /**
     * @var Write|MockObject
     */
    protected $directoryWrite;

    /**
     * @var Context|MockObject
     */
    protected $contextHelper;

    /**
     * @var Theme|MockObject
     */
    protected $theme;

    /**
     * @var Customization|MockObject
     */
    protected $customization;

    /**
     * @var EncoderInterface|MockObject
     */
    protected $urlEncoder;

    /**
     * @var DecoderInterface|MockObject
     */
    protected $urlDecoder;

    /**
     * @var DriverInterface|MockObject
     */
    private $filesystemDriver;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->customizationPath = '/' . implode('/', ['var', 'theme']);

        $this->request = $this->createMock(Http::class);
        $this->filesystem = $this->createMock(Filesystem::class);
        $this->session = $this->createMock(Session::class);
        $this->contextHelper = $this->createMock(Context::class);
        $this->directoryWrite = $this->createMock(Write::class);
        $this->themeFactory = $this->createMock(FlyweightFactory::class);
        $this->theme = $this->createMock(Theme::class);
        $this->customization = $this->createMock(Customization::class);

        $this->filesystem->expects($this->any())
            ->method('getDirectoryWrite')
            ->willReturn($this->directoryWrite);
        $this->urlEncoder = $this->getMockBuilder(EncoderInterface::class)
            ->getMock();
        $this->urlDecoder = $this->getMockBuilder(DecoderInterface::class)
            ->getMock();

        $this->initializeDefaultRequestMock();

        $this->directoryWrite->expects($this->any())->method('create')->willReturn(true);
        $this->contextHelper->expects($this->any())->method('getRequest')->willReturn($this->request);
        $this->contextHelper->expects($this->any())->method('getUrlEncoder')->willReturn($this->urlEncoder);
        $this->contextHelper->expects($this->any())->method('getUrlDecoder')->willReturn($this->urlDecoder);
        $this->themeFactory->expects($this->any())->method('create')->willReturn($this->theme);
        $this->filesystemDriver = $this->createMock(DriverInterface::class);

        $this->theme->expects($this->any())
            ->method('getCustomization')
            ->willReturn($this->customization);

        $this->helper = new Storage(
            $this->contextHelper,
            $this->filesystem,
            $this->session,
            $this->themeFactory,
            null,
            $this->filesystemDriver
        );
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        $this->request = null;
        $this->filesystem = null;
        $this->session = null;
        $this->contextHelper = null;
        $this->directoryWrite = null;
        $this->themeFactory = null;
        $this->theme = null;
        $this->customization = null;
    }

    /**
     * @return void
     * @covers \Magento\Theme\Helper\Storage::getShortFilename
     * @covers \Magento\Theme\Helper\Storage::__construct
     */
    public function testGetShortFilename(): void
    {
        $this->initializeDefaultRequestMock();
        $longFileName = 'veryLongFileNameMoreThanTwenty';
        $expectedFileName = 'veryLongFileNameMore...';
        $this->assertEquals($expectedFileName, $this->helper->getShortFilename($longFileName, 20));
    }

    /**
     * @return void
     */
    public function testGetStorageRoot(): void
    {
        $this->initializeDefaultRequestMock();
        $expectedStorageRoot = '/' . \Magento\Theme\Model\Wysiwyg\Storage::TYPE_IMAGE;
        $this->assertEquals($expectedStorageRoot, $this->helper->getStorageRoot());
    }

    /**
     * @return void
     */
    public function testGetThumbnailDirectory(): void
    {
        $this->initializeDefaultRequestMock();
        $imagePath = implode('/', ['root', 'image', 'image_name.jpg']);
        $thumbnailDir = implode(
            '/',
            ['root', 'image', \Magento\Theme\Model\Wysiwyg\Storage::THUMBNAIL_DIRECTORY]
        );

        $this->assertEquals($thumbnailDir, $this->helper->getThumbnailDirectory($imagePath));
    }

    /**
     * @return void
     */
    public function testGetThumbnailPath(): void
    {
        $image = 'image_name.jpg';
        $thumbnailPath = '/' . implode(
            '/',
            [
                \Magento\Theme\Model\Wysiwyg\Storage::TYPE_IMAGE,
                \Magento\Theme\Model\Wysiwyg\Storage::THUMBNAIL_DIRECTORY,
                $image
            ]
        );

        $this->customization->expects(
            $this->any()
        )->method(
            'getCustomizationPath'
        )->willReturn(
            $this->customizationPath
        );

        $this->directoryWrite->expects($this->any())->method('isExist')->willReturn(true);

        $this->assertEquals($thumbnailPath, $this->helper->getThumbnailPath($image));
    }

    /**
     * @return void
     */
    public function testGetRequestParams(): void
    {
        $withArgs = [
            [Storage::PARAM_THEME_ID],
            [Storage::PARAM_CONTENT_TYPE],
            [Storage::PARAM_NODE]
        ];
        $willReturnArgs = [6, 'image', 'node'];
        $this->resetRequestMock($withArgs, $willReturnArgs);

        $expectedResult = [
            Storage::PARAM_THEME_ID => 6,
            Storage::PARAM_CONTENT_TYPE => \Magento\Theme\Model\Wysiwyg\Storage::TYPE_IMAGE,
            Storage::PARAM_NODE => 'node'
        ];
        $this->assertEquals($expectedResult, $this->helper->getRequestParams());
    }

    /**
     * @return void
     */
    public function testGetAllowedExtensionsByType(): void
    {
        $withArgs = [
            [Storage::PARAM_CONTENT_TYPE],
            [Storage::PARAM_CONTENT_TYPE]
        ];
        $willReturnArgs = [
            \Magento\Theme\Model\Wysiwyg\Storage::TYPE_FONT,
            \Magento\Theme\Model\Wysiwyg\Storage::TYPE_IMAGE
        ];
        $this->resetRequestMock($withArgs, $willReturnArgs);

        $fontTypes = $this->helper->getAllowedExtensionsByType();
        $this->assertEquals(['ttf', 'otf', 'eot', 'svg', 'woff'], $fontTypes);

        $imagesTypes = $this->helper->getAllowedExtensionsByType();
        $this->assertEquals(['jpg', 'jpeg', 'gif', 'png', 'xbm', 'wbmp'], $imagesTypes);
    }

    /**
     * @test
     * @return void
     */
    public function testGetThumbnailPathNotFound(): void
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('The image not found');

        $this->filesystemDriver->method('getRealpathSafety')
            ->willReturnArgument(0);
        $image = 'notFoundImage.png';
        $root = '/image';
        $sourceNode = '/not/a/root';
        $node = base64_encode($sourceNode);

        $withArgs = [];
        $willReturnArg = $this->returnValueMap(
            [
                [
                    Storage::PARAM_THEME_ID,
                    null,
                    6,
                ],
                [
                    Storage::PARAM_CONTENT_TYPE,
                    null,
                    \Magento\Theme\Model\Wysiwyg\Storage::TYPE_IMAGE
                ],
                [
                    Storage::PARAM_NODE,
                    null,
                    $node
                ]
            ]
        );
        $this->resetRequestMock($withArgs, [$willReturnArg]);

        $this->urlDecoder->expects($this->once())
            ->method('decode')
            ->with($node)
            ->willReturnCallback(function ($path) {
                return base64_decode($path);
            });
        $this->directoryWrite->expects($this->once())
            ->method('isDirectory')
            ->with($root . $sourceNode)
            ->willReturn(true);
        $this->directoryWrite->expects($this->once())
            ->method('getRelativePath')
            ->with($root . $sourceNode)
            ->willReturn($sourceNode);
        $this->directoryWrite->expects($this->once())
            ->method('isExist')
            ->with($sourceNode . '/' . $image);

        $this->helper->getThumbnailPath($image);
    }

    /**
     * @return void
     * @covers \Magento\Theme\Helper\Storage::convertPathToId
     * @covers \Magento\Theme\Helper\Storage::convertIdToPath
     */
    public function testConvertPathToIdAndIdToPath(): void
    {
        $this->initializeDefaultRequestMock();
        $path = '/image/path/to';
        $this->urlEncoder->expects($this->once())
            ->method('encode')
            ->with('/path/to')
            ->willReturnCallback(function ($path) {
                return base64_encode($path);
            });
        $this->urlDecoder->expects($this->once())
            ->method('decode')
            ->with(base64_encode('/path/to'))
            ->willReturnCallback(function ($path) {
                return base64_decode($path);
            });

        $value = $this->helper->convertPathToId($path);
        $this->assertEquals(base64_encode('/path/to'), $value);
        $this->assertEquals($path, $this->helper->convertIdToPath($value));
    }

    /**
     * @return void
     */
    public function testGetSession(): void
    {
        $this->initializeDefaultRequestMock();
        $this->assertInstanceOf(Session::class, $this->helper->getSession());
    }

    /**
     * @return void
     */
    public function testGetRelativeUrl(): void
    {
        $filename = base64_encode('filename.ext');
        $notRoot = base64_encode('not/a/root');
        $withArgs = [[Storage::PARAM_CONTENT_TYPE], [Storage::PARAM_NODE], [Storage::PARAM_FILENAME]];
        $willReturnArgs = [\Magento\Theme\Model\Wysiwyg\Storage::TYPE_IMAGE, $notRoot, $filename];
        $this->resetRequestMock($withArgs, $willReturnArgs);
        $decode = function ($value) {
            return base64_decode($value);
        };
        $this->urlDecoder
            ->method('decode')
            ->withConsecutive([$notRoot], [$filename])
            ->willReturnOnConsecutiveCalls($this->returnCallback($decode), $this->returnCallback($decode));

        $this->assertEquals(
            '../image/not/a/root/filename.ext',
            $this->helper->getRelativeUrl()
        );
    }

    /**
     * @return array
     */
    public function getStorageTypeForNameDataProvider(): array
    {
        return [
            'font' => [\Magento\Theme\Model\Wysiwyg\Storage::TYPE_FONT, Storage::FONTS],
            'image' => [\Magento\Theme\Model\Wysiwyg\Storage::TYPE_IMAGE, Storage::IMAGES]
        ];
    }

    /**
     * @test
     * @param string $type
     * @param string $name
     *
     * @return void
     * @dataProvider getStorageTypeForNameDataProvider
     */
    public function testGetStorageTypeName($type, $name): void
    {
        $this->resetRequestMock([[Storage::PARAM_CONTENT_TYPE]], [$type]);

        $this->assertEquals($name, $this->helper->getStorageTypeName());
    }

    /**
     * @test
     * @return void
     */
    public function testGetStorageTypeNameInvalid(): void
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('Invalid type');
        $this->helper->getStorageTypeName();
    }

    /**
     * @test
     * @return void
     */
    public function testGetThemeNotFound(): void
    {
        $this->initializeDefaultRequestMock();
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Theme was not found');
        $this->themeFactory->expects($this->once())
            ->method('create')
            ->willReturn(null);
        $helper = new Storage(
            $this->contextHelper,
            $this->filesystem,
            $this->session,
            $this->themeFactory
        );
        $helper->getStorageRoot();
    }

    /**
     * @dataProvider getCurrentPathDataProvider
     */
    public function testGetCurrentPathCachesResult(): void
    {
        $this->request->expects($this->once())
            ->method('getParam')
            ->with(Storage::PARAM_NODE)
            ->willReturn(Storage::NODE_ROOT);

        $actualPath = $this->helper->getCurrentPath();
        self::assertSame('/image', $actualPath);
    }

    /**
     * @return void
     * @dataProvider getCurrentPathDataProvider
     */
    public function testGetCurrentPath(
        string $expectedPath,
        string $requestedPath,
        ?bool $isDirectory = null,
        ?string $relativePath = null,
        ?string $resolvedPath = null
    ): void {
        $this->directoryWrite->method('isDirectory')
            ->willReturn($isDirectory);

        $this->directoryWrite->method('getRelativePath')
            ->willReturn($relativePath);

        $this->urlDecoder->method('decode')
            ->willReturnArgument(0);

        if ($resolvedPath) {
            $this->filesystemDriver->method('getRealpathSafety')
                ->willReturn($resolvedPath);
        } else {
            $this->filesystemDriver->method('getRealpathSafety')
                ->willReturnArgument(0);
        }
        $this->resetRequestMock([[Storage::PARAM_NODE]], [$requestedPath]);

        $actualPath = $this->helper->getCurrentPath();

        self::assertSame($expectedPath, $actualPath);
    }

    /**
     * @return array
     */
    public function getCurrentPathDataProvider(): array
    {
        $rootPath = '/' . \Magento\Theme\Model\Wysiwyg\Storage::TYPE_IMAGE;

        return [
            'requested path "root" should short-circuit' => [$rootPath, Storage::NODE_ROOT],
            'non-existent directory should default to the base path' => [$rootPath, $rootPath . '/foo'],
            'requested path that resolves to a bad path should default to root' =>
                [$rootPath, $rootPath . '/something', true, null, '/bar'],
            'real path should resolve to relative path' => ['foo/', $rootPath . '/foo', true, 'foo/']
        ];
    }

    /**
     * @return void
     */
    private function initializeDefaultRequestMock(): void
    {
        $this->request
            ->method('getParam')
            ->withConsecutive(
                [Storage::PARAM_THEME_ID],
                [Storage::PARAM_CONTENT_TYPE]
            )
            ->willReturnOnConsecutiveCalls(
                6,
                \Magento\Theme\Model\Wysiwyg\Storage::TYPE_IMAGE
            );
    }

    /**
     * @param array $withArgs
     * @param array $willReturnArgs
     *
     * @return void
     */
    private function resetRequestMock(array $withArgs, array $willReturnArgs): void
    {
        array_unshift($withArgs, [Storage::PARAM_THEME_ID], [Storage::PARAM_CONTENT_TYPE]);
        array_unshift($willReturnArgs, 6, \Magento\Theme\Model\Wysiwyg\Storage::TYPE_IMAGE);
        $this->request = $this->createMock(Http::class);
        $this->contextHelper = $this->createMock(Context::class);
        $this->contextHelper->expects($this->any())->method('getUrlEncoder')->willReturn($this->urlEncoder);
        $this->contextHelper->expects($this->any())->method('getUrlDecoder')->willReturn($this->urlDecoder);
        $this->contextHelper->expects($this->any())->method('getRequest')->willReturn($this->request);
        $this->request
            ->method('getParam')
            ->withConsecutive(...$withArgs)
            ->willReturnOnConsecutiveCalls(...$willReturnArgs);

        $this->helper = new Storage(
            $this->contextHelper,
            $this->filesystem,
            $this->session,
            $this->themeFactory,
            null,
            $this->filesystemDriver
        );
    }
}
