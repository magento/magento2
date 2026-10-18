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

    /** @var DownloadHelper */
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

        $this->_filesystemMock = $this->createMock(Filesystem::class);
        $this->_handleMock = $this->createMock(\Magento\Framework\Filesystem\File\ReadInterface::class);
        $this->_workingDirectoryMock = $this->createMock(DirReadInterface::class);
        $this->_downloadableFileMock = $this->createMock(DownloadableFile::class);
        $this->sessionManager = $this->createMock(SessionManagerInterface::class);
        $this->fileReadFactory = $this->createMock(ReadFactory::class);
        $this->mime = $this->createMock(Mime::class);
        $this->httpClientFactory = $this->createMock(CurlFactory::class);
        $this->domainValidator = $this->createMock(DomainValidator::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $context = $this->createMock(Context::class);
        $context->method('getLogger')->willReturn($this->logger);

        $this->_helper = (new ObjectManager($this))->getObject(
            DownloadHelper::class,
            [
                'context' => $context,
                'downloadableFile' => $this->_downloadableFileMock,
                'filesystem'       => $this->_filesystemMock,
                'session'          => $this->sessionManager,
                'fileReadFactory'  => $this->fileReadFactory,
                'mime' => $this->mime,
                'httpClientFactory' => $this->httpClientFactory,
                'domainValidator' => $this->domainValidator
            ]
        );
    }

    protected function tearDown(): void
    {
        self::$connectionAborted = 0;
        parent::tearDown();
    }

    public function testSetResourceInvalidPath()
    {
        $this->expectException('InvalidArgumentException');
        $this->_helper->setResource('/some/path/../file', DownloadHelper::LINK_TYPE_FILE);
    }

    public function testGetFileSizeNoResource()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('Please set resource file and link type.');
        $this->_helper->getFileSize();
    }

    public function testGetFileSizeInvalidLinkType()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('Invalid download link type.');
        $this->_helper->setResource(self::FILE_PATH, 'The link type is invalid. Verify and try again.');
        $this->_helper->getFileSize();
    }

    public function testGetFileSizeUrl()
    {
        $this->_setupUrlMocks();
        $this->assertEquals(self::FILE_SIZE, $this->_helper->getFileSize());
    }

    public function testGetFileSize()
    {
        $this->_setupFileMocks();
        $this->assertEquals(self::FILE_SIZE, $this->_helper->getFileSize());
    }

    public function testGetFileSizeNoFile()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('Invalid download link type.');
        $this->_setupFileMocks(false);
        $this->_helper->getFileSize();
    }

    public function testGetContentType()
    {
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
        $this->assertEquals(self::MIME_TYPE, $this->_helper->getContentType());
    }

    #[DataProvider('dataProviderForTestGetContentTypeThroughHelper')]
    public function testGetContentTypeThroughHelper($functionExistsResult, $mimeContentTypeResult)
    {
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

        $this->assertEquals(self::MIME_TYPE, $this->_helper->getContentType());
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
        $this->assertEquals(self::MIME_TYPE, $this->_helper->getContentType());
    }

    public function testGetFilename()
    {
        $baseName = 'base_name.file';
        $path = TESTS_TEMP_DIR . '/' . $baseName;
        $this->_setupFileMocks(true, self::FILE_SIZE, $path);
        $this->assertEquals($baseName, $this->_helper->getFilename());
    }

    public function testGetFileNameUrl()
    {
        $this->_setupUrlMocks();
        $this->assertEquals('example.com', $this->_helper->getFilename());
    }

    public function testGetFileNameUrlWithContentDisposition()
    {
        $fileName = 'some_other.file';
        $this->_setupUrlMocks(self::FILE_SIZE, self::URL, ['disposition' => "inline; filename={$fileName}"]);
        $this->assertEquals($fileName, $this->_helper->getFilename());
    }

    /**
     * @param bool $doesExist
     * @param int $size
     * @param string $path
     */
    protected function _setupFileMocks($doesExist = true, $size = self::FILE_SIZE, $path = self::FILE_PATH)
    {
        $this->_handleMock->method('stat')->willReturn(['size' => $size]);
        $this->_downloadableFileMock->expects($this->any())->method('ensureFileInFilesystem')->with($path)
            ->willReturn($doesExist);
        $this->_workingDirectoryMock->expects($doesExist ? $this->once() : $this->never())->method('openFile')
            ->willReturn($this->_handleMock);
        $this->_filesystemMock->expects($this->any())->method('getDirectoryRead')->with(DirectoryList::MEDIA)
            ->willReturn($this->_workingDirectoryMock);
        $this->_helper->setResource($path, DownloadHelper::LINK_TYPE_FILE);
    }

    /**
     * @param int $size
     * @param string $url
     * @param array $additionalStatData
     */
    protected function _setupUrlMocks($size = self::FILE_SIZE, $url = self::URL, $additionalStatData = [])
    {
        $headers = ['Content-Length' => (string) $size, 'Content-Type' => self::MIME_TYPE];
        if (isset($additionalStatData['disposition'])) {
            $headers['Content-Disposition'] = $additionalStatData['disposition'];
        }
        $client = $this->createClient($url, 200, $headers);
        $this->httpClientFactory->expects($this->once())->method('create')->willReturn($client);
        $this->domainValidator->expects($this->once())->method('isValid')->with($url)->willReturn(true);
        $this->fileReadFactory->expects($this->never())->method('create');
        $this->_helper->setResource($url, DownloadHelper::LINK_TYPE_URL);
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
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_LOW_SPEED_LIMIT => 1,
            CURLOPT_LOW_SPEED_TIME => 60
        ])->willReturnCallback(static function (array $values) use (&$options) {
            $options = $values;
        });
        $client->expects($this->exactly($probe ? 3 : 2))->method('setOption')
            ->willReturnCallback(function ($key, $value) use (&$options) {
                $this->assertArrayNotHasKey($key, $options);
                if ($key === CURLOPT_TIMEOUT) {
                    $this->assertSame(30, $value);
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
        } else {
            $this->assertArrayNotHasKey(CURLOPT_TIMEOUT, $options);
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
            $this->assertSame(self::FILE_SIZE, $this->_helper->getFileSize());
            $this->assertSame(self::MIME_TYPE, $this->_helper->getContentType());
            $this->assertSame('test.txt', $this->_helper->getFilename());
        }
        $this->assertSame(1, $this->headerReadCount);
        $this->expectOutputString('');
    }

    public function testBodyStartedAbortPreservesParsedMetadata(): void
    {
        $client = $this->createClient(self::URL, 200, ['Content-Length' => '123'], ['first body chunk']);
        $this->httpClientFactory->expects($this->once())->method('create')->willReturn($client);
        $this->domainValidator->method('isValid')->willReturn(true);
        $this->logger->expects($this->never())->method('warning');
        $this->_helper->setResource(self::URL, DownloadHelper::LINK_TYPE_URL);
        $this->expectOutputString('');
        $this->assertSame(123, $this->_helper->getFileSize());
    }

    public function testEmptyBodyAndHeadersAreCached(): void
    {
        $client = $this->createClient(self::URL, 204, [], []);
        $this->httpClientFactory->expects($this->once())->method('create')->willReturn($client);
        $this->domainValidator->method('isValid')->willReturn(true);
        $this->_helper->setResource(self::URL, DownloadHelper::LINK_TYPE_URL);
        $this->assertSame(0, $this->_helper->getFileSize());
        $this->assertSame('application/octet-stream', $this->_helper->getContentType());
        $this->assertSame('example.com', $this->_helper->getFilename());
        $this->assertSame(1, $this->headerReadCount);
    }

    public function testRepeatedHeadersUseTheirLastValues(): void
    {
        $client = $this->createClient(self::URL, 200, [
            'CONTENT-LENGTH' => ['1', '456'],
            'Content-Type' => ['text/plain', self::MIME_TYPE],
            'Content-Disposition' => ['inline; filename=first.txt', 'inline; filename=last.txt']
        ]);
        $this->httpClientFactory->expects($this->once())->method('create')->willReturn($client);
        $this->domainValidator->method('isValid')->willReturn(true);
        $this->_helper->setResource(self::URL, DownloadHelper::LINK_TYPE_URL);
        $this->assertSame(456, $this->_helper->getFileSize());
        $this->assertSame(self::MIME_TYPE, $this->_helper->getContentType());
        $this->assertSame('last.txt', $this->_helper->getFilename());
    }

    public function testEarlyHintsAreDiscardedForProbeAndStream(): void
    {
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
        $this->_helper->setResource(self::URL, DownloadHelper::LINK_TYPE_URL);
        $this->assertSame('example.com', $this->_helper->getFilename());
        $this->assertSame(4, $this->_helper->getFileSize());
        $this->assertSame('text/plain', $this->_helper->getContentType());
        $this->expectSessionClose();
        $this->expectOutputString('body');
        $this->_helper->output();
    }

    public function testOriginRedirectAfterConnectIsFollowed(): void
    {
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
        $this->_helper->setResource(self::URL, DownloadHelper::LINK_TYPE_URL);
        $this->assertSame('final', $this->_helper->getFilename());
        $this->assertSame(123, $this->_helper->getFileSize());
    }

    public function testHeaderWithoutSpaceAfterColonIsParsed(): void
    {
        $lines = ["HTTP/1.1 200 OK\r\n", "Content-Length:123\r\n", "\r\n"];
        $probe = $this->createClient(self::URL, headerLines: $lines);
        $this->httpClientFactory->expects($this->once())->method('create')->willReturn($probe);
        $this->domainValidator->method('isValid')->willReturn(true);
        $this->_helper->setResource(self::URL, DownloadHelper::LINK_TYPE_URL);
        $this->assertSame(123, $this->_helper->getFileSize());
    }

    public function testWhitelistedRelativeRedirectPreservesSignedUrl(): void
    {
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
        $this->_helper->setResource($url, DownloadHelper::LINK_TYPE_URL);
        $this->assertSame('%66ile%2Fname?signature=a%2Fb%2Bc%3D', $this->_helper->getFilename());
        $this->assertSame(123, $this->_helper->getFileSize());
        $this->assertSame('text/plain', $this->_helper->getContentType());
    }

    #[DataProvider('redirectLocationProvider')]
    public function testRedirectLocationPreservesEncodingAndPort(string $url, string $location, string $target): void
    {
        $probe = $this->createClient($url, 302, ['Location' => $location]);
        $final = $this->createClient($target, 200, ['Content-Length' => '123']);
        $this->httpClientFactory->expects($this->exactly(2))->method('create')->willReturn($probe, $final);
        $this->domainValidator->expects($this->exactly(2))->method('isValid')->willReturnMap([
            [$url, true],
            [$target, true]
        ]);
        $this->_helper->setResource($url, DownloadHelper::LINK_TYPE_URL);
        $this->assertSame(123, $this->_helper->getFileSize());
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

    public function testRedirectToNonWhitelistedHostIsRejected(): void
    {
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
        $this->_helper->setResource(self::URL, DownloadHelper::LINK_TYPE_URL);
        $this->expectException(LocalizedException::class);
        $this->_helper->getFilename();
    }

    public function testRedirectLimitIsEnforced(): void
    {
        $clients = [
            $this->createClient(self::URL, 302, ['Location' => '/one'], []),
            $this->createClient(self::URL . '/one', 302, ['Location' => '/two'], []),
            $this->createClient(self::URL . '/two', 302, ['Location' => '/three'], []),
            $this->createClient(self::URL . '/three', 302, ['Location' => '/four'], [])
        ];
        $this->httpClientFactory->expects($this->exactly(4))->method('create')->willReturn(...$clients);
        $this->domainValidator->expects($this->exactly(4))->method('isValid')->willReturn(true);
        $this->_helper->setResource(self::URL, DownloadHelper::LINK_TYPE_URL);
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Something went wrong while getting the requested content.');
        $this->_helper->getFileSize();
    }

    #[DataProvider('nonSuccessStatusProvider')]
    public function testNonSuccessResponseIsRejected(int $status): void
    {
        $client = $this->createClient(self::URL, $status);
        $this->httpClientFactory->expects($this->once())->method('create')->willReturn($client);
        $this->domainValidator->method('isValid')->willReturn(true);
        $this->_helper->setResource(self::URL, DownloadHelper::LINK_TYPE_URL);
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Something went wrong while getting the requested content.');
        $this->_helper->getFilename();
    }

    public static function nonSuccessStatusProvider(): array
    {
        return [[302], [404], [500]];
    }

    public function testInitialNonWhitelistedUrlIsRejected(): void
    {
        $this->domainValidator->expects($this->once())->method('isValid')->with(self::URL)->willReturn(false);
        $this->httpClientFactory->expects($this->never())->method('create');
        $this->_helper->setResource(self::URL, DownloadHelper::LINK_TYPE_URL);
        $this->expectException(LocalizedException::class);
        $this->_helper->getFileSize();
    }

    public function testUppercaseHttpSchemeIsAllowed(): void
    {
        $url = 'HTTPS://example.com/file';
        $client = $this->createClient($url);
        $this->httpClientFactory->expects($this->once())->method('create')->willReturn($client);
        $this->domainValidator->expects($this->once())->method('isValid')->with($url)->willReturn(true);
        $this->_helper->setResource($url, DownloadHelper::LINK_TYPE_URL);
        $this->assertSame('file', $this->_helper->getFilename());
    }

    public function testNonHttpSchemeIsRejected(): void
    {
        $this->httpClientFactory->expects($this->never())->method('create');
        $this->_helper->setResource('ftp://example.com/file', DownloadHelper::LINK_TYPE_URL);
        $this->expectException(LocalizedException::class);
        $this->_helper->getFileSize();
    }

    public function testAbortWithoutBodyIsRethrownAsLocalizedException(): void
    {
        $cause = new \Exception('Connection failed before body.');
        $url = self::URL . '/file?secret=value';
        $client = $this->createClient($url, 200, [], [], $cause);
        $this->httpClientFactory->expects($this->once())->method('create')->willReturn($client);
        $this->domainValidator->method('isValid')->willReturn(true);
        $this->logger->expects($this->once())->method('warning')->with($this->isString(), [
            'host' => 'example.com',
            'message' => $cause->getMessage()
        ]);
        $this->_helper->setResource($url, DownloadHelper::LINK_TYPE_URL);
        try {
            $this->_helper->getFileSize();
            $this->fail('Expected a localized transport exception.');
        } catch (LocalizedException $exception) {
            $this->assertSame('Something went wrong while getting the requested content.', $exception->getMessage());
            $this->assertSame($cause, $exception->getPrevious());
        }
    }

    public function testSettingAnotherUrlClearsMetadata(): void
    {
        $urls = [self::URL, self::URL . '/second'];
        $clients = [];
        foreach ($urls as $index => $url) {
            $clients[] = $this->createClient($url, 200, ['Content-Length' => (string) $index]);
        }
        $this->httpClientFactory->expects($this->exactly(2))->method('create')->willReturn(...$clients);
        $this->domainValidator->expects($this->exactly(2))->method('isValid')->willReturn(true);
        foreach ($urls as $index => $url) {
            $this->_helper->setResource($url, DownloadHelper::LINK_TYPE_URL);
            $this->assertSame($index, $this->_helper->getFileSize());
        }
        $this->assertSame(2, $this->headerReadCount);
    }

    public function testOutput()
    {
        $probe = $this->createClient(self::URL);
        $stream = $this->createStreamingClient(self::URL, 200, [], ['first chunk', '0', 'last chunk']);
        $this->httpClientFactory->expects($this->exactly(2))->method('create')->willReturn($probe, $stream);
        $this->domainValidator->method('isValid')->willReturn(true);
        $this->_helper->setResource(self::URL, DownloadHelper::LINK_TYPE_URL);
        $this->_helper->getFilename();
        $this->expectSessionClose();
        $this->expectOutputString('first chunk0last chunk');
        $this->_helper->output();
        $this->assertSame([false, true], $this->requestSessionStates);
    }

    public function testDirectOutputReleasesSessionBeforeRequests(): void
    {
        $probe = $this->createClient(self::URL);
        $stream = $this->createStreamingClient(self::URL, 200, [], ['body']);
        $this->httpClientFactory->expects($this->exactly(2))->method('create')->willReturn($probe, $stream);
        $this->domainValidator->method('isValid')->willReturn(true);
        $this->_helper->setResource(self::URL, DownloadHelper::LINK_TYPE_URL);
        $this->expectSessionClose();
        $this->expectOutputString('body');
        $this->_helper->output();
        $this->assertSame([true, true], $this->requestSessionStates);
    }

    #[DataProvider('streamNonSuccessProvider')]
    public function testOutputRejectsNonSuccessBody(int $status, array $chunks): void
    {
        $probe = $this->createClient(self::URL);
        $stream = $this->createStreamingClient(self::URL, $status, [], $chunks);
        $this->httpClientFactory->expects($this->exactly(2))->method('create')->willReturn($probe, $stream);
        $this->domainValidator->method('isValid')->willReturn(true);
        $this->_helper->setResource(self::URL, DownloadHelper::LINK_TYPE_URL);
        $this->expectSessionClose();
        $this->expectOutputString('');
        $this->logger->expects($this->once())->method('warning')->with($this->isString(), [
            'host' => 'example.com',
            'message' => 'Invalid download response.'
        ]);
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Something went wrong while getting the requested content.');
        $this->_helper->output();
    }

    public static function streamNonSuccessProvider(): array
    {
        return [
            'redirect' => [302, ['private remote error']],
            'not found' => [404, ['private remote error']],
            'server error' => [500, ['private remote error']],
            'empty error body' => [404, []]
        ];
    }

    public function testStreamingOriginErrorAfterConnectIsRejected(): void
    {
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
        $this->_helper->setResource(self::URL, DownloadHelper::LINK_TYPE_URL);
        $this->expectSessionClose();
        $this->expectOutputString('');
        $this->logger->expects($this->once())->method('warning')->with($this->isString(), [
            'host' => 'example.com',
            'message' => 'Invalid download response.'
        ]);
        $this->expectException(LocalizedException::class);
        $this->_helper->output();
    }

    #[DataProvider('completedStreamLengthProvider')]
    public function testMatchingStreamingLengthCompletes(int $length, array $chunks, string $expectedOutput): void
    {
        $headers = ['Content-Length' => (string) $length];
        $probe = $this->createClient(self::URL, 200, $headers);
        $stream = $this->createStreamingClient(self::URL, 200, $headers, $chunks);
        $this->httpClientFactory->expects($this->exactly(2))->method('create')->willReturn($probe, $stream);
        $this->domainValidator->method('isValid')->willReturn(true);
        $this->_helper->setResource(self::URL, DownloadHelper::LINK_TYPE_URL);
        $this->expectSessionClose();
        $this->expectOutputString($expectedOutput);
        $this->logger->expects($this->never())->method('warning');
        $this->_helper->output();
    }

    public static function completedStreamLengthProvider(): array
    {
        return [
            'complete body' => [4, ['bo', 'dy'], 'body'],
            'empty body' => [0, [], '']
        ];
    }

    #[DataProvider('streamLengthFailureProvider')]
    public function testInconsistentStreamingLengthIsRejected(
        ?int $metadataLength,
        ?int $streamLength,
        array $chunks,
        string $expectedOutput
    ): void {
        $metadataHeaders = $metadataLength === null ? [] : ['Content-Length' => (string) $metadataLength];
        $streamHeaders = $streamLength === null ? [] : ['Content-Length' => (string) $streamLength];
        $probe = $this->createClient(self::URL, 200, $metadataHeaders);
        $stream = $this->createStreamingClient(self::URL, 200, $streamHeaders, $chunks);
        $this->httpClientFactory->expects($this->exactly(2))->method('create')->willReturn($probe, $stream);
        $this->domainValidator->method('isValid')->willReturn(true);
        $this->_helper->setResource(self::URL, DownloadHelper::LINK_TYPE_URL);
        $this->expectSessionClose();
        $this->expectOutputString($expectedOutput);
        $this->logger->expects($this->once())->method('warning')->with($this->isString(), [
            'host' => 'example.com',
            'message' => 'Download size changed.'
        ]);
        $this->expectException(LocalizedException::class);
        $this->_helper->output();
    }

    public static function streamLengthFailureProvider(): array
    {
        return [
            'changed header before first chunk' => [6, 5, ['first'], ''],
            'missing streaming length' => [5, null, ['first'], ''],
            'overflowing chunk withheld' => [5, 5, ['abc', 'def'], 'abc'],
            'truncated body' => [5, 5, ['abc'], 'abc'],
            'empty truncated body' => [5, 5, [], ''],
            'streaming-only length caps output' => [null, 3, ['1234'], ''],
            'streaming-only truncated body' => [null, 5, ['abc'], 'abc'],
            'zero-length response rejects body' => [0, 0, ['x'], '']
        ];
    }

    public function testOutputStopsWhenCustomerDisconnects(): void
    {
        $probe = $this->createClient(self::URL);
        $stream = $this->createStreamingClient(self::URL, 200, [], ['body']);
        $this->httpClientFactory->expects($this->exactly(2))->method('create')->willReturn($probe, $stream);
        $this->domainValidator->method('isValid')->willReturn(true);
        $this->_helper->setResource(self::URL, DownloadHelper::LINK_TYPE_URL);
        self::$connectionAborted = 1;
        $this->expectSessionClose();
        $this->expectOutputString('');
        $this->logger->expects($this->never())->method('warning');
        $this->_helper->output();
    }

    public function testOutputThrowsTransportFailureAfterPartialBody(): void
    {
        $cause = new \Exception('Connection lost.');
        $probe = $this->createClient(self::URL);
        $stream = $this->createStreamingClient(self::URL, 200, [], ['partial body'], $cause);
        $this->httpClientFactory->expects($this->exactly(2))->method('create')->willReturn($probe, $stream);
        $this->domainValidator->method('isValid')->willReturn(true);
        $this->_helper->setResource(self::URL, DownloadHelper::LINK_TYPE_URL);
        $this->expectSessionClose();
        $this->expectOutputString('partial body');
        $this->logger->expects($this->once())->method('warning')->with($this->isString(), [
            'host' => 'example.com',
            'message' => $cause->getMessage()
        ]);
        try {
            $this->_helper->output();
            $this->fail('Expected an incomplete transfer to throw.');
        } catch (LocalizedException $exception) {
            $this->assertSame($cause, $exception->getPrevious());
        }
    }

    public function testFileOutputPreservesLooseTruthiness(): void
    {
        $this->_setupFileMocks();
        $this->_handleMock->expects($this->exactly(2))->method('read')->with(1024)->willReturn('file body', '0');
        $this->sessionManager->expects($this->once())->method('writeClose');
        $this->expectOutputString('file body');
        $this->_helper->output();
    }
}
