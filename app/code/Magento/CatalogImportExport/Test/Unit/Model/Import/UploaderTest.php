<?php declare(strict_types=1);

/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\CatalogImportExport\Test\Unit\Model\Import;

use PHPUnit\Framework\Attributes\DataProvider;
use Magento\CatalogImportExport\Model\Import\Uploader;
use Magento\CatalogImportExport\Model\Import\Product\RowValidatorInterface;
use Magento\Downloadable\Model\Url\DomainValidator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\TargetDirectory;
use Magento\Framework\Filesystem\Directory\Write;
use Magento\Framework\Filesystem\File\ReadFactory;
use Magento\Framework\HTTP\ClientFactory;
use Magento\Framework\HTTP\ClientInterface;
use Magento\Framework\Image\AdapterFactory;
use Magento\Framework\Math\Random;
use Magento\MediaStorage\Helper\File\Storage;
use Magento\MediaStorage\Helper\File\Storage\Database;
use Magento\MediaStorage\Model\File\Validator\NotProtectedExtension;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UploaderTest extends TestCase
{
    /**
     * @var Database|MockObject
     */
    protected $coreFileStorageDb;

    /**
     * @var Storage|MockObject
     */
    protected $coreFileStorage;

    /**
     * @var AdapterFactory|MockObject
     */
    protected $imageFactory;

    /**
     * @var NotProtectedExtension|MockObject
     */
    protected $validator;

    /**
     * @var Filesystem|MockObject
     */
    protected $filesystem;

    /**
     * @var ReadFactory|MockObject
     */
    protected $readFactory;

    /**
     * @var WriteInterface|MockObject
     */
    protected $directoryMock;

    /**
     * @var Random|MockObject
     */
    private $random;

    /**
     * @var Uploader|MockObject
     */
    protected $uploader;

    /**
     * @var TargetDirectory|MockObject
     */
    private $targetDirectory;

    /**
     * @var DomainValidator|MockObject
     */
    private $domainValidator;

    /**
     * @var ClientFactory|MockObject
     */
    private $httpClientFactory;

    /**
     * @var ClientInterface|MockObject
     */
    private $httpClient;

    protected function setUp(): void
    {
        $this->coreFileStorageDb = $this->createMock(Database::class);

        $this->coreFileStorage = $this->createMock(Storage::class);

        $this->imageFactory = $this->createMock(AdapterFactory::class);

        $this->validator = $this->createMock(NotProtectedExtension::class);

        $this->readFactory = $this->createPartialMock(ReadFactory::class, ['create']);
        $this->readFactory->expects($this->never())->method('create');
        $this->httpClientFactory = $this->createMock(ClientFactory::class);
        $this->httpClient = $this->createMock(ClientInterface::class);

        $this->directoryMock = $this->createPartialMock(
            Write::class,
            ['writeFile', 'getRelativePath', 'isWritable', 'getAbsolutePath']
        );

        $this->filesystem = $this->createPartialMock(Filesystem::class, ['getDirectoryWrite']);
        $this->filesystem->method('getDirectoryWrite')->willReturn($this->directoryMock);

        $this->random = $this->createPartialMock(Random::class, ['getRandomString']);

        $this->targetDirectory = $this->createPartialMock(
            TargetDirectory::class,
            ['getDirectoryWrite', 'getDirectoryRead']
        );
        $this->targetDirectory->method('getDirectoryWrite')->willReturn($this->directoryMock);
        $this->targetDirectory->method('getDirectoryRead')->willReturn($this->directoryMock);

        $this->domainValidator = $this->getMockBuilder(DomainValidator::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isValid'])
            ->getMock();

        $this->uploader = $this->getMockBuilder(Uploader::class)
            ->setConstructorArgs(
                [
                    $this->coreFileStorageDb,
                    $this->coreFileStorage,
                    $this->imageFactory,
                    $this->validator,
                    $this->filesystem,
                    $this->readFactory,
                    null,
                    $this->random,
                    $this->targetDirectory,
                    $this->domainValidator,
                    $this->httpClientFactory
                ]
            )
            ->onlyMethods(['_setUploadFile', 'save', 'getTmpDir', 'checkAllowedExtension'])
            ->getMock();
    }

    /**
     * @param $fileUrl
     * @param $expectedFileName
     * @param $checkAllowedExtension
     * @param int $status
     * @throws LocalizedException
     */
    #[DataProvider('moveFileUrlDataProvider')]
    public function testMoveFileUrl($fileUrl, $expectedFileName, $checkAllowedExtension, int $status = 200)
    {
        $tmpDir = 'var/tmp';
        $destDir = 'var/dest/dir';
        $this->uploader->method('getTmpDir')->willReturn($tmpDir);

        // Mock domain validator to allow the URL
        $this->domainValidator->expects($this->once())
            ->method('isValid')
            ->with($fileUrl)
            ->willReturn(true);

        // Expected invocation to validate file extension
        $this->uploader->expects($this->exactly($checkAllowedExtension))->method('checkAllowedExtension')
            ->willReturn(true);

        // Expected invocation to generate random string for file name postfix
        $this->random->expects($this->once())->method('getRandomString')
            ->with(16)
            ->willReturn('38GcEmPFKXXR8NMj');

        // Expected invocation to build the temp file path with the correct directory and filename
        $this->directoryMock->expects($this->any())->method('getRelativePath')
            ->with($tmpDir . '/' . $expectedFileName)
            ->willReturn($tmpDir . '/' . $expectedFileName);

        $this->httpClientFactory->expects($this->once())->method('create')->willReturn($this->httpClient);
        $this->httpClient->expects($this->once())->method('setTimeout')->with(0);
        $this->httpClient->expects($this->once())->method('setOptions')->with([
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_LOW_SPEED_LIMIT => 1,
            CURLOPT_LOW_SPEED_TIME => 60
        ]);
        $this->httpClient->expects($this->once())->method('get')->with($fileUrl);
        $this->httpClient->expects($this->once())->method('getStatus')->willReturn($status);
        $this->httpClient->expects($this->once())->method('getBody')->willReturn('image contents');
        $this->directoryMock->expects($this->once())->method('writeFile')
            ->with($tmpDir . '/' . $expectedFileName, 'image contents')
            ->willReturn($expectedFileName);

        // Expected invocations save the downloaded file to temp file
        // and move the temp file to the destination directory
        $this->directoryMock->expects($this->exactly(2))
            ->method('isWritable')
            ->willReturnCallback(fn($param) => match ([$param]) {
                [$destDir] => true,
                [$tmpDir] => true
            });

        $this->directoryMock->expects($this->once())->method('getAbsolutePath')
            ->with($destDir)
            ->willReturn($destDir . '/' . $expectedFileName);
        $this->uploader->expects($this->once())->method('_setUploadFile')
            ->willReturnSelf();

        $returnFile = $destDir . DIRECTORY_SEPARATOR . $expectedFileName;

        $this->uploader->expects($this->once())->method('save')
            ->with($destDir . '/' . $expectedFileName)
            ->willReturn([
                'name' => $expectedFileName,
                'path' => 'absPath',
                'file' => $returnFile
            ]);

        $this->uploader->setDestDir($destDir);
        $result = $this->uploader->move($fileUrl);

        $this->assertEquals(['name' => $expectedFileName, 'file' => $returnFile], $result);
        $this->assertArrayNotHasKey('path', $result);
    }

    public function testMoveFileName()
    {
        $destDir = 'var/dest/dir';
        $fileName = 'test_uploader_file';
        $expectedRelativeFilePath = $fileName;
        $this->directoryMock->expects($this->once())->method('isWritable')->with($destDir)->willReturn(true);
        $this->directoryMock->expects($this->any())->method('getRelativePath')->with($expectedRelativeFilePath);
        $this->directoryMock->expects($this->once())->method('getAbsolutePath')->with($destDir)
            ->willReturn($destDir . '/' . $fileName);
        //Check invoking of getTmpDir(), _setUploadFile(), save() methods.
        $this->uploader->expects($this->once())->method('getTmpDir')->willReturn('');
        $this->uploader->expects($this->once())->method('_setUploadFile')->willReturnSelf();

        $returnFile = $destDir . DIRECTORY_SEPARATOR . $fileName;

        $this->uploader->expects($this->once())->method('save')->with($destDir . '/' . $fileName)
            ->willReturn(['name' => $fileName, 'file' => $returnFile]);

        $this->uploader->setDestDir($destDir);
        $this->assertEquals(['name' => $fileName, 'file' => $returnFile], $this->uploader->move($fileName));
    }

    public function testFilenameLength()
    {
        $destDir = 'var/tmp/' . str_repeat('testFilenameLength', 13); // 242 characters

        $fileName = \uniqid(); // 13 characters

        $this->directoryMock->expects($this->once())
            ->method('isWritable')
            ->with($destDir)
            ->willReturn(true);

        $this->directoryMock->expects($this->once())
            ->method('getRelativePath')
            ->with($fileName)
            ->willReturn(null);

        $this->directoryMock->expects($this->once())
            ->method('getAbsolutePath')
            ->with($destDir)
            ->willReturn($destDir);

        $this->uploader->expects($this->once())
            ->method('save')
            ->with($destDir)
            ->willReturn([
                'name' => $fileName,
                'file' => $destDir . DIRECTORY_SEPARATOR . $fileName // 256 characters
            ]);

        $this->uploader->setDestDir($destDir);

        $this->expectException(\LengthException::class);

        $this->uploader->move($fileName);
    }

    #[DataProvider('inaccessibleStatusDataProvider')]
    public function testMoveFileUrlWithInaccessibleStatus(int $status): void
    {
        $url = 'https://example.com/image.jpg?token=secret';
        $this->prepareRemoteDownload($url);
        $this->httpClient->expects($this->once())->method('get')->with($url);
        $this->httpClient->expects($this->once())->method('getStatus')->willReturn($status);
        $this->httpClient->expects($this->never())->method('getBody');
        $this->directoryMock->expects($this->never())->method('writeFile');
        $this->uploader->expects($this->never())->method('_setUploadFile');
        $this->uploader->expects($this->never())->method('save');

        $this->assertHttpFailure($url, $status, 'https://example.com/image.jpg');
    }

    public static function inaccessibleStatusDataProvider(): array
    {
        return [
            'informational' => [199],
            'redirect boundary' => [300],
            'permanent redirect' => [301],
            'temporary redirect' => [302],
            'not found' => [404],
            'server error' => [500]
        ];
    }

    public function testMoveFileUrlWithTransportException(): void
    {
        $url = 'http://example.com/image.jpg';
        $this->prepareRemoteDownload($url);
        $exception = new \Exception('Connection timed out');
        $this->httpClient->expects($this->once())->method('get')->with($url)->willThrowException($exception);
        $this->httpClient->expects($this->never())->method('getStatus');
        $this->httpClient->expects($this->never())->method('getBody');
        $this->directoryMock->expects($this->never())->method('writeFile');
        $this->uploader->expects($this->never())->method('_setUploadFile');
        $this->uploader->expects($this->never())->method('save');

        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage(RowValidatorInterface::ERROR_MEDIA_URL_NOT_ACCESSIBLE);

        $this->uploader->move($url);
    }

    public function testMoveFileUrlWithInvalidDomain(): void
    {
        $url = 'https://example.com/image.jpg';
        $this->domainValidator->expects($this->once())->method('isValid')->with($url)->willReturn(false);
        $this->httpClientFactory->expects($this->never())->method('create');
        $this->directoryMock->expects($this->never())->method('writeFile');

        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage(RowValidatorInterface::ERROR_MEDIA_URL_NOT_ACCESSIBLE);

        $this->uploader->move($url);
    }

    #[DataProvider('redirectDataProvider')]
    public function testMoveFileUrlFollowsAllowedRedirect(
        int $status,
        string $location,
        string $expectedUrl,
        string $headerName
    ): void {
        $url = 'https://example.com/import/image.jpg?token=secret';
        $this->prepareRemoteFilePath();
        $redirectClient = $this->createRedirectClient($url, $status, [$headerName => $location]);
        $this->httpClientFactory->expects($this->exactly(2))->method('create')
            ->willReturnOnConsecutiveCalls($redirectClient, $this->httpClient);
        $this->domainValidator->expects($this->exactly(2))->method('isValid')->willReturnMap([
            [$url, true],
            [$expectedUrl, true]
        ]);
        $this->expectClientOptions($this->httpClient);
        $this->httpClient->expects($this->once())->method('get')->with($expectedUrl);
        $this->httpClient->expects($this->once())->method('getStatus')->willReturn(200);
        $this->httpClient->expects($this->once())->method('getBody')->willReturn('image contents');
        $this->prepareSuccessfulRemoteSave();

        $this->assertSame(['name' => 'image.jpg', 'file' => '/image.jpg'], $this->uploader->move($url));
    }

    public static function redirectDataProvider(): array
    {
        return [
            'absolute' => [301, 'https://cdn.example.com/final.jpg', 'https://cdn.example.com/final.jpg', 'Location'],
            'relative' => [302, '../final.jpg', 'https://example.com/final.jpg', 'location'],
            'root relative' => [303, '/final.jpg', 'https://example.com/final.jpg', 'LOCATION'],
            'scheme relative' => [307, '//cdn.example.com/final.jpg', 'https://cdn.example.com/final.jpg', 'LoCaTiOn'],
            'query relative' => [308, '?v=2', 'https://example.com/import/image.jpg?v=2', 'Location']
        ];
    }

    public function testMoveFileUrlRejectsDisallowedRedirect(): void
    {
        $url = 'https://example.com/image.jpg?token=original';
        $redirectUrl = 'https://blocked.example.com/image.jpg?token=redirect';
        $this->prepareRemoteFilePath();
        $redirectClient = $this->createRedirectClient($url, 302, ['Location' => $redirectUrl]);
        $this->httpClientFactory->expects($this->once())->method('create')->willReturn($redirectClient);
        $this->domainValidator->expects($this->exactly(2))->method('isValid')->willReturnMap([
            [$url, true],
            [$redirectUrl, false]
        ]);
        $this->directoryMock->expects($this->never())->method('writeFile');
        $this->uploader->expects($this->never())->method('_setUploadFile');
        $this->uploader->expects($this->never())->method('save');

        $this->assertHttpFailure($url, 302, 'https://blocked.example.com/image.jpg');
    }

    #[DataProvider('redirectLimitDataProvider')]
    public function testMoveFileUrlRedirectLimit(int $redirectCount, bool $succeeds): void
    {
        $url = 'https://example.com/image.jpg?hop=0';
        $this->prepareRemoteFilePath();
        $clients = [];
        $validations = [[$url, true]];
        $nextUrl = $url;
        for ($hop = 0; $hop < $redirectCount; $hop++) {
            $currentUrl = 'https://example.com/image.jpg?hop=' . $hop;
            $nextUrl = 'https://example.com/image.jpg?hop=' . ($hop + 1);
            $clients[] = $this->createRedirectClient($currentUrl, 302, ['Location' => $nextUrl]);
            if ($hop < 5) {
                $validations[] = [$nextUrl, true];
            }
        }
        if ($succeeds) {
            $this->expectClientOptions($this->httpClient);
            $this->httpClient->expects($this->once())->method('get')->with($nextUrl);
            $this->httpClient->expects($this->once())->method('getStatus')->willReturn(200);
            $this->httpClient->expects($this->once())->method('getBody')->willReturn('image contents');
            $clients[] = $this->httpClient;
            $this->prepareSuccessfulRemoteSave();
        } else {
            $this->directoryMock->expects($this->never())->method('writeFile');
            $this->uploader->expects($this->never())->method('_setUploadFile');
            $this->uploader->expects($this->never())->method('save');
        }
        $this->httpClientFactory->expects($this->exactly(6))->method('create')
            ->willReturnOnConsecutiveCalls(...$clients);
        $this->domainValidator->expects($this->exactly(6))->method('isValid')->willReturnMap($validations);

        if ($succeeds) {
            $this->assertSame(['name' => 'image.jpg', 'file' => '/image.jpg'], $this->uploader->move($url));
        } else {
            $this->assertHttpFailure($url, 302, 'https://example.com/image.jpg');
        }
    }

    public static function redirectLimitDataProvider(): array
    {
        return ['five allowed' => [5, true], 'six rejected' => [6, false]];
    }

    private function createRedirectClient(string $url, int $status, array $headers): ClientInterface
    {
        $client = $this->createMock(ClientInterface::class);
        $this->expectClientOptions($client);
        $client->expects($this->once())->method('get')->with($url);
        $client->expects($this->once())->method('getStatus')->willReturn($status);
        $client->expects($this->once())->method('getHeaders')->willReturn($headers);
        $client->expects($this->never())->method('getBody');
        return $client;
    }

    private function expectClientOptions(ClientInterface $client): void
    {
        $client->expects($this->once())->method('setTimeout')->with(0);
        $client->expects($this->once())->method('setOptions')->with([
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_LOW_SPEED_LIMIT => 1,
            CURLOPT_LOW_SPEED_TIME => 60
        ]);
    }

    private function prepareSuccessfulRemoteSave(): void
    {
        $this->directoryMock->expects($this->once())->method('writeFile')
            ->with('var/tmp/image_38GcEmPFKXXR8NMj.jpg', 'image contents');
        $this->directoryMock->method('getAbsolutePath')->willReturn('var/dest');
        $this->uploader->expects($this->once())->method('_setUploadFile');
        $this->uploader->method('save')->willReturn(['name' => 'image.jpg', 'file' => '/image.jpg']);
    }

    private function prepareRemoteDownload(string $url): void
    {
        $this->domainValidator->expects($this->once())->method('isValid')->with($url)->willReturn(true);
        $this->prepareRemoteFilePath();
        $this->httpClientFactory->expects($this->once())->method('create')->willReturn($this->httpClient);
        $this->httpClient->expects($this->once())->method('setTimeout')->with(0);
        $this->httpClient->expects($this->once())->method('setOptions')->with([
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_LOW_SPEED_LIMIT => 1,
            CURLOPT_LOW_SPEED_TIME => 60
        ]);
    }

    private function prepareRemoteFilePath(): void
    {
        $this->uploader->method('getTmpDir')->willReturn('var/tmp');
        $this->uploader->method('checkAllowedExtension')->willReturn(true);
        $this->random->method('getRandomString')->willReturn('38GcEmPFKXXR8NMj');
        $this->directoryMock->method('getRelativePath')->willReturn('var/tmp/image_38GcEmPFKXXR8NMj.jpg');
        $this->directoryMock->method('isWritable')->willReturn(true);
    }

    private function assertHttpFailure(string $url, int $status, string $expectedUrl): void
    {
        try {
            $this->uploader->move($url);
            $this->fail('Expected an inaccessible media URL exception.');
        } catch (LocalizedException $exception) {
            $this->assertSame(RowValidatorInterface::ERROR_MEDIA_URL_NOT_ACCESSIBLE, $exception->getMessage());
            $this->assertInstanceOf(\RuntimeException::class, $exception->getPrevious());
            $this->assertSame(
                sprintf('HTTP status %d for URL %s', $status, $expectedUrl),
                $exception->getPrevious()->getMessage()
            );
        }
    }

    /**
     * @return array
     */
    public static function moveFileUrlDataProvider()
    {
        return [
            'no content' => ['https://example.com/image.jpg', 'image_38GcEmPFKXXR8NMj.jpg', 1, 204],
            'upper success boundary' => ['https://example.com/image.jpg', 'image_38GcEmPFKXXR8NMj.jpg', 1, 299],
            'uppercase scheme' => ['HTTP://example.com/image.jpg', 'image_38GcEmPFKXXR8NMj.jpg', 1],
            'http_no_file_ext' => [
                'fileUrl' => 'http://test_uploader_file',
                'expectedFileName' => 'test_uploader_file_38GcEmPFKXXR8NMj',
                'checkAllowedExtension' => 0
            ],
            'https_special_chars_no_file_ext' => [
                'fileUrl' => 'https://!:^&`;file',
                'expectedFileName' => 'file_38GcEmPFKXXR8NMj',
                'checkAllowedExtension' => 0
            ],
            'https_no_file_ext' => [
                'fileUrl' => 'https://test_uploader_file',
                'expectedFileName' => 'test_uploader_file_38GcEmPFKXXR8NMj',
                'checkAllowedExtension' => 0
            ],
            'https_invalid_chars' => [
                'fileUrl' => 'https://www.google.com/!:^&`;image.jpg',
                'expectedFileName' => 'image_38GcEmPFKXXR8NMj.jpg',
                'checkAllowedExtension' => 1
            ],
            'https_invalid_chars_no_file_ext' => [
                'fileUrl' => 'https://!:^&`;image',
                'expectedFileName' => 'image_38GcEmPFKXXR8NMj',
                'checkAllowedExtension' => 0
            ],
            'http_jpg' => [
                'fileUrl' => 'http://www.google.com/image.jpg',
                'expectedFileName' => 'image_38GcEmPFKXXR8NMj.jpg',
                'checkAllowedExtension' => 1
            ],
            'https_jpg' => [
                'fileUrl' => 'https://www.google.com/image.jpg',
                'expectedFileName' => 'image_38GcEmPFKXXR8NMj.jpg',
                'checkAllowedExtension' => 1
            ],
            'https_jpeg' => [
                'fileUrl' => 'https://www.google.com/image.jpeg',
                'expectedFileName' => 'image_38GcEmPFKXXR8NMj.jpeg',
                'checkAllowedExtension' => 1
            ],
            'https_png' => [
                'fileUrl' => 'https://www.google.com/image.png',
                'expectedFileName' => 'image_38GcEmPFKXXR8NMj.png',
                'checkAllowedExtension' => 1
            ],
            'https_gif' => [
                'fileUrl' => 'https://www.google.com/image.gif',
                'expectedFileName' => 'image_38GcEmPFKXXR8NMj.gif',
                'checkAllowedExtension' => 1
            ],
            'https_one_query_param' => [
                'fileUrl' => 'https://www.google.com/image.jpg?param=1',
                'expectedFileName' => 'image_38GcEmPFKXXR8NMj.jpg',
                'checkAllowedExtension' => 1
            ],
            'https_two_query_params' => [
                'fileUrl' => 'https://www.google.com/image.jpg?param=1&param=2',
                'expectedFileName' => 'image_38GcEmPFKXXR8NMj.jpg',
                'checkAllowedExtension' => 1
            ]
        ];
    }
}
