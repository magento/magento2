<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Downloadable\Test\Unit\Helper;

use Magento\Downloadable\Model\Url\DomainValidator;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\HTTP\Client\CurlFactory;
use Psr\Log\LoggerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Downloadable\Helper\Download as DownloadHelper;
use Magento\Downloadable\Helper\File as DownloadableFile;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\File\Mime;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface as DirReadInterface;
use Magento\Framework\Filesystem\File\ReadFactory;
use Magento\Framework\Filesystem\File\ReadInterface as FileReadInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\MediaStorage\Helper\File\Storage\Database;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class DownloadTest extends TestCase
{
    /** @var int Result of connection_aborted() */
    public static $connectionAborted = 0;

    /** @var DownloadHelper|null */
    protected $_helper;

    /** @var Filesystem|MockObject */
    protected $_filesystemMock;

    /** @var FileReadInterface|MockObject */
    protected $_handleMock;

    /** @var DirReadInterface|MockObject */
    protected $_workingDirectoryMock;

    /** @var DownloadableFile|MockObject */
    protected $_downloadableFileMock;

    /** @var  SessionManagerInterface|MockObject */
    protected $sessionManager;

    /** @var ReadFactory|MockObject */
    protected $fileReadFactory;

    /** @var bool Result of function_exists() */
    public static $functionExists;

    /** @var string Result of mime_content_type() */
    public static $mimeContentType;

    private const FILE_SIZE = 4096;

    private const FILE_PATH = '/some/path';

    private const MIME_TYPE = 'image/png';

    private const URL = 'http://example.com';

    /**
     * @var Mime|MockObject
     */
    private $mime;

    /** @var CurlFactory|MockObject */
    private $httpClientFactory;

    /** @var DomainValidator|MockObject */
    private $domainValidator;

    /** @var LoggerInterface|MockObject */
    private $logger;

    /** @var int */
    private int $headerReadCount = 0;

    /** @var bool */
    private bool $sessionClosed = false;

    /** @var array */
    private array $requestSessionStates = [];

    protected function setUp(): void
    {
        require_once __DIR__ . '/../_files/download_mock.php';

        self::$connectionAborted = 0;
        self::$functionExists = true;
        self::$mimeContentType = self::MIME_TYPE;

        $this->_filesystemMock = $this->createStub(Filesystem::class);
        $this->_handleMock = $this->createStub(\Magento\Framework\Filesystem\File\ReadInterface::class);
        $this->_workingDirectoryMock = $this->createStub(DirReadInterface::class);
        $this->_downloadableFileMock = $this->createStub(DownloadableFile::class);
        $this->sessionManager = $this->createStub(SessionManagerInterface::class);
        $this->fileReadFactory = $this->createStub(ReadFactory::class);
        $this->mime = $this->createStub(Mime::class);
        $this->httpClientFactory = $this->createStub(CurlFactory::class);
        $this->domainValidator = $this->createStub(DomainValidator::class);
        $this->logger = $this->createStub(LoggerInterface::class);
        $this->_helper = null;
    }

    private function getHelper(): DownloadHelper
    {
        if ($this->_helper === null) {
            $context = $this->createStub(Context::class);
            $context->method('getLogger')->willReturn($this->logger);
            $this->_helper = (new ObjectManager($this))->getObject(
                DownloadHelper::class,
                [
                    'context' => $context,
                    'downloadableFile' => $this->_downloadableFileMock,
                    'coreFileStorageDb' => $this->createStub(Database::class),
                    'filesystem' => $this->_filesystemMock,
                    'session' => $this->sessionManager,
                    'fileReadFactory' => $this->fileReadFactory,
                    'mime' => $this->mime,
                    'httpClientFactory' => $this->httpClientFactory,
                    'domainValidator' => $this->domainValidator
                ]
            );
        }
        return $this->_helper;
    }

    protected function tearDown(): void
    {
        self::$connectionAborted = 0;
        parent::tearDown();
    }

    public function testSetResourceInvalidPath()
    {
        $this->expectException('InvalidArgumentException');
        $this->getHelper()->setResource('/some/path/../file', DownloadHelper::LINK_TYPE_FILE);
    }

    public function testGetFileSizeNoResource()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('Please set resource file and link type.');
        $this->getHelper()->getFileSize();
    }

    public function testGetFileSizeInvalidLinkType()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('Invalid download link type.');
        $this->getHelper()->setResource(self::FILE_PATH, 'The link type is invalid. Verify and try again.');
        $this->getHelper()->getFileSize();
    }

    public function testGetFileSizeUrl()
    {
        $this->_setupUrlMocks();
        $this->assertEquals(self::FILE_SIZE, $this->getHelper()->getFileSize());
    }

    public function testGetFileSize()
    {
        $this->_setupFileMocks();
        $this->assertEquals(self::FILE_SIZE, $this->getHelper()->getFileSize());
    }

    public function testGetFileSizeNoFile()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('Invalid download link type.');
        $this->_setupFileMocks(false);
        $this->getHelper()->getFileSize();
    }

    public function testGetContentType()
    {
        $this->mime = $this->createMock(Mime::class);

        $this->mime->expects(
            self::once()
        )->method(
            'getMimeType'
        )->willReturn(
            self::MIME_TYPE
        );
        $this->_setupFileMocks();
        $this->_downloadableFileMock->expects($this->never())->method('getFileType');
        $this->_workingDirectoryMock->expects($this->once())->method('getAbsolutePath')
            ->willReturn('/path/to/file.txt');
        $this->assertEquals(self::MIME_TYPE, $this->getHelper()->getContentType());
    }

    #[DataProvider('dataProviderForTestGetContentTypeThroughHelper')]
    public function testGetContentTypeThroughHelper($functionExistsResult, $mimeContentTypeResult)
    {
        $this->mime = $this->createMock(Mime::class);

        $this->_setupFileMocks();
        self::$functionExists = $functionExistsResult;
        self::$mimeContentType = $mimeContentTypeResult;

        $this->mime->expects(
            self::once()
        )->method(
            'getMimeType'
        )->willReturn(
            self::MIME_TYPE
        );

        $this->assertEquals(self::MIME_TYPE, $this->getHelper()->getContentType());
    }

    /**
     * @return array
     */
    public static function dataProviderForTestGetContentTypeThroughHelper()
    {
        return [[false, ''], [true, false]];
    }

    public function testGetContentTypeUrl()
    {
        $this->_setupUrlMocks();
        $this->assertEquals(self::MIME_TYPE, $this->getHelper()->getContentType());
    }

    public function testGetFilename()
    {
        $baseName = 'base_name.file';
        $path = TESTS_TEMP_DIR . '/' . $baseName;
        $this->_setupFileMocks(true, self::FILE_SIZE, $path);
        $this->assertEquals($baseName, $this->getHelper()->getFilename());
    }

    public function testGetFileNameUrl()
    {
        $this->_setupUrlMocks();
        $this->assertEquals('download', $this->getHelper()->getFilename());
    }

    #[DataProvider('urlFilenameProvider')]
    public function testUrlFilenameUsesOnlyPath(string $url, string $expected): void
    {
        $this->httpClientFactory = $this->createMock(CurlFactory::class);
        $probe = $this->createClient($url);
        $this->httpClientFactory->expects($this->once())->method('create')->willReturn($probe);
        $this->domainValidator->method('isValid')->willReturn(true);
        $this->getHelper()->setResource($url, DownloadHelper::LINK_TYPE_URL);
        $this->assertSame($expected, $this->getHelper()->getFilename());
    }

    public static function urlFilenameProvider(): array
    {
        return [
            'query and fragment excluded' => [
                'https://example.com/files/report.pdf?token=a/b#preview',
                'report.pdf'
            ],
            'escaped path preserved' => [
                'https://example.com/files/name%2Fpart.bin?signature=a%2Fb',
                'name%2Fpart.bin'
            ],
            'missing path uses default' => ['https://example.com?token=value', 'download']
        ];
    }

    public function testGetFileNameUrlWithContentDisposition()
    {
        $fileName = 'some_other.file';
        $this->_setupUrlMocks(self::FILE_SIZE, self::URL, ['disposition' => "inline; filename={$fileName}"]);
        $this->assertEquals($fileName, $this->getHelper()->getFilename());
    }

    /**
     * @param bool $doesExist
     * @param int $size
     * @param string $path
     */
    protected function _setupFileMocks($doesExist = true, $size = self::FILE_SIZE, $path = self::FILE_PATH)
    {
        $this->_filesystemMock = $this->createMock(Filesystem::class);
        $this->_workingDirectoryMock = $this->createMock(DirReadInterface::class);
        $this->_downloadableFileMock = $this->createMock(DownloadableFile::class);

        $this->_handleMock->method('stat')->willReturn(['size' => $size]);
        $this->_downloadableFileMock->expects($this->any())->method('ensureFileInFilesystem')->with($path)
            ->willReturn($doesExist);
        $this->_workingDirectoryMock->expects($doesExist ? $this->once() : $this->never())->method('openFile')
            ->willReturn($this->_handleMock);
        $this->_filesystemMock->expects($this->any())->method('getDirectoryRead')->with(DirectoryList::MEDIA)
            ->willReturn($this->_workingDirectoryMock);
        $this->getHelper()->setResource($path, DownloadHelper::LINK_TYPE_FILE);
    }

    /**
     * @param int $size
     * @param string $url
     * @param array $additionalStatData
     */
    protected function _setupUrlMocks($size = self::FILE_SIZE, $url = self::URL, $additionalStatData = [])
    {
        $this->fileReadFactory = $this->createMock(ReadFactory::class);
        $this->httpClientFactory = $this->createMock(CurlFactory::class);
        $this->domainValidator = $this->createMock(DomainValidator::class);

        $headers = ['Content-Length' => (string) $size, 'Content-Type' => self::MIME_TYPE];
        if (isset($additionalStatData['disposition'])) {
            $headers['Content-Disposition'] = $additionalStatData['disposition'];
        }
        $client = $this->createClient($url, 200, $headers);
        $this->httpClientFactory->expects($this->once())->method('create')->willReturn($client);
        $this->domainValidator->expects($this->once())->method('isValid')->with($url)->willReturn(true);
        $this->fileReadFactory->expects($this->never())->method('create');
        $this->getHelper()->setResource($url, DownloadHelper::LINK_TYPE_URL);
    }

    private function createClient(
        string $url,
        int $status = 200,
        array $headers = [],
        array $chunks = ['remote body'],
        ?\Exception $failure = null,
        bool $probe = true,
        ?array $headerLines = null
    ): Curl {
        $client = $this->createMock(Curl::class);
        $options = [];
        $client->expects($this->once())->method('setTimeout')->with(0);
        $client->expects($this->once())->method('setOptions')->with([
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_SUPPRESS_CONNECT_HEADERS => true,
            CURLOPT_CONNECTTIMEOUT => 10
        ])->willReturnCallback(static function (array $values) use (&$options) {
            $options = $values;
        });
        $client->expects($this->exactly($probe ? 5 : 2))->method('setOption')
            ->willReturnCallback(function ($key, $value) use (&$options) {
                $this->assertArrayNotHasKey($key, $options);
                $scalarOptions = [CURLOPT_TIMEOUT => 30, CURLOPT_LOW_SPEED_LIMIT => 1, CURLOPT_LOW_SPEED_TIME => 60];
                if (isset($scalarOptions[$key])) {
                    $this->assertSame($scalarOptions[$key], $value);
                } else {
                    $this->assertContains($key, [CURLOPT_HEADERFUNCTION, CURLOPT_WRITEFUNCTION]);
                    $this->assertIsCallable($value);
                }
                $options[$key] = $value;
            });
        $client->expects($this->never())->method('getStatus');
        $client->expects($this->never())->method('getHeaders');
        $client->expects($this->never())->method('getBody');
        $lines = $headerLines ?? $this->createHeaderLines($status, $headers);
        $client->expects($this->once())->method('get')->with($url)->willReturnCallback(
            function () use (&$options, $lines, $chunks, $failure, $probe) {
                $this->simulateRequest($options, $lines, $chunks, $failure, $probe);
            }
        );
        return $client;
    }

    private function simulateRequest(
        array $options,
        array $lines,
        array $chunks,
        ?\Exception $failure,
        bool $probe
    ): void {
        $this->requestSessionStates[] = $this->sessionClosed;
        if ($probe) {
            $this->assertSame(30, $options[CURLOPT_TIMEOUT]);
            $this->assertSame(1, $options[CURLOPT_LOW_SPEED_LIMIT]);
            $this->assertSame(60, $options[CURLOPT_LOW_SPEED_TIME]);
        } else {
            $this->assertArrayNotHasKey(CURLOPT_TIMEOUT, $options);
            $this->assertArrayNotHasKey(CURLOPT_LOW_SPEED_LIMIT, $options);
            $this->assertArrayNotHasKey(CURLOPT_LOW_SPEED_TIME, $options);
        }
        $this->headerReadCount++;
        foreach ($lines as $line) {
            $this->assertSame(strlen($line), $options[CURLOPT_HEADERFUNCTION](null, $line));
        }
        foreach ($chunks as $chunk) {
            $written = $options[CURLOPT_WRITEFUNCTION](null, $chunk);
            if ($written === 0) {
                throw new \Exception('Write callback aborted.');
            }
            $this->assertSame(strlen($chunk), $written);
        }
        if ($failure !== null) {
            throw $failure;
        }
    }

    private function createHeaderLines(int $status, array $headers): array
    {
        $lines = ["HTTP/1.1 {$status} Response\r\n"];
        foreach ($headers as $name => $values) {
            foreach ((array) $values as $value) {
                $lines[] = "{$name}: {$value}\r\n";
            }
        }
        $lines[] = "\r\n";
        return $lines;
    }

    private function createStreamingClient(
        string $url,
        int $status = 200,
        array $headers = [],
        array $chunks = ['remote body'],
        ?\Exception $failure = null,
        ?array $headerLines = null
    ): Curl {
        return $this->createClient($url, $status, $headers, $chunks, $failure, false, $headerLines);
    }

    private function expectSessionClose(): void
    {
        $this->sessionManager->expects($this->once())->method('writeClose')->willReturnCallback(function () {
            $this->sessionClosed = true;
        });
    }

    public function testMetadataIsParsedOncePerResource(): void
    {
        $this->_setupUrlMocks(self::FILE_SIZE, self::URL, ['disposition' => 'inline; filename=test.txt']);
        for ($i = 0; $i < 2; $i++) {
            $this->assertSame(self::FILE_SIZE, $this->getHelper()->getFileSize());
            $this->assertSame(self::MIME_TYPE, $this->getHelper()->getContentType());
            $this->assertSame('test.txt', $this->getHelper()->getFilename());
        }
        $this->assertSame(1, $this->headerReadCount);
        $this->expectOutputString('');
    }

    public function testBodyStartedAbortPreservesParsedMetadata(): void
    {
        $this->httpClientFactory = $this->createMock(CurlFactory::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $client = $this->createClient(self::URL, 200, ['Content-Length' => '123'], ['first body chunk']);
        $this->httpClientFactory->expects($this->once())->method('create')->willReturn($client);
        $this->domainValidator->method('isValid')->willReturn(true);
        $this->logger->expects($this->never())->method('warning');
        $this->getHelper()->setResource(self::URL, DownloadHelper::LINK_TYPE_URL);
        $this->expectOutputString('');
        $this->assertSame(123, $this->getHelper()->getFileSize());
    }

    public function testEmptyBodyAndHeadersAreCached(): void
    {
        $this->httpClientFactory = $this->createMock(CurlFactory::class);

        $client = $this->createClient(self::URL, 200, [], []);
        $this->httpClientFactory->expects($this->once())->method('create')->willReturn($client);
        $this->domainValidator->method('isValid')->willReturn(true);
        $this->getHelper()->setResource(self::URL, DownloadHelper::LINK_TYPE_URL);
        $this->assertSame(0, $this->getHelper()->getFileSize());
        $this->assertSame('application/octet-stream', $this->getHelper()->getContentType());
        $this->assertSame('download', $this->getHelper()->getFilename());
        $this->assertSame(1, $this->headerReadCount);
    }

    public function testRepeatedHeadersUseTheirLastValues(): void
    {
        $this->httpClientFactory = $this->createMock(CurlFactory::class);

        $client = $this->createClient(self::URL, 200, [
            'CONTENT-LENGTH' => ['1', '456'],
            'Content-Type' => ['text/plain', self::MIME_TYPE],
            'Content-Disposition' => ['inline; filename=first.txt', 'inline; filename=last.txt']
        ]);
        $this->httpClientFactory->expects($this->once())->method('create')->willReturn($client);
        $this->domainValidator->method('isValid')->willReturn(true);
        $this->getHelper()->setResource(self::URL, DownloadHelper::LINK_TYPE_URL);
        $this->assertSame(456, $this->getHelper()->getFileSize());
        $this->assertSame(self::MIME_TYPE, $this->getHelper()->getContentType());
        $this->assertSame('last.txt', $this->getHelper()->getFilename());
    }

    public function testEarlyHintsAreDiscardedForProbeAndStream(): void
    {
        $this->sessionManager = $this->createMock(SessionManagerInterface::class);
        $this->httpClientFactory = $this->createMock(CurlFactory::class);

        $lines = [
            "HTTP/1.1 103 Early Hints\r\n",
            "Content-Length:999\r\n",
            "Content-Disposition:inline; filename=early.txt\r\n",
            "\r\n",
            "HTTP/2 200 OK\r\n",
            "Content-Length:4\r\n",
            "Content-Type: text/plain\r\n",
            "\r\n"
        ];
        $probe = $this->createClient(self::URL, headerLines: $lines);
        $stream = $this->createStreamingClient(self::URL, chunks: ['body'], headerLines: $lines);
        $this->httpClientFactory->expects($this->exactly(2))->method('create')->willReturn($probe, $stream);
        $this->domainValidator->method('isValid')->willReturn(true);
        $this->getHelper()->setResource(self::URL, DownloadHelper::LINK_TYPE_URL);
        $this->assertSame('download', $this->getHelper()->getFilename());
        $this->assertSame(4, $this->getHelper()->getFileSize());
        $this->assertSame('text/plain', $this->getHelper()->getContentType());
        $this->expectSessionClose();
        $this->expectOutputString('body');
        $this->getHelper()->output();
    }

    public function testOriginRedirectAfterConnectIsFollowed(): void
    {
        $this->httpClientFactory = $this->createMock(CurlFactory::class);
        $this->domainValidator = $this->createMock(DomainValidator::class);

        $target = self::URL . '/final';
        $lines = [
            "HTTP/1.1 200 Connection established\r\n",
            "Content-Disposition:inline; filename=proxy.txt\r\n",
            "\r\n",
            "HTTP/1.1 302 Found\r\n",
            "Location:/final\r\n",
            "\r\n"
        ];
        $probe = $this->createClient(self::URL, headerLines: $lines);
        $final = $this->createClient($target, 200, ['Content-Length' => '123']);
        $this->httpClientFactory->expects($this->exactly(2))->method('create')->willReturn($probe, $final);
        $this->domainValidator->expects($this->exactly(2))->method('isValid')->willReturnMap([
            [self::URL, true],
            [$target, true]
        ]);
        $this->getHelper()->setResource(self::URL, DownloadHelper::LINK_TYPE_URL);
        $this->assertSame('final', $this->getHelper()->getFilename());
        $this->assertSame(123, $this->getHelper()->getFileSize());
    }

    public function testHeaderWithoutSpaceAfterColonIsParsed(): void
    {
        $this->httpClientFactory = $this->createMock(CurlFactory::class);

        $lines = ["HTTP/1.1 200 OK\r\n", "Content-Length:123\r\n", "\r\n"];
        $probe = $this->createClient(self::URL, headerLines: $lines);
        $this->httpClientFactory->expects($this->once())->method('create')->willReturn($probe);
        $this->domainValidator->method('isValid')->willReturn(true);
        $this->getHelper()->setResource(self::URL, DownloadHelper::LINK_TYPE_URL);
        $this->assertSame(123, $this->getHelper()->getFileSize());
    }

    public function testWhitelistedRelativeRedirectPreservesSignedUrl(): void
    {
        $this->httpClientFactory = $this->createMock(CurlFactory::class);
        $this->domainValidator = $this->createMock(DomainValidator::class);

        $url = 'https://example.com/start?token=initial%2Ftoken';
        $location = '/%66ile%2Fname?signature=a%2Fb%2Bc%3D';
        $target = 'https://example.com' . $location;
        $probe = $this->createClient($url, 302, ['Location' => $location]);
        $final = $this->createClient($target, 200, ['Content-Length' => '123', 'Content-Type' => 'text/plain']);
        $this->httpClientFactory->expects($this->exactly(2))->method('create')->willReturn($probe, $final);
        $this->domainValidator->expects($this->exactly(2))->method('isValid')->willReturnMap([
            [$url, true],
            [$target, true]
        ]);
        $this->getHelper()->setResource($url, DownloadHelper::LINK_TYPE_URL);
        $this->assertSame('%66ile%2Fname', $this->getHelper()->getFilename());
        $this->assertSame(123, $this->getHelper()->getFileSize());
        $this->assertSame('text/plain', $this->getHelper()->getContentType());
    }

    #[DataProvider('redirectLocationProvider')]
    public function testRedirectLocationPreservesEncodingAndPort(string $url, string $location, string $target): void
    {
        $this->httpClientFactory = $this->createMock(CurlFactory::class);
        $this->domainValidator = $this->createMock(DomainValidator::class);

        $probe = $this->createClient($url, 302, ['Location' => $location]);
        $final = $this->createClient($target, 200, ['Content-Length' => '123']);
        $this->httpClientFactory->expects($this->exactly(2))->method('create')->willReturn($probe, $final);
        $this->domainValidator->expects($this->exactly(2))->method('isValid')->willReturnMap([
            [$url, true],
            [$target, true]
        ]);
        $this->getHelper()->setResource($url, DownloadHelper::LINK_TYPE_URL);
        $this->assertSame(123, $this->getHelper()->getFileSize());
    }

    public static function redirectLocationProvider(): array
    {
        return [
            'absolute location verbatim' => [
                'http://example.com/start',
                'https://cdn.example.com/%66ile%2Fpart?signature=a%2Fb%3D',
                'https://cdn.example.com/%66ile%2Fpart?signature=a%2Fb%3D'
            ],
            'relative http without explicit base port' => [
                'http://example.com/start',
                '/file%2Fpart?signature=a%2Fb',
                'http://example.com/file%2Fpart?signature=a%2Fb'
            ],
            'relative https without explicit base port' => [
                'https://example.com/start',
                '/file%2Fpart?signature=a%2Fb',
                'https://example.com/file%2Fpart?signature=a%2Fb'
            ],
            'relative with explicit non-default base port' => [
                'https://example.com:8443/start',
                '/file%2Fpart?signature=a%2Fb',
                'https://example.com:8443/file%2Fpart?signature=a%2Fb'
            ],
            'fragment-only location keeps base path and query' => [
                'https://example.com/path%2Ffile?old=%2F',
                '#f',
                'https://example.com/path%2Ffile?old=%2F#f'
            ],
            'network-path location' => [
                'https://example.com/start',
                '//cdn.example.com/x',
                'https://cdn.example.com/x'
            ],
            'parent-relative location' => [
                'http://example.com/dir/start',
                '../a?q',
                'http://example.com/a?q'
            ],
            'absolute path with escaped query' => [
                'http://example.com/start',
                '/p?x=%2F',
                'http://example.com/p?x=%2F'
            ],
            'query-only location keeps base path' => [
                'https://example.com/path%2Ffile?old=1',
                '?x=1&signature=a%2Fb',
                'https://example.com/path%2Ffile?x=1&signature=a%2Fb'
            ],
            'relative with explicit default base port' => [
                'https://example.com:443/start',
                '/file%2Fpart?signature=a%2Fb',
                'https://example.com:443/file%2Fpart?signature=a%2Fb'
            ]
        ];
    }

    #[DataProvider('emptyLocationProvider')]
    public function testEmptyRedirectLocationIsRejected(string $location): void
    {
        $this->httpClientFactory = $this->createMock(CurlFactory::class);
        $probe = $this->createClient(self::URL, 302, ['Location' => $location]);
        $this->httpClientFactory->expects($this->once())->method('create')->willReturn($probe);
        $this->domainValidator->method('isValid')->willReturn(true);
        $this->getHelper()->setResource(self::URL, DownloadHelper::LINK_TYPE_URL);
        $this->expectException(LocalizedException::class);
        $this->getHelper()->getFilename();
    }

    public static function emptyLocationProvider(): array
    {
        return [[''], ['   ']];
    }

    public function testRedirectToNonWhitelistedHostIsRejected(): void
    {
        $this->httpClientFactory = $this->createMock(CurlFactory::class);
        $this->domainValidator = $this->createMock(DomainValidator::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $target = 'https://untrusted.example/file?secret=value';
        $client = $this->createClient(self::URL, 302, ['Location' => $target]);
        $this->httpClientFactory->expects($this->once())->method('create')->willReturn($client);
        $this->domainValidator->expects($this->exactly(2))->method('isValid')->willReturnMap([
            [self::URL, true],
            [$target, false]
        ]);
        $this->logger->expects($this->once())->method('warning')->with($this->isString(), [
            'host' => 'untrusted.example',
            'message' => 'Invalid download URL.'
        ]);
        $this->getHelper()->setResource(self::URL, DownloadHelper::LINK_TYPE_URL);
        $this->expectException(LocalizedException::class);
        $this->getHelper()->getFilename();
    }

    public function testRedirectLimitIsEnforced(): void
    {
        $this->httpClientFactory = $this->createMock(CurlFactory::class);
        $this->domainValidator = $this->createMock(DomainValidator::class);

        $clients = [
            $this->createClient(self::URL, 302, ['Location' => '/one'], []),
            $this->createClient(self::URL . '/one', 302, ['Location' => '/two'], []),
            $this->createClient(self::URL . '/two', 302, ['Location' => '/three'], []),
            $this->createClient(self::URL . '/three', 302, ['Location' => '/four'], [])
        ];
        $this->httpClientFactory->expects($this->exactly(4))->method('create')->willReturn(...$clients);
        $this->domainValidator->expects($this->exactly(4))->method('isValid')->willReturn(true);
        $this->getHelper()->setResource(self::URL, DownloadHelper::LINK_TYPE_URL);
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Something went wrong while getting the requested content.');
        $this->getHelper()->getFileSize();
    }

    #[DataProvider('nonSuccessStatusProvider')]
    public function testNonSuccessResponseIsRejected(int $status): void
    {
        $this->httpClientFactory = $this->createMock(CurlFactory::class);

        $client = $this->createClient(self::URL, $status);
        $this->httpClientFactory->expects($this->once())->method('create')->willReturn($client);
        $this->domainValidator->method('isValid')->willReturn(true);
        $this->getHelper()->setResource(self::URL, DownloadHelper::LINK_TYPE_URL);
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Something went wrong while getting the requested content.');
        $this->getHelper()->getFilename();
    }

    public static function nonSuccessStatusProvider(): array
    {
        return [[204], [206], [302], [404], [500]];
    }

    public function testInitialNonWhitelistedUrlIsRejected(): void
    {
        $this->httpClientFactory = $this->createMock(CurlFactory::class);
        $this->domainValidator = $this->createMock(DomainValidator::class);

        $this->domainValidator->expects($this->once())->method('isValid')->with(self::URL)->willReturn(false);
        $this->httpClientFactory->expects($this->never())->method('create');
        $this->getHelper()->setResource(self::URL, DownloadHelper::LINK_TYPE_URL);
        $this->expectException(LocalizedException::class);
        $this->getHelper()->getFileSize();
    }

    public function testUppercaseHttpSchemeIsAllowed(): void
    {
        $this->httpClientFactory = $this->createMock(CurlFactory::class);
        $this->domainValidator = $this->createMock(DomainValidator::class);

        $url = 'HTTPS://example.com/file';
        $client = $this->createClient($url);
        $this->httpClientFactory->expects($this->once())->method('create')->willReturn($client);
        $this->domainValidator->expects($this->once())->method('isValid')->with($url)->willReturn(true);
        $this->getHelper()->setResource($url, DownloadHelper::LINK_TYPE_URL);
        $this->assertSame('file', $this->getHelper()->getFilename());
    }

    public function testNonHttpSchemeIsRejected(): void
    {
        $this->httpClientFactory = $this->createMock(CurlFactory::class);

        $this->httpClientFactory->expects($this->never())->method('create');
        $this->getHelper()->setResource('ftp://example.com/file', DownloadHelper::LINK_TYPE_URL);
        $this->expectException(LocalizedException::class);
        $this->getHelper()->getFileSize();
    }

    public function testAbortWithoutBodyIsRethrownAsLocalizedException(): void
    {
        $this->httpClientFactory = $this->createMock(CurlFactory::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $cause = new \Exception('Connection failed before body.');
        $url = self::URL . '/file?secret=value';
        $client = $this->createClient($url, 200, [], [], $cause);
        $this->httpClientFactory->expects($this->once())->method('create')->willReturn($client);
        $this->domainValidator->method('isValid')->willReturn(true);
        $this->logger->expects($this->once())->method('warning')->with($this->isString(), [
            'host' => 'example.com',
            'message' => $cause->getMessage()
        ]);
        $this->getHelper()->setResource($url, DownloadHelper::LINK_TYPE_URL);
        try {
            $this->getHelper()->getFileSize();
            $this->fail('Expected a localized transport exception.');
        } catch (LocalizedException $exception) {
            $this->assertSame('Something went wrong while getting the requested content.', $exception->getMessage());
            $this->assertSame($cause, $exception->getPrevious());
        }
    }

    public function testSettingAnotherUrlClearsMetadata(): void
    {
        $this->httpClientFactory = $this->createMock(CurlFactory::class);
        $this->domainValidator = $this->createMock(DomainValidator::class);

        $urls = [self::URL, self::URL . '/second'];
        $clients = [];
        foreach ($urls as $index => $url) {
            $clients[] = $this->createClient($url, 200, ['Content-Length' => (string) $index]);
        }
        $this->httpClientFactory->expects($this->exactly(2))->method('create')->willReturn(...$clients);
        $this->domainValidator->expects($this->exactly(2))->method('isValid')->willReturn(true);
        foreach ($urls as $index => $url) {
            $this->getHelper()->setResource($url, DownloadHelper::LINK_TYPE_URL);
            $this->assertSame($index, $this->getHelper()->getFileSize());
        }
        $this->assertSame(2, $this->headerReadCount);
    }

    public function testOutput()
    {
        $this->sessionManager = $this->createMock(SessionManagerInterface::class);
        $this->httpClientFactory = $this->createMock(CurlFactory::class);

        $probe = $this->createClient(self::URL);
        $stream = $this->createStreamingClient(self::URL, 200, [], ['first chunk', '0', 'last chunk']);
        $this->httpClientFactory->expects($this->exactly(2))->method('create')->willReturn($probe, $stream);
        $this->domainValidator->method('isValid')->willReturn(true);
        $this->getHelper()->setResource(self::URL, DownloadHelper::LINK_TYPE_URL);
        $this->getHelper()->getFilename();
        $this->expectSessionClose();
        $this->expectOutputString('first chunk0last chunk');
        $this->getHelper()->output();
        $this->assertSame([false, true], $this->requestSessionStates);
    }

    public function testDirectOutputReleasesSessionBeforeRequests(): void
    {
        $this->sessionManager = $this->createMock(SessionManagerInterface::class);
        $this->httpClientFactory = $this->createMock(CurlFactory::class);

        $probe = $this->createClient(self::URL);
        $stream = $this->createStreamingClient(self::URL, 200, [], ['body']);
        $this->httpClientFactory->expects($this->exactly(2))->method('create')->willReturn($probe, $stream);
        $this->domainValidator->method('isValid')->willReturn(true);
        $this->getHelper()->setResource(self::URL, DownloadHelper::LINK_TYPE_URL);
        $this->expectSessionClose();
        $this->expectOutputString('body');
        $this->getHelper()->output();
        $this->assertSame([true, true], $this->requestSessionStates);
    }

    #[DataProvider('streamNonSuccessProvider')]
    public function testOutputRejectsNonSuccessBody(int $status, array $chunks): void
    {
        $this->sessionManager = $this->createMock(SessionManagerInterface::class);
        $this->httpClientFactory = $this->createMock(CurlFactory::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $probe = $this->createClient(self::URL);
        $stream = $this->createStreamingClient(self::URL, $status, [], $chunks);
        $this->httpClientFactory->expects($this->exactly(2))->method('create')->willReturn($probe, $stream);
        $this->domainValidator->method('isValid')->willReturn(true);
        $this->getHelper()->setResource(self::URL, DownloadHelper::LINK_TYPE_URL);
        $this->expectSessionClose();
        $this->expectOutputString('');
        $this->logger->expects($this->once())->method('warning')->with($this->isString(), [
            'host' => 'example.com',
            'message' => 'Invalid download response.'
        ]);
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Something went wrong while getting the requested content.');
        $this->getHelper()->output();
    }

    public static function streamNonSuccessProvider(): array
    {
        return [
            'no content' => [204, []],
            'partial response' => [206, ['partial body']],
            'redirect' => [302, ['private remote error']],
            'not found' => [404, ['private remote error']],
            'server error' => [500, ['private remote error']],
            'empty error body' => [404, []]
        ];
    }

    public function testStreamingOriginErrorAfterConnectIsRejected(): void
    {
        $this->sessionManager = $this->createMock(SessionManagerInterface::class);
        $this->httpClientFactory = $this->createMock(CurlFactory::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $lines = [
            "HTTP/1.1 200 Connection established\r\n",
            "\r\n",
            "HTTP/1.1 404 Not Found\r\n",
            "Content-Length:20\r\n",
            "\r\n"
        ];
        $probe = $this->createClient(self::URL);
        $stream = $this->createStreamingClient(self::URL, chunks: ['private error'], headerLines: $lines);
        $this->httpClientFactory->expects($this->exactly(2))->method('create')->willReturn($probe, $stream);
        $this->domainValidator->method('isValid')->willReturn(true);
        $this->getHelper()->setResource(self::URL, DownloadHelper::LINK_TYPE_URL);
        $this->expectSessionClose();
        $this->expectOutputString('');
        $this->logger->expects($this->once())->method('warning')->with($this->isString(), [
            'host' => 'example.com',
            'message' => 'Invalid download response.'
        ]);
        $this->expectException(LocalizedException::class);
        $this->getHelper()->output();
    }

    #[DataProvider('completedStreamLengthProvider')]
    public function testMatchingStreamingLengthCompletes(
        ?int $metadataLength,
        ?int $streamLength,
        array $chunks,
        string $expectedOutput
    ): void {
        $this->sessionManager = $this->createMock(SessionManagerInterface::class);
        $this->httpClientFactory = $this->createMock(CurlFactory::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $metadataHeaders = $metadataLength === null ? [] : ['Content-Length' => (string) $metadataLength];
        $streamHeaders = $streamLength === null ? [] : ['Content-Length' => (string) $streamLength];
        $probe = $this->createClient(self::URL, 200, $metadataHeaders);
        $stream = $this->createStreamingClient(self::URL, 200, $streamHeaders, $chunks);
        $this->httpClientFactory->expects($this->exactly(2))->method('create')->willReturn($probe, $stream);
        $this->domainValidator->method('isValid')->willReturn(true);
        $this->getHelper()->setResource(self::URL, DownloadHelper::LINK_TYPE_URL);
        $this->expectSessionClose();
        $this->expectOutputString($expectedOutput);
        $this->logger->expects($this->never())->method('warning');
        $this->getHelper()->output();
    }

    public static function completedStreamLengthProvider(): array
    {
        return [
            'complete body' => [4, 4, ['bo', 'dy'], 'body'],
            'empty body' => [0, 0, [], ''],
            'metadata-only length' => [4, null, ['bo', 'dy'], 'body'],
            'streaming-only length' => [null, 4, ['bo', 'dy'], 'body'],
            'unknown length' => [null, null, ['bo', 'dy'], 'body']
        ];
    }

    #[DataProvider('streamLengthFailureProvider')]
    public function testInconsistentStreamingLengthIsRejected(
        ?int $metadataLength,
        ?int $streamLength,
        array $chunks,
        string $expectedOutput
    ): void {
        $this->sessionManager = $this->createMock(SessionManagerInterface::class);
        $this->httpClientFactory = $this->createMock(CurlFactory::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $metadataHeaders = $metadataLength === null ? [] : ['Content-Length' => (string) $metadataLength];
        $streamHeaders = $streamLength === null ? [] : ['Content-Length' => (string) $streamLength];
        $probe = $this->createClient(self::URL, 200, $metadataHeaders);
        $stream = $this->createStreamingClient(self::URL, 200, $streamHeaders, $chunks);
        $this->httpClientFactory->expects($this->exactly(2))->method('create')->willReturn($probe, $stream);
        $this->domainValidator->method('isValid')->willReturn(true);
        $this->getHelper()->setResource(self::URL, DownloadHelper::LINK_TYPE_URL);
        $this->expectSessionClose();
        $this->expectOutputString($expectedOutput);
        $this->logger->expects($this->once())->method('warning')->with($this->isString(), [
            'host' => 'example.com',
            'message' => 'Download size changed.'
        ]);
        $this->expectException(LocalizedException::class);
        $this->getHelper()->output();
    }

    public static function streamLengthFailureProvider(): array
    {
        return [
            'changed header before first chunk' => [6, 5, ['first'], ''],
            'overflowing chunk withheld' => [5, 5, ['abc', 'def'], 'abc'],
            'truncated body' => [5, 5, ['abc'], 'abc'],
            'empty truncated body' => [5, 5, [], ''],
            'metadata-only length caps output' => [3, null, ['1234'], ''],
            'metadata-only truncated body' => [5, null, ['abc'], 'abc'],
            'streaming-only length caps output' => [null, 3, ['1234'], ''],
            'streaming-only truncated body' => [null, 5, ['abc'], 'abc'],
            'zero-length response rejects body' => [0, 0, ['x'], '']
        ];
    }

    public function testOutputStopsWhenCustomerDisconnects(): void
    {
        $this->sessionManager = $this->createMock(SessionManagerInterface::class);
        $this->httpClientFactory = $this->createMock(CurlFactory::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $probe = $this->createClient(self::URL);
        $stream = $this->createStreamingClient(self::URL, 200, [], ['body']);
        $this->httpClientFactory->expects($this->exactly(2))->method('create')->willReturn($probe, $stream);
        $this->domainValidator->method('isValid')->willReturn(true);
        $this->getHelper()->setResource(self::URL, DownloadHelper::LINK_TYPE_URL);
        self::$connectionAborted = 1;
        $this->expectSessionClose();
        $this->expectOutputString('');
        $this->logger->expects($this->never())->method('warning');
        $this->getHelper()->output();
    }

    #[DataProvider('transportFailureProvider')]
    public function testOutputThrowsTransportFailureAfterPartialBody(array $headers, string $message, int $code): void
    {
        $this->sessionManager = $this->createMock(SessionManagerInterface::class);
        $this->httpClientFactory = $this->createMock(CurlFactory::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $cause = new \Exception($message, $code);
        $probe = $this->createClient(self::URL);
        $stream = $this->createStreamingClient(self::URL, 200, $headers, ['partial body'], $cause);
        $this->httpClientFactory->expects($this->exactly(2))->method('create')->willReturn($probe, $stream);
        $this->domainValidator->method('isValid')->willReturn(true);
        $this->getHelper()->setResource(self::URL, DownloadHelper::LINK_TYPE_URL);
        $this->expectSessionClose();
        $this->expectOutputString('partial body');
        $this->logger->expects($this->once())->method('warning')->with($this->isString(), [
            'host' => 'example.com',
            'message' => $cause->getMessage()
        ]);
        try {
            $this->getHelper()->output();
            $this->fail('Expected an incomplete transfer to throw.');
        } catch (LocalizedException $exception) {
            $this->assertSame($cause, $exception->getPrevious());
        }
    }

    public static function transportFailureProvider(): array
    {
        return [
            'transport failure' => [[], 'Connection lost.', 0],
            'truncated chunked transfer' => [
                ['Transfer-Encoding' => 'chunked'],
                'transfer closed with outstanding read data remaining',
                18
            ]
        ];
    }

    public function testFileOutputPreservesLooseTruthiness(): void
    {
        $this->_handleMock = $this->createMock(FileReadInterface::class);
        $this->sessionManager = $this->createMock(SessionManagerInterface::class);

        $this->_setupFileMocks();
        $this->_handleMock->expects($this->exactly(2))->method('read')->with(1024)->willReturn('file body', '0');
        $this->sessionManager->expects($this->once())->method('writeClose');
        $this->expectOutputString('file body');
        $this->getHelper()->output();
    }
}
