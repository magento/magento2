<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Storage helper test
 */
namespace Magento\Theme\Test\Unit\Helper;

use Magento\Framework\Filesystem\DriverInterface;
use Magento\Theme\Helper\Storage;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StorageTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Filesystem|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $filesystem;

    /**
     * @var \Magento\Backend\Model\Session|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $session;

    /**
     * @var \Magento\Framework\View\Design\Theme\FlyweightFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $themeFactory;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
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
     * @var \Magento\Framework\Filesystem\Directory\Write|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $directoryWrite;

    /**
     * @var \Magento\Framework\App\Helper\Context|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $contextHelper;

    /**
     * @var \Magento\Theme\Model\Theme|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $theme;

    /**
     * @var \Magento\Framework\View\Design\Theme\Customization|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $customization;

    /**
     * @var \Magento\Framework\Url\EncoderInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $urlEncoder;

    /**
     * @var \Magento\Framework\Url\DecoderInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $urlDecoder;

    protected $requestParams;

    /**
     * @var DriverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filesystemDriver;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->customizationPath = '/' . implode('/', ['var', 'theme']);

        $this->request = $this->createMock(\Magento\Framework\App\Request\Http::class);
        $this->filesystem = $this->createMock(\Magento\Framework\Filesystem::class);
        $this->session = $this->createMock(\Magento\Backend\Model\Session::class);
        $this->contextHelper = $this->createMock(\Magento\Framework\App\Helper\Context::class);
        $this->directoryWrite = $this->createMock(\Magento\Framework\Filesystem\Directory\Write::class);
        $this->themeFactory = $this->createMock(\Magento\Framework\View\Design\Theme\FlyweightFactory::class);
        $this->theme = $this->createMock(\Magento\Theme\Model\Theme::class);
        $this->customization = $this->createMock(\Magento\Framework\View\Design\Theme\Customization::class);

        $this->filesystem->expects($this->any())
            ->method('getDirectoryWrite')
            ->willReturn($this->directoryWrite);
        $this->urlEncoder = $this->getMockBuilder(\Magento\Framework\Url\EncoderInterface::class)->getMock();
        $this->urlDecoder = $this->getMockBuilder(\Magento\Framework\Url\DecoderInterface::class)->getMock();

        $this->directoryWrite->expects($this->any())->method('create')->willReturn(true);
        $this->contextHelper->expects($this->any())->method('getRequest')->willReturn($this->request);
        $this->contextHelper->expects($this->any())->method('getUrlEncoder')->willReturn($this->urlEncoder);
        $this->contextHelper->expects($this->any())->method('getUrlDecoder')->willReturn($this->urlDecoder);
        $this->themeFactory->expects($this->any())->method('create')->willReturn($this->theme);
        $this->filesystemDriver = $this->createMock(DriverInterface::class);

        $this->theme->expects($this->any())
            ->method('getCustomization')
            ->willReturn($this->customization);

        $this->request->expects($this->at(0))
            ->method('getParam')
            ->with(Storage::PARAM_THEME_ID)
            ->willReturn(6);
        $this->request->expects($this->at(1))
            ->method('getParam')
            ->with(Storage::PARAM_CONTENT_TYPE)
            ->willReturn(\Magento\Theme\Model\Wysiwyg\Storage::TYPE_IMAGE);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->helper = $objectManager->getObject(
            Storage::class,
            [
                'context' => $this->contextHelper,
                'filesystem' => $this->filesystem,
                'session' => $this->session,
                'themeFactory' => $this->themeFactory,
                'file' => null,
                'filesystemDriver' => $this->filesystemDriver,
            ]
        );
    }

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
     * @covers \Magento\Theme\Helper\Storage::getShortFilename
     * @covers \Magento\Theme\Helper\Storage::__construct
     */
    public function testGetShortFilename()
    {
        $longFileName = 'veryLongFileNameMoreThanTwenty';
        $expectedFileName = 'veryLongFileNameMore...';
        $this->assertEquals($expectedFileName, $this->helper->getShortFilename($longFileName, 20));
    }

    public function testGetStorageRoot()
    {
        $expectedStorageRoot = '/' . \Magento\Theme\Model\Wysiwyg\Storage::TYPE_IMAGE;
        $this->assertEquals($expectedStorageRoot, $this->helper->getStorageRoot());
    }

    public function testGetThumbnailDirectory()
    {
        $imagePath = implode('/', ['root', 'image', 'image_name.jpg']);
        $thumbnailDir = implode(
            '/',
            ['root', 'image', \Magento\Theme\Model\Wysiwyg\Storage::THUMBNAIL_DIRECTORY]
        );

        $this->assertEquals($thumbnailDir, $this->helper->getThumbnailDirectory($imagePath));
    }

    public function testGetThumbnailPath()
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

    public function testGetRequestParams()
    {
        $this->request->expects(
            $this->at(0)
        )->method(
            'getParam'
        )->with(
            Storage::PARAM_THEME_ID
        )->willReturn(
            6
        );
        $this->request->expects(
            $this->at(1)
        )->method(
            'getParam'
        )->with(
            Storage::PARAM_CONTENT_TYPE
        )->willReturn(
            'image'
        );
        $this->request->expects(
            $this->at(2)
        )->method(
            'getParam'
        )->with(
            Storage::PARAM_NODE
        )->willReturn(
            'node'
        );

        $expectedResult = [
            Storage::PARAM_THEME_ID => 6,
            Storage::PARAM_CONTENT_TYPE => \Magento\Theme\Model\Wysiwyg\Storage::TYPE_IMAGE,
            Storage::PARAM_NODE => 'node',
        ];
        $this->assertEquals($expectedResult, $this->helper->getRequestParams());
    }

    public function testGetAllowedExtensionsByType()
    {
        $this->request->expects(
            $this->at(0)
        )->method(
            'getParam'
        )->with(
            Storage::PARAM_CONTENT_TYPE
        )->willReturn(
            \Magento\Theme\Model\Wysiwyg\Storage::TYPE_FONT
        );

        $this->request->expects(
            $this->at(1)
        )->method(
            'getParam'
        )->with(
            Storage::PARAM_CONTENT_TYPE
        )->willReturn(
            \Magento\Theme\Model\Wysiwyg\Storage::TYPE_IMAGE
        );

        $fontTypes = $this->helper->getAllowedExtensionsByType();
        $this->assertEquals(['ttf', 'otf', 'eot', 'svg', 'woff'], $fontTypes);

        $imagesTypes = $this->helper->getAllowedExtensionsByType();
        $this->assertEquals(['jpg', 'jpeg', 'gif', 'png', 'xbm', 'wbmp'], $imagesTypes);
    }

    /**
     * @test
     * @return void
     */
    public function testGetThumbnailPathNotFound()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The image not found');

        $this->filesystemDriver->method('getRealpathSafety')->willReturnArgument(0);
        $image = 'notFoundImage.png';
        $root = '/image';
        $sourceNode = '/not/a/root';
        $node = base64_encode($sourceNode);
        $this->request->expects($this->at(0))
            ->method('getParam')
            ->willReturnMap(
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
                    ],
                ]
            );
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
     * @covers \Magento\Theme\Helper\Storage::convertPathToId
     * @covers \Magento\Theme\Helper\Storage::convertIdToPath
     */
    public function testConvertPathToIdAndIdToPath()
    {
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

    public function testGetSession()
    {
        $this->assertInstanceOf(\Magento\Backend\Model\Session::class, $this->helper->getSession());
    }

    public function testGetRelativeUrl()
    {
        $filename = base64_encode('filename.ext');
        $notRoot = base64_encode('not/a/root');
        $this->request->expects($this->any())
            ->method('getParam')
            ->willReturnMap(
                [
                    'type' => [
                        Storage::PARAM_CONTENT_TYPE,
                        null,
                        \Magento\Theme\Model\Wysiwyg\Storage::TYPE_IMAGE,
                    ],
                    'node' => [
                        Storage::PARAM_NODE,
                        null,
                        $notRoot,
                    ],
                    'filenaem' => [
                        Storage::PARAM_FILENAME,
                        null,
                        $filename,
                    ],
                ]
            );
        $decode = function ($value) {
            return base64_decode($value);
        };
        $this->urlDecoder->expects($this->at(0))
            ->method('decode')
            ->with($notRoot)
            ->willReturnCallback($decode);
        $this->urlDecoder->expects($this->at(1))
            ->method('decode')
            ->with($filename)
            ->willReturnCallback($decode);

        $this->assertEquals(
            '../image/not/a/root/filename.ext',
            $this->helper->getRelativeUrl()
        );
    }

    /**
     * @return array
     */
    public function getStorageTypeForNameDataProvider()
    {
        return [
            'font' => [\Magento\Theme\Model\Wysiwyg\Storage::TYPE_FONT, Storage::FONTS],
            'image' => [\Magento\Theme\Model\Wysiwyg\Storage::TYPE_IMAGE, Storage::IMAGES],
        ];
    }

    /**
     * @test
     * @param string $type
     * @param string $name
     * @return void
     * @dataProvider getStorageTypeForNameDataProvider
     */
    public function testGetStorageTypeName($type, $name)
    {
        $this->request->expects($this->once())
            ->method('getParam')
            ->with(Storage::PARAM_CONTENT_TYPE)
            ->willReturn($type);

        $this->assertEquals($name, $this->helper->getStorageTypeName());
    }

    /**
     * @test
     * @return void
     */
    public function testGetStorageTypeNameInvalid()
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->expectExceptionMessage('Invalid type');

        $this->helper->getStorageTypeName();
    }

    /**
     * @test
     * @return void
     */
    public function testGetThemeNotFound()
    {
        $this->expectException(\InvalidArgumentException::class);
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
     *
     * @return void
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
     * @dataProvider getCurrentPathDataProvider
     *
     * @return void
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

        $this->request->method('getParam')
            ->with(Storage::PARAM_NODE)
            ->willReturn($requestedPath);

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
            'real path should resolve to relative path' => ['foo/', $rootPath . '/foo', true, 'foo/'],
        ];
    }
}
